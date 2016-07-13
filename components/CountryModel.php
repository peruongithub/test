<?php
namespace components;


use trident\Request;

class CountryModel
{
    use PrepareDBArgumentTrait;

    const SELECT_ALL = '*';

    /**
     * @var $db \PDO
     */
    protected $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @param Request $request
     * @param null $id
     * @return bool
     */
    public function isValidCountry(Request $request, $id = null)
    {
        if (null === $id) {
            $id = $this->prepare($request->param('country', 1));
        }
        $stmt = $this->db->prepare('SELECT id FROM country WHERE id = :value');

        $stmt->execute(['value' => $id]);

        return $stmt->fetchColumn() ? true : false;
    }

    /**
     * @param Request $request
     * @param null|int $id
     * @return array|mixed
     */
    public function getCountryData(Request $request, $id = null)
    {
        if (self::SELECT_ALL === $id) {
            $stmt = $this->db->prepare('SELECT * FROM country');
            $stmt->execute();

            return $stmt->fetchAll();
        } elseif (is_int($id)) {
            //
        } else {
            $id = $this->prepare($request->param('country_id', 1));
        }

        $stmt = $this->db->prepare('SELECT * FROM country WHERE id = :value');
        $stmt->execute(['value' => $id]);

        return $stmt->fetch();
    }

    /**
     * @return ModelResponse
     */
    public final function createSchema()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `country` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) NOT NULL,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_UNIQUE` (`code`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8';

        try {
            $this->db->exec($sql);
        } catch (\PDOException $e) {
            return new ModelResponse($e->getMessage(), null, Action::CREATE, Status::FAILURE);
        }

        return new ModelResponse(
            'Country schema successfully created.',
            null,
            Action::CREATE,
            Status::SUCCESS
        );
    }

    /**
     * @return ModelResponse
     */
    public final function fillData()
    {
        $countries = require('territories.php');

        $sql = [];

        foreach ($countries as $code => $name) {
            $sql[] = "('$code',  '$name')";
        }

        $sql = implode(', ', $sql);

        try {
            $sql = 'INSERT IGNORE INTO `country` (`code`, `name`) VALUES '.$sql;
            $stmt = $this->db->query($sql);
        } catch (\PDOException $e) {
            return new ModelResponse($e->getMessage(), null, Action::INSERT, Status::FAILURE);
        }

        $stmt->closeCursor();

        return new ModelResponse(
            'Table `country` was successful created and filled.',
            null,
            Action::INSERT,
            Status::SUCCESS
        );
    }
}