<?php
namespace components;


use trident\DI;
use trident\Request;

class SetupModel
{
    /**
     * @var $validator Validator
     */
    protected $validator;

    protected $returnPassword = false;

    public function __construct()
    {
        $this->validator = DI::build('components\\Validator');
    }

    public function getDefaults()
    {
        return [
            'host' => 'localhost',
            'dbname' => '',
            'name' => '',
        ];
    }

    public function saveDbConfig(Request $request)
    {
        $this->returnPassword = true;
        $response = $this->checkConnection($request);
        $this->returnPassword = false;

        if (!(Status::SUCCESS === $response->getStatus() && Action::OTHER === $response->getAction())) {
            return $response;
        }

        $data = $response->getData();
        $pdoConfig = [
            $this->getDSN($data['host'], $data['dbname']),
            $data['name'],
            $data['password'],
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ];

        $config = '<?php return '.self::var_export($pdoConfig).';';

        try {
            self::writeFile(__DIR__.'/pdoConfig.php', $config);
        } catch (\Exception $e) {
            return new ModelResponse($e->getMessage(), $data, Action::OTHER, Status::FAILURE);
        }

        return new ModelResponse(
            'Config file was successfully stored.',
            $data,
            Action::OTHER,
            Status::SUCCESS
        );
    }

    /**
     * @param Request $request
     * @return ModelResponse
     */
    public function checkConnection(Request $request)
    {
        $data = [];
        $host = $data['host'] = $request->param('host', 'localhost');

        $dbName = $data['dbname'] = $request->param('dbname', null);
        if (empty($dbName) || !$this->validator->alpha_numeric_und($dbName)) {
            return new ModelResponse(
                'Not defined data base name.', $data, Action::VALIDATE, Status::ERROR
            );
        }

        $name = $data['name'] = $request->param('name', null);
        if (empty($name) || !$this->validator->alpha_numeric_und($name)) {
            return new ModelResponse(
                'Not defined data base user name.',
                $data,
                Action::VALIDATE,
                Status::ERROR
            );
        }

        // password
        $password = $request->param('password', null);
        $confirmedPassword = $request->param('confirm_password', null);

        if (!($this->validateStr($password) && $this->validateStr($confirmedPassword))) {
            return new ModelResponse(
                'Not defined data base user password.',
                null,
                Action::VALIDATE,
                Status::ERROR
            );
        }

        if ($password !== $confirmedPassword) {
            return new ModelResponse(
                'Confirm password and New password is not the same, but must be.',
                null,
                Action::VALIDATE,
                Status::ERROR
            );
        }

        $dsn = $this->getDSN($host, $dbName);

        try {
            new \PDO($dsn, $name, $password);
        } catch (\PDOException $e) {
            return new ModelResponse($e->getMessage(), $data, Action::OTHER, Status::FAILURE);
        }

        if ($this->returnPassword) {
            $data['password'] = $password;
        }

        return new ModelResponse('Connected.', $data, Action::OTHER, Status::SUCCESS);

    }

    public function validateStr($login)
    {
        return !empty($login) && $this->validator->alpha_numeric($login);
    }

    private function getDSN($host, $dbName)
    {
        return "mysql:host=$host;dbname=$dbName;charset=utf8";
    }

    private static function var_export($value)
    {
        return str_replace('  ', '', var_export($value, true));
    }

    public static function writeFile($path, $data, $mode = 'wb+')
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0770, true)) {
            throw new \LogicException ('Could not create directory: "'.$path.'"');
        }
        if (!$fp = fopen($path, $mode)) {
            throw new \LogicException ('Could not open file for writing');
        }

        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            throw new \RuntimeException ('Could not lock file');
        }

        fwrite($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);
        @chmod($path, 0770);

        return true;
    }

    public function setInstalled()
    {
        try {
            self::writeFile(__DIR__.'/installed.php', '<?php return true;');
        } catch (\Exception $e) {
            return new ModelResponse($e->getMessage(), null, Action::OTHER, Status::FAILURE);
        }

        return new ModelResponse('File was successfully stored.', null, Action::OTHER, Status::SUCCESS);
    }

    public function createSchema()
    {
        $multiResponse = new ModelMultiResponse();

        $models = ['countryModel', 'userModel', 'linkModel'];

        foreach ($models as $model) {
            $model = DI::get($model);
            $return = $model->createSchema();
            if (!(Status::SUCCESS === $return->getStatus() && Action::CREATE === $return->getAction())) {
                return $return;
            }
            $multiResponse->addResponse($return);
        }

        $multiResponse->setMessage('All schemas successfully created.')
            ->setAction(Action::CREATE)
            ->setStatus(Status::SUCCESS);

        return $multiResponse;
    }

    public function fillData()
    {
        /**
         * @var $country CountryModel
         */
        $country = DI::get('countryModel');

        return $country->fillData();
    }

}