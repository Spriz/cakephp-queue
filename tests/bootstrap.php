<?php
use Cake\Datasource\ConnectionManager;
use Cake\Routing\DispatcherFactory;
use TestApp\Controller\AppController;

define('DS', DIRECTORY_SEPARATOR);
if (!defined('WINDOWS')) {
	if (DS === '\\' || substr(PHP_OS, 0, 3) === 'WIN') {
		define('WINDOWS', true);
	} else {
		define('WINDOWS', false);
	}
}

define('ROOT', dirname(__DIR__));
define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('APP', sys_get_temp_dir());
define('APP_DIR', 'src');
define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . APP_DIR . DS);

define('WWW_ROOT', ROOT . DS . 'webroot' . DS);
define('CONFIG', dirname(__FILE__) . DS . 'config' . DS);

ini_set('intl.default_locale', 'de-DE');

require ROOT . '/vendor/autoload.php';
require CORE_PATH . 'config/bootstrap.php';

Cake\Core\Configure::write('App', [
	'namespace' => 'TestApp',
	'encoding' => 'UTF-8',
	'paths' => [
		'templates' => [ROOT . DS . 'tests' . DS . 'test_app' . DS . 'src' . DS . 'Template' . DS],
	]
]);

Cake\Core\Configure::write('debug', true);

Cake\Core\Configure::write('EmailTransport', [
		'default' => [
			'className' => 'Debug',
		],
]);
Cake\Core\Configure::write('Email', [
		'default' => [
			'transport' => 'default',
			'from' => 'you@localhost',
		],
]);

mb_internal_encoding('UTF-8');

$Tmp = new \Cake\Filesystem\Folder(TMP);
$Tmp->create(TMP . 'cache/models', 0770);
$Tmp->create(TMP . 'cache/persistent', 0770);
$Tmp->create(TMP . 'cache/views', 0770);

$cache = [
	'default' => [
		'engine' => 'File',
		'path' => CACHE,
	],
	'_cake_core_' => [
		'className' => 'File',
		'prefix' => 'crud_myapp_cake_core_',
		'path' => CACHE . 'persistent/',
		'serialize' => true,
		'duration' => '+10 seconds',
	],
	'_cake_model_' => [
		'className' => 'File',
		'prefix' => 'crud_my_app_cake_model_',
		'path' => CACHE . 'models/',
		'serialize' => 'File',
		'duration' => '+10 seconds',
	],
];

Cake\Cache\Cache::config($cache);

Cake\Core\Plugin::load('Queue', ['path' => ROOT . DS, 'autoload' => true, 'bootstrap' => false, 'routes' => true]);
Cake\Core\Plugin::load('Tools', ['path' => ROOT . DS . 'vendor' . DS . 'deuromark' . DS . 'cakephp-tools' . DS]);

DispatcherFactory::add('Routing');
DispatcherFactory::add('ControllerFactory');
class_alias(AppController::class, 'App\Controller\AppController');

Cake\Mailer\Email::configTransport('default', [
	'className' => 'Debug',
]);
Cake\Mailer\Email::configTransport('queue', [
	'className' => 'Queue.Queue',
]);
Cake\Mailer\Email::config('default', [
	'transport' => 'default',
]);

// Allow local overwrite
if (!getenv('db_class')) {
	//putenv('db_dsn=sqlite::memory:');
	Cake\Datasource\ConnectionManager::config('test', ['url' => getenv('db_dsn')]);
	return;
}

// Uses Travis config then (MySQL, Postgres, ...)
Cake\Datasource\ConnectionManager::config('test', [
	'className' => 'Cake\Database\Connection',
	'driver' => getenv('db_class'),
	'dsn' => getenv('db_dsn'),
	'database' => getenv('db_database'),
	'username' => getenv('db_username'),
	'password' => getenv('db_password'),
	'timezone' => 'UTC',
	'quoteIdentifiers' => true,
	'cacheMetadata' => true,

]);