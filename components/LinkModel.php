<?php

namespace components;

use trident\DI;
use trident\Request;

class LinkModel extends DataTableModel
{
    use PrepareDBArgumentTrait;

    protected $dateFormat = 'd F Y H:i';

    /**
     * @var $validator Validator
     */
    protected $validator;

    protected $uid;

    public function __construct(\PDO $db, UserModel $userModel)//
    {
        parent::__construct($db);
        $this->validator = DI::build('components\\Validator');
        if (!$userModel->isGuest()) {
            $userData = $userModel->getUser();
            $this->uid = $userData['id'];
        }
    }

    public function getDefaults()
    {
        return [
            'columns' => [
                'URL',
                'Short link',
                'Expire',
            ],
        ];
    }

    public function getColumns()
    {
        return [
            ['db' => 'link', 'dt' => 'url'],
            [
                'db' => 'id',
                'dt' => 'link',
                'formatter' => function ($d, $row) {
                    return $this->idToCode($d);
                },
            ],
            [
                'db' => 'expire',
                'dt' => 'expire',
                'formatter' => function ($d, $row) {
                    return (new \DateTime('', LocaleDateTime::getTimeZone()))->setTimestamp($d)->format(
                        $this->dateFormat
                    );
                },
            ],
        ];
    }

    /**
     * Функция получения кода ссылки из индекса
     *
     * @param $id int
     * @return string
     */
    protected function idToCode($id)
    {
        $id = (int)$id;
        $digits = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $link = '';
        do {
            $dig = $id % 62;
            $link = $digits[$dig].$link;
            $id = floor($id / 62);
        } while ($id != 0);

        return $link;
    }

    public function getPK()
    {
        return 'id';
    }

    public function getTableName()
    {
        return 'link';
    }

    /**
     * @param Request $request
     * @return bool|string
     */
    public function getLink(Request $request)
    {
        $code = $request->param('code', null);

        if (null === $code) {
            return $this->simple($request);
        }

        $id = $this->codeToId($code);

        $stmt = $this->db->prepare('SELECT link FROM link WHERE id = :id AND expire >= :now AND user = :user');

        $stmt->execute(
            [
                'id' => $id,
                'now' => (new \DateTime('now', LocaleDateTime::getTimeZone()))->getTimestamp(),
                'user' => $this->uid,
            ]
        );

        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $result;
    }

    /**
     * Функция получения индекса из кода ссылки
     *
     * @param $link string
     * @return int
     */
    protected function codeToId($link)
    {
        $digits = Array(
            '0' => 0,
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            'a' => 10,
            'b' => 11,
            'c' => 12,
            'd' => 13,
            'e' => 14,
            'f' => 15,
            'g' => 16,
            'h' => 17,
            'i' => 18,
            'j' => 19,
            'k' => 20,
            'l' => 21,
            'm' => 22,
            'n' => 23,
            'o' => 24,
            'p' => 25,
            'q' => 26,
            'r' => 27,
            's' => 28,
            't' => 29,
            'u' => 30,
            'v' => 31,
            'w' => 32,
            'x' => 33,
            'y' => 34,
            'z' => 35,
            'A' => 36,
            'B' => 37,
            'C' => 38,
            'D' => 39,
            'E' => 40,
            'F' => 41,
            'G' => 42,
            'H' => 43,
            'I' => 44,
            'J' => 45,
            'K' => 46,
            'L' => 47,
            'M' => 48,
            'N' => 49,
            'O' => 50,
            'P' => 51,
            'Q' => 52,
            'R' => 53,
            'S' => 54,
            'T' => 55,
            'U' => 56,
            'V' => 57,
            'W' => 58,
            'X' => 59,
            'Y' => 60,
            'Z' => 61,
        );
        $id = 0;
        for ($i = 0; $i < strlen($link); $i++) {
            $id += $digits[$link[(strlen($link) - $i - 1)]] * pow(62, $i);
        }

        return $id;
    }

