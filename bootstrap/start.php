<?php
$base_path = dirname(__DIR__);
include $base_path.'/vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(dirname(__DIR__));
$dotenv->load();

