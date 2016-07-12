<?php

namespace components;


use trident\DI;
use trident\phpRender;
use trident\Request;
use trident\Route;
use trident\URL;

class UserTriad extends MainTriad
{
    /**
     * @var $session phpRender
     */
    protected $render;

    protected $actions = [
        'login' => [
            'httpMethods' => 'GET, POST',
            'template' => './data/tpl/login.tpl.php',
        ],
        'register' => [
            'httpMethods' => 'GET, POST',
            'template' => './data/tpl/register.tpl.php',
        ],
        'myPersonalData' => [
            'template' => './data/tpl/userData.tpl.php',
            'filters' => ['onlyForLogged' => ['components\\UserFilter', 'onlyForLogged']],
        ],
        'changePassword' => [
            'template' => './data/tpl/changePassword.tpl.php',
            'filters' => ['onlyForLogged' => ['components\\UserFilter', 'onlyForLogged']],
        ],
        'myAccount' => [
            'actions' => [
                'myPersonalData' => [null, 'myPersonalData'],
                'changePassword' => [null, 'changePassword'],
                'myData' => ['link', 'linkData'],
            ],
            'filters' => ['onlyForLogged' => ['components\\UserFilter', 'onlyForLogged']],
        ],
        'checkUniqueLogin' => [
            'filters' => ['onlyAjaxAllowed' => [__CLASS__, 'onlyAjaxAllowed']],
        ],
        'checkUniqueEmail' => [
            'filters' => ['onlyAjaxAllowed' => [__CLASS__, 'onlyAjaxAllowed']],
        ],
    ];
    protected $runWithReflectionActions = [];

    /**
     * @var $model UserModel
     */
    protected $model;

    public function login()
    {
        $data = [
            'uri' => $this->createUrl(__METHOD__),
            'method' => Request::POST,
            'errors' => '',
        ];

        if (Request::POST === $this->request->method()) {
            //'set'
            $result = $this->model->login($this->request);
            if (Status::SUCCESS !== $result->getStatus()) {
                $data['errors'] = $result->getMessage();

                return $data;
            }

            $this->request->redirect(URL::base());

            return '';
        }

        //'get'

        return $data;
    }

    public function logout()
    {
        $this->model->logout();

        $this->request->redirect(URL::base());
        //$this->request->redirect($this->createUrl(null,'login'));
    }

    public function register()
    {
        $data = $this->model->getDefaults();

        $data['url'] = $this->createUrl(__METHOD__);
        $data['method'] = Request::POST;
        $data['errors'] = '';
        $countryModel = DI::get('countryModel');
        $data['country_list'] = $countryModel->getCountryData(Request::initial(), CountryModel::SELECT_ALL);

        $data['checkUniqueLogin'] = $this->createUrl('checkUniqueLogin');
        $data['checkUniqueEmail'] = $this->createUrl('checkUniqueEmail');

        if (Request::POST === $this->request->method()) {
            //'set'
            $result = $this->model->register($this->request);
            if (Status::SUCCESS !== $result->getStatus()) {
                $data['errors'] = $result->getMessage();

                return array_replace($data, $this->request->post());
            }

            $this->request->redirect(URL::base());

            return '';
        }

        //'get'

        return $data;
    }

    public function myPersonalData()
    {
        //'get'
        try {
            $userData = $this->model->getUser();
        } catch (\RuntimeException $e) {
            //log exception
            $this->request->redirect(URL::base());

            return '';
        }

        $data = [
            'uri' => $this->createUrl(__METHOD__, Route::DEF_ROUTE_NAME, $this->getRout()),
            'method' => Request::POST,
        ];

        $countryModel = DI::get('countryModel');
        $data['country_list'] = $countryModel->getCountryData(Request::initial(), CountryModel::SELECT_ALL);

        $data = array_replace($data, $userData);

        if (Request::POST === $this->request->method()) {
            //'set'
            $result = $this->model->updateUserData($this->request);
            $status = $result->getStatus();
            if (Status::ERROR === $status) {
                $this->response->status(400);
            } elseif (Status::FAILURE === $status) {
                $this->response->status(500);
            }

            return $result->toArray();
        }

        return $data;
    }

    public function changePassword()
    {
        $data = [
            'uri' => $this->createUrl(__METHOD__, Route::DEF_ROUTE_NAME, $this->getRout()),
            'method' => Request::POST,
            'errors' => '',
        ];
        if (Request::POST === $this->request->method()) {
            //'set'
            $result = $this->model->changePassword($this->request);
            $status = $result->getStatus();
            if (Status::ERROR === $status) {
                $this->response->status(400);
            } elseif (Status::FAILURE === $status) {
                $this->response->status(500);
            }

            return $result->toArray();
        }

        return $data;
    }

    public function myAccount(array $actions)
    {
        $data['mainContent'] = DI::get('render')->fetch('./data/tpl/myAccount.tpl.php', $actions, $this);

        return $data;
    }

    /**
     * @return bool
     */
    public function checkUniqueLogin()
    {
        return $this->model->isUnique($this->request, null, UserModel::LOGIN);
    }

    /**
     * @return bool
     */
    public function checkUniqueEmail()
    {
        return $this->model->isUnique($this->request, null, UserModel::EMAIL);
    }
}