    /**
     * Insert to data base new link, or update if it exist.
     *
     * @param Request $request
     * @return ModelResponse
     */
    public function saveLink(Request $request)
    {
        $link = $request->param('link', 'http::/google.com.ua');

        if (false === $this->validateLink($request, $link)) {
            return new ModelResponse('Not valid link', $link, Action::VALIDATE, Status::ERROR);
        }

        // birthday
        $expire = $request->param('expire', 'now +1day');
        $expire = (new \DateTime($expire, LocaleDateTime::getTimeZone()))->getTimestamp();

        $hash = $this->getHash($link);

        $isIsset = $this->isIsset($hash);

        $data = [
            'user' => $this->uid,
            'hash' => $hash,
            'expire' => $expire,
        ];

        $response = new ModelResponse(null, null, Action::INSERT, Status::SUCCESS);

        if (false === $isIsset) {
            $stmt = $this->db->prepare('INSERT link (link, user, hash, expire) VALUES (:link, :user, :hash, :expire)');
            $data['link'] = $link;
            $response->setMessage('Link successfully stored in data base.');
        } else {
            $id = $request->param('code', null);
            $sql = 'UPDATE link SET expire = :expire WHERE user = :user AND hash = :hash';
            if (null !== $id) {
                $sql .= ' AND id = :id';
                $data['id'] = $this->codeToId($request->param('code', '0000'));
            }
            $stmt = $this->db->prepare($sql);
            $response->setMessage('Link successfully updated.');
            $response->setAction(Action::UPDATE);
        }

        $status = $stmt->execute($data);
        if (!$status) {
            $response->setMessage('Can not create or update link.')
                ->setStatus(Status::FAILURE);
        }
        $result = $stmt->rowCount();
        if (!$result && $status && Action::UPDATE === $response->getAction()) {
            $response->setMessage('Link is not changed.');
        }
        $return = $isIsset ? $isIsset : $this->db->lastInsertId('link');

        $response->setData($this->idToCode($return));

        return $response;
    }

    /**
     * @param Request $request
     * @param string|null $link
     * @return bool
     */
    public function validateLink(Request $request, $link = null)
    {
        if (empty($link)) {
            $link = $request->param('link', 'http::/google.com.ua');
        }

        $stream = @fopen($link, 'r');

        if (is_resource($stream)) {
            fclose($stream);

            return true;
        }

        return false;
    }

    /**
     * @param $link string
     * @return string
     */
    protected function getHash($link)
    {
        return md5($link);
    }

    /**
     * @param string $linkHash
     * @return int|false If link exist return her id, else return false.
     */
    protected function isIsset($linkHash)
    {
        $stmt = $this->db->prepare('SELECT id FROM link WHERE hash = :hash AND user = :user');

        $stmt->execute(['hash' => $linkHash, 'user' => $this->uid]);
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $result;// ? false : true;
    }

    /**
     * @param Request $request
     * @return ModelResponse
     */
    public function deleteLink(Request $request)
    {
        $code = $request->param('code', null);
        $codes = $request->param('codes', null);

        if (null === $code && null === $codes) {
            return new ModelResponse('No data to delete.', null, Action::VALIDATE, Status::ERROR);
        } elseif (null !== $code) {// delete one link
            $stmt = $this->db->prepare('DELETE FROM link WHERE id = :id AND user = :user');
            $data = [
                'user' => $this->uid,
                'id' => $this->codeToId($code),
            ];
        } else {// delete many links
            if (!is_array($codes)) {
                return new ModelResponse(
                    'For deletion many links you must send array of codes.',
                    $codes,
                    Action::VALIDATE,
                    Status::ERROR
                );
            }
            $data = [
                'user' => $this->uid,
            ];
            $in = [];

            foreach ($codes as $key => $value) {
                $data['ids'.$key] = $this->codeToId($value);
                $in[] = ':ids'.$key;
            }

            $in = implode(', ', $in);

            $stmt = $this->db->prepare('DELETE FROM link WHERE id IN ('.$in.') AND user = :user');
        }

        $stmt->execute($data);
        $result = $stmt->rowCount();

        return new ModelResponse(
            'Deleted.',
            ['rowsCount' => $result],
            Action::DELETE,
            $result ? Status::SUCCESS : Status::FAILURE
        );
    }

    /**
     * @param Request $request
     * @return string|false
     */
    public function linkExist(Request $request)
    {
        $code = $request->param('code', null);

        if (null === $code) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT link FROM link WHERE id = :value');

        $stmt->execute(['value' => $this->codeToId($code)]);
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $result ? $result : false;
    }

    /**
     * @return ModelResponse
     */
    public final function createSchema()
    {
        $sql = <<<'sql'
CREATE TABLE `link` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `link` VARCHAR(1024) NOT NULL,
  `user` INT(11) NOT NULL,
  `hash` VARCHAR(32) NOT NULL,
  `expire` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_hash` (`user`,`hash`)
) ENGINE=MYISAM AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8
sql;

        try {
            $this->db->exec($sql);
        } catch (\PDOException $e) {
            return new ModelResponse($e->getMessage(), null, Action::CREATE, Status::FAILURE);
        }

        return new ModelResponse(
            'Link schema successfully created.',
            null,
            Action::CREATE,
            Status::SUCCESS
        );
    }
}