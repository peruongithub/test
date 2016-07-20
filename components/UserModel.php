<?php

namespace components;


use trident\DI;
use trident\GeoIP\GeoIPBase;
use trident\Request;

class UserModel
{
    use PrepareDBArgumentTrait;

    const LOGIN = 'login';

    const EMAIL = 'email';

    const MIN_PASS_LEN = 8;

    protected $reservedLogin = ['guest', 'admin'];

    protected $dateFormat = 'd F Y';

    /**
     * @var $session Session
     */
    protected $session;

    /**
     * @var $db \PDO
     */
    protected $db;

    /**
     * @var $countryModel CountryModel
     */
    protected $countryModel;

    /**
     * @var $validator Validator
     */
    protected $validator;

    protected $user;

    public function __construct(Session $session, \PDO $db, CountryModel $countryModel)//
    {
        $this->session = $session;
        $this->db = $db;
        $this->countryModel = $countryModel;
        $this->validator = DI::build('components\\Validator');
    }

    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    public function getDefaults()
    {
        return [
            'id' => 0,
            'email' => '',
            'login' => '',
            'name' => '',
            'birthday' => (new \DateTime('now', LocaleDateTime::getTimeZone()))->format($this->dateFormat),
            'country_id' => '0',
            'country_code' => GeoIPBase::getClientRegionCode(),
            'country_name' => 'undefined',
            'minPassLen' => self::MIN_PASS_LEN
        ];
    }

    /**
     * @return void
     */
    public function logout()
    {
        $this->session->delete('uid')->destroy();
    }

    /**
     * @param Request $request
     * @return ModelResponse
     */
    public function login(Request $request)
    {
        $login = $this->prepare($request->param('login', 'guest'));
        $password = $request->param('password', 'defaultPassword');

        $password = $this->cryptPassword($password);

        $stmt = $this->db->prepare(
            '
            SELECT 
              user.id, 
              user.login,
              user.email,
              user.name,
              user.birthday, 
              user.country as country_id,
              country.code as country_code,
              country.name as country_name
            FROM 
              user 
              JOIN country
            WHERE 
                (user.login = :login OR user.email = :login) 
              AND 
                user.password = :password 
              AND 
                user.country = country.id
        '
        );

        $stmt->execute(['login' => $login, 'password' => $password]);

        $result = $stmt->fetch();
        $stmt->closeCursor();

        if (!$result) {
            return new ModelResponse(
                'User with this login not found or wrong password.',
                null,
                Action::GET,
                Status::FAILURE
            );
        }
        $this->user = $result;

        $this->user['birthday'] = (new \DateTime('', LocaleDateTime::getTimeZone()))->setTimestamp(
            $this->user['birthday']
        )->format($this->dateFormat);

        $this->session->start(null);

        $this->session->set('uid', $this->user['id']);

        return new ModelResponse('User logged in.', null, Action::GET, Status::SUCCESS);
    }

    protected function cryptPassword($password)
    {
        return md5($password);
    }

    /**
     * @param Request $request
     * @return ModelResponse
     */
    public function register(Request $request)
    {
        $login = $this->prepare($request->param('login', 'guest'));

        if (false === $this->validateLogin($request, $login)) {
            return new ModelResponse('Not valid user login', $login, Action::VALIDATE, Status::ERROR);
        }

        // email
        $email = $this->prepare($request->param('email', 'guest'));

        if (false === $this->validateEmail($request, $email)) {
            return new ModelResponse('Not valid user email', $email, Action::VALIDATE, Status::ERROR);
        }

        // password
        $password = $request->param('password', 'defaultPassword');
        if (($check = $this->validatePassword($password)) instanceof ModelResponse) {
            return $check;
        }
        $password = $this->cryptPassword($password);
        $confirmedPassword = $this->cryptPassword($request->param('confirm_password', 'defaultPassword'));

        if ($password !== $confirmedPassword) {
            return new ModelResponse(
                'Confirm password and New password is not the same, but must be.',
                null,
                Action::VALIDATE,
                Status::ERROR
            );
        }

        // name
        $name = $request->param('name', 'no name user');

        // birthday
        $birthday = $request->param('birthday', 'now');
        $birthday = new \DateTime($birthday, LocaleDateTime::getTimeZone());
        $timestamp = $birthday->getTimestamp();

        // country
        $country = $this->prepare($request->param('country', 1));

        if (false === $this->countryModel->isValidCountry($request, $country)) {
            return new ModelResponse('Unknown country', $country, Action::VALIDATE, Status::ERROR);
        }

        // agree
        $agree = $this->validator->checkbox($this->prepare($request->param('agree', 0)));

        if (false === boolval($agree)) {
            return new ModelResponse(
                'You can not register without agree terms and conditions.',
                null,
                Action::VALIDATE,
                Status::ERROR
            );
        }

        $stmt = $this->db->prepare(
            'INSERT user (login, email, password, name, birthday, country, registered, agree)
            VALUES (:login, :email, :password, :name, :birthday, :country, :registered, :agree)'
        );

        $data = [
            'login' => $login,
            'email' => $email,
            'password' => $password,
            'name' => $name,
            'birthday' => $timestamp,
            'country' => $country,
            'registered' => (new \DateTime('now', LocaleDateTime::getTimeZone()))->getTimestamp(),
            'agree' => $agree,

        ];

        $status = $stmt->execute($data);

        if (!$status) {
            return new ModelResponse('Can not create new user account.', null, Action::INSERT, Status::FAILURE);
        }

        $stmt->closeCursor();
        // set data (login)
        $id = $this->db->lastInsertId('user');
        $data['id'] = $id;
        $data['birthday'] = $birthday;
        $data['country_id'] = $country;

        $countryData = $this->countryModel->getCountryData($request, $country);

        $data['country_code'] = $countryData['code'];
        $data['country_name'] = $countryData['name'];

        unset($data['agree'], $data['country'], $countryData);

        $this->user = $data;

        $this->session->start(null);

        $this->session->set('uid', $id);

        return new ModelResponse('New user account was successfully created.', null, Action::INSERT, Status::SUCCESS);
    }

