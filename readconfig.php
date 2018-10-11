<?php

use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

$config = [
    'client_id'           => '',
    'client_secret'       => '',
    'db_host'             => '',
    'db_user'             => 'root',
    'db_port'             => '3306',
    'db_password'         => '',
    'db_list'             => [],
    'db_compress'         => true,
    'gd_credentials_path' => './credentials.json',
    'gd_token_path'       => './token.json',
    'gd_root_folder_id'   => '',
    'log_info'            => './info.log',
    'log_exception'       => './exception.log',
];

$config = array_merge($config, Yaml::parseFile(__DIR__ . DIRECTORY_SEPARATOR . "configuration.yaml"));