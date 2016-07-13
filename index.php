<?php
/**
 * Perederko Ruslan <perederko.ruslan@gmail.com>
 */

require_once('vendor/autoload.php');

$initClassData = [
    'trident\\DI' => [
        'session' => [
            'className' => 'components\\Session',
            'argument' => [
                'cookieExpire' => (new DateTime('now'))->modify('+1 week'),
            ],
        ],
        'db' => [
            'className' => 'PDO',
            'arguments' => require('./components/pdoConfig.php'),
        ],
        'render' => [
            'className' => 'trident\\phpRender',
        ],
        'setupModel' => [
            'className' => 'components\\SetupModel',
        ],
        'countryModel' => [
            'className' => 'components\\CountryModel',
            'arguments' => ['$::db' => 'db'],
        ],
        'userModel' => [
            'className' => 'components\\UserModel',
            'arguments' => [
                '$::session' => 'session',
                '$::db' => 'db',
                '$::countryModel' => 'countryModel',
            ],
        ],
        'linkModel' => [
            'className' => 'components\\LinkModel',
            'arguments' => [
                '$::db' => 'db',
                '$::userModel' => 'userModel',
            ],
        ],
    ],
    'trident\\Route' => [
        'default_protocol' => 'http://',
        'localhosts' => [false, '', 'local', 'localhost'],
        'routes' => [
            [\trident\Route::DEF_ROUTE_NAME, '<controller>(/<action>)(/<s>)'],
        ],
    ],
    'components\\LocaleDateTime' => [],
];

$coreProperties = [
    'context' => 'production',
    'appComponents' => [
        'main' => [
            'className' => 'components\\MainTriad',
        ],
        'setup' => [
            'className' => 'components\\SetupTriad',
            'argument' => [
                '$::model' => 'setupModel',
            ],
        ],
        'link' => [
            'className' => 'components\\LinkTriad',
            'argument' => [
                '$::model' => 'linkModel',
                'actions' => [
                    'index' => [
                        'httpMethods' => 'GET',
                    ],
                ],
            ],
            'routes' => [
                [
                    'link',
                    '<code>',
                    ['code' => '^(?!(link|setup|user|null))[A-Za-z0-9]+'],
                    ['controller' => 'link', 'action' => 'index'],
                ],
                ['restLink', 'link(/<code>)', ['code' => '[A-Za-z0-9]+'], ['controller' => 'link', 'action' => 'link']],
            ],
        ],
        'user' => [
            'className' => 'components\\UserTriad',
            'argument' => [
                '$::model' => 'userModel',
                'actions' => [
                    'login' => [
                        'template' => './data/tpl/login.tpl.php',
                    ],
                ],
            ],
            'routes' => [
                ['llr', '<action>', ['action' => '(login|logout|register)'], ['controller' => 'user']],
                ['myAccount', 'account(/<s>)', null, ['controller' => 'user', 'action' => 'myAccount']],
            ],

        ],
    ],
    'defaultAppComponent' => 'main',
    'defaultAction' => 'index',
    'hideInputPoint' => true,
];

\trident\Core::init($coreProperties, $initClassData);

$request = (new \trident\Request());


$installed = require('./components/installed.php');

if (!$installed && !in_array(trim($request->getUri(),'/'),['setup','setup/dbConfig','setup/done'])) {
    $request->redirect(\trident\URL::base('http',null,null,null,'setup'));
    $response = $request->getResponse();
}else{
    $response = $request->execute();
}

$status = $response->status();
$isAjax = $request->is_ajax();
$tpl = null;
switch ($status) {
    case 400:
        $tpl = './data/tpl/errors/error400.php';
        break;
    case 403:
        $tpl = './data/tpl/errors/error403.php';
        break;
    case 404:
        $tpl = './data/tpl/errors/error404.php';
        break;
    case 500:
        $tpl = './data/tpl/errors/error500.php';
        break;
    case 503:
        $tpl = './data/tpl/errors/error503.php';
        break;
}
if (null !== $tpl && !$isAjax) {
    /**
     * @var $render \trident\phpRender
     */
    $render = \trident\DI::get('render');
    $response->body($render->fetch($tpl));
}
$response->send();