    public function validateLogin(Request $request, $login)
    {
        $valid = !empty($login)
            &&
            $this->validator->alpha_numeric($login)
            &&
            $this->isUnique($request, $login, self::LOGIN);

        return $valid;
    }

    /**
     * @param Request $request
     * @param string|null $value
     * @param string $type
     * @return bool
     */
    public function isUnique(Request $request, $value = null, $type = self::LOGIN)
    {
        if (null === $value) {
            $value = $this->prepare($request->param($type, 'guest'));
        }
        $value = strtolower($value);

        if (in_array($value, $this->reservedLogin)) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT '.$type.' FROM user WHERE '.$type.' = :value');

        $stmt->execute(['value' => $value]);
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        return !$result ? true : false;
    }

    public function validateEmail(Request $request, $email)
    {
        $valid = !empty($email)
            &&
            $this->validator->valid_email($email)
            &&
            $this->isUnique($request, $email, self::EMAIL);

        return $valid;
    }

    /**
     * @param $password
     * @return bool|UserError
     */
    protected function validatePassword($password)
    {
        if ('defaultPassword' === $password || empty($password)) {
            return new ModelResponse('Not valid user password', null, Action::VALIDATE, Status::ERROR);
        }
        if (mb_strlen($password) < self::MIN_PASS_LEN) {
            return new ModelResponse(
                'Number characters in password must be greeter or equal '.self::MIN_PASS_LEN,
                null,
                Action::VALIDATE,
                Status::ERROR
            );
        }

        return true;
    }

    /**
     * @param Request $request
     * @return ModelResponse
     */
    public function updateUserData(Request $request)
    {
        $userData = $this->getUser();

        $uid = $userData['id'];

        $login = $this->prepare($request->param('login', 'guest'));

        if ($userData['login'] !== $login && false === $this->validateLogin($request, $login)) {
            return new ModelResponse('Not valid user login', $login, Action::VALIDATE, Status::ERROR);
        } else {
            $login = $userData['login'];
        }

        // email
        $email = $this->prepare($request->param('email', 'guest'));

        if ($userData['email'] !== $email && false === $this->validateEmail($request, $login)) {
            return new ModelResponse('Not valid user email', $email, Action::VALIDATE, Status::ERROR);
        } else {
            $email = $userData['email'];
        }

        // name
        $name = $request->param('name', 'no name user');

        // birthday
        $birthday = $request->param('birthday', 'now');
        $birthday = new \DateTime($birthday, LocaleDateTime::getTimeZone());
        $timestamp = $birthday->getTimestamp();

        // country
        $country = $this->prepare($request->param('country', 1));

        if (false === $this->countryModel->isValidCountry($request, $country)) {
            return new ModelResponse('Unknown country', $country, Action::VALIDATE, Status::ERROR);
        }

        $stmt = $this->db->prepare(
            'UPDATE user SET login=:login, email=:email, name=:name, birthday=:birthday, country=:country WHERE id = :value'
        );

        $data = [
            'login' => $login,
            'email' => $email,
            'name' => $name,
            'birthday' => $timestamp,
            'country' => $country,
            'value' => $uid,

        ];

        $result = $stmt->execute($data);
        $stmt->closeCursor();
        if (!$result) {
            return new ModelResponse('Can not update user account.', $data, Action::UPDATE, Status::FAILURE);
        }

        $this->getUser();

        return new ModelResponse(
            'Personal user data was successfully updated.', $data, Action::UPDATE, Status::SUCCESS
        );
    }

