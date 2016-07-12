<?php

namespace components;


use trident\Request;
use trident\Route;
use trident\Triad;
use trident\URL;

class SetupTriad extends Triad
{
    /**
     * @var $model SetupModel
     */
    protected $model;

    protected $actions = [
        'index' => [
            'httpMethods' => 'GET',
            'template' => './data/tpl/setup.tpl.php',
        ],
        'done' => [
            'httpMethods' => 'GET',
            'filters' => ['onlyAjaxAllowed' => [__CLASS__, 'onlyAjaxAllowed']],
        ],
        'dbConfig' => [
            'httpMethods' => 'HEAD,PUT,POST',
            'template' => './data/tpl/linkData.tpl.php',
            'filters' => ['onlyAjaxAllowed' => [__CLASS__, 'onlyAjaxAllowed']],
        ],
    ];

    public static function notInstalled($action, array $config, Triad $triad)
    {
        $installed = require('installed.php');
        if ($installed) {
            /**
             * @var $request Request
             */
            $request = $triad->get('request');
            $request->redirect(URL::base());

            return false;
        }

        return true;
    }

    public function init($options = null)
    {
        parent::init($options);
        $this->defaultConfigForActions['filters']['notInstalled'] = [__CLASS__, 'notInstalled'];
    }

    public function index()
    {
        $defaults = $this->model->getDefaults();
        $defaults['dbConfig'] = $this->createUrl('dbConfig', Route::DEF_ROUTE_NAME, $this->getRout());
        $defaults['done'] = $this->createUrl('done', Route::DEF_ROUTE_NAME, $this->getRout());

        return $defaults;
    }

    public function dbConfig()
    {
        switch ($this->request->method()) {
            case Request::HEAD:
                $modelResponse = $this->model->checkConnection($this->request);
                if (!(Status::SUCCESS === $modelResponse->getStatus() && Action::OTHER === $modelResponse->getAction())
                ) {
                    $this->response->status(404);
                } else {
                    $this->response->status(200);
                }
                break;
            case Request::POST:
                $modelResponse = $this->model->checkConnection($this->request);
                if (!(Status::SUCCESS === $modelResponse->getStatus() && Action::OTHER === $modelResponse->getAction())
                ) {
                    $this->response->status(400);
                } else {
                    $this->response->status(200);
                }

                return $modelResponse->toArray();
                break;
            case Request::PUT:
                $modelResponse = $this->model->saveDbConfig($this->request);
                if (!(Status::SUCCESS === $modelResponse->getStatus() && Action::OTHER === $modelResponse->getAction())
                ) {
                    $this->response->status(400);
                } else {
                    $this->response->status(200);
                }

                return $modelResponse->toArray();
                break;
        }

        return null;
    }

    public function done()
    {
        $multiResponse = new ModelMultiResponse();
        $actions = ['createSchema', 'fillData', 'setInstalled'];

        foreach ($actions as $action) {
            /**
             * @var $response ModelResponse
             */
            $response = $this->model->$action();
            if (Status::SUCCESS !== $response->getStatus()) {
                $this->response->status(400);

                return $response->toArray();
            }
            $multiResponse->addResponse($response);
        }
        $multiResponse->setMessage('Installation successfully completed.')
            ->setAction(Action::CREATE)
            ->setStatus(Status::SUCCESS);

        return $multiResponse->toArray();
    }
}