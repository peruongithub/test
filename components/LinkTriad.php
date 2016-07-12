<?php
namespace components;

use trident\Request;
use trident\Triad;
use trident\URL;

class LinkTriad extends Triad
{
    /**
     * @var $model LinkModel
     */
    protected $model;

    protected $actions = [
        'index' => [
            'httpMethods' => 'GET',
        ],
        'linkData' => [
            'httpMethods' => 'GET',
            'template' => './data/tpl/linkData.tpl.php',
            'filters' => [
                'onlyForLogged' => ['components\\UserFilter', 'onlyForLogged'],
            ],
        ],
        'link' => [
            'httpMethods' => 'HEAD, GET, POST, PUT, PATCH, DELETE',
            'filters' => [
                'onlyAjaxAllowed' => [__CLASS__, 'onlyAjaxAllowed'],
                'onlyForLogged' => ['components\\UserFilter', 'onlyForLogged'],
            ],
        ],
    ];

    public function index()
    {
        $code = $this->request->param('code', null);
        if (null === $code) {
            $this->response->status(404);
        } else {
            $link = $this->model->getLink($this->request);
            if (false === $link) {
                $this->response->status(404);
            } else {
                $this->request->redirect($link);
            }
        }

        return null;
    }

    public function linkData()
    {
        $data = $this->model->getDefaults();
        $data['restUrl'] = $this->createUrl(
            'link',
            str_replace('/', '_', $this->getRout().'_restLink'),
            $this->getRout()
        );

        return $data;
    }

    public function link()
    {
        switch ($this->request->method()) {
            case Request::POST:
                $result = $this->model->saveLink($this->request);

                if (Status::ERROR === $result->getStatus() || Status::FAILURE === $result->getStatus()) {
                    $this->response->status(400);

                    return $result->toArray();
                }

                if (Action::UPDATE === $result->getAction()) {
                    $this->response->status(200);
                } else {
                    $this->response->status(201);
                }

                $result = $result->toArray();
                $result['url'] = URL::base('http', null, null, null, $result['data']);

                return $result;
                break;
            case Request::DELETE:
                $result = $this->model->deleteLink($this->request);

                if (Status::ERROR === $result->getStatus() || Status::FAILURE === $result->getStatus()) {
                    $this->response->status(400);
                }

                return $result->toArray();
                break;
            case Request::HEAD:
                $link = $this->model->getLink($this->request);
                if ($link) {
                    $this->response->status(200);
                } else {
                    $this->response->status(404);
                }

                return '';
                break;
            case Request::GET:
            default:
                $data = $this->model->getLink($this->request);
                if ($data) {
                    return $data;
                }

                $this->response->status(404);

                return '';
        }
    }
}