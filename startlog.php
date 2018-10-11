<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "readconfig.php";

$systemLogger = new Logger('system');

$systemLogger->pushHandler(new StreamHandler($config['log_info'], Logger::INFO));

$exceptionLogger = new Logger('exception');

$exceptionLogger->pushHandler(new StreamHandler($config['log_exception'], Logger::ERROR));