    /**
     * @return array
     * @throws \RuntimeException
     */
    public function getUser()
    {
        if (!$this->isGuest()) {
            if (null === $this->user) {
                $uid = (int)$this->prepare($this->session->get('uid', null));

                $stmt = $this->db->prepare(
                    '
            SELECT 
              user.id, 
              user.login,
              user.email,
              user.name,
              user.birthday, 
              user.country as country_id,
              country.code as country_code,
              country.name as country_name
            FROM 
              user 
              JOIN country
            WHERE 
                user.id = :id 
              AND 
                user.country = country.id
        '
                );

                $stmt->execute(['id' => $uid]);

                $data = $stmt->fetch();
                $stmt->closeCursor();
                if (!$data) {
                    throw new \RuntimeException('Not found user');
                }

                $data['birthday'] = (new \DateTime('', LocaleDateTime::getTimeZone()))->setTimestamp(
                    $data['birthday']
                )->format($this->dateFormat);

                $this->user = $data;
            }

            return $this->user;
        }
        throw new \RuntimeException('Not logged in user');
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return null === $this->session->get('uid', null);
    }

    /**
     * @param Request $request
     * @return ModelResponse
     */
    public function changePassword(Request $request)
    {
        $uid = $this->session->get('uid');

        if (false === $this->userExist($uid)) {
            return new ModelResponse('User is not logged-in', null, Action::VALIDATE, Status::ERROR);
        }
        $oldPassword = $request->param('old_password', 'defaultPassword');

        $oldPassword = $this->cryptPassword($oldPassword);

        $uid = (int)$this->prepare($uid);

        $stmt = $this->db->prepare('SELECT password FROM user WHERE id = :value');

        $stmt->execute(['value' => $uid]);

        $passInDB = $stmt->fetchColumn();

        if ($passInDB !== $oldPassword) {
            return new ModelResponse('Wrong old password.', null, Action::VALIDATE, Status::ERROR);
        }
        $stmt->closeCursor();

        //verify
        $password = $request->param('password', 'defaultPassword');

        if (($check = $this->validatePassword($password)) instanceof ModelResponse) {
            return $check;
        }
        $password = $this->cryptPassword($password);

        $confirmedPassword = $this->cryptPassword($request->param('confirm_password', 'defaultPassword'));

        if ($password !== $confirmedPassword) {
            return new ModelResponse(
                'Confirm password and New password is not the same, but must be.',
                null,
                Action::VALIDATE,
                Status::ERROR
            );
        }

        //set
        $stmt = $this->db->prepare('UPDATE user SET password = :password WHERE id = :value');

        $result = $stmt->execute(['value' => $uid, 'password' => $password]);
        $stmt->closeCursor();

        if (!$result) {
            return new ModelResponse('Can not update user password.', null, Action::UPDATE, Status::FAILURE);
        }

        return new ModelResponse('User password was successfully updated.', null, Action::UPDATE, Status::SUCCESS);
    }

    /**
     * @param int|null $uid
     * @return bool
     */
    public function userExist($uid = null)
    {
        if (null === $uid || null === ($uid = $this->session->get('uid', null))) {
            return false;
            //throw new \RuntimeException('User id is not defined');
        }
        $uid = (int)$this->prepare($uid);

        $stmt = $this->db->prepare('SELECT id FROM user WHERE id = :value');

        $stmt->execute(['value' => $uid]);
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $result ? true : false;
    }

    /**
     * @return ModelResponse
     */
    public final function createSchema()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(45) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(256) NOT NULL,
  `birthday` int(11) NOT NULL,
  `registered` int(11) NOT NULL,
  `agree` int(1) NOT NULL DEFAULT \'0\',
  `country` int(3) NOT NULL,
  `name` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_UNIQUE` (`login`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8';

        try {
            $this->db->exec($sql);
        } catch (\PDOException $e) {
            return new ModelResponse($e->getMessage(), null, Action::CREATE, Status::FAILURE);
        }

        return new ModelResponse(
            'User schema successfully created.',
            null,
            Action::CREATE,
            Status::SUCCESS
        );
    }
}