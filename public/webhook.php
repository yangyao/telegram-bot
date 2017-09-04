<?php

include "../bootstrap/start.php";

use Yangyao\TelegramBot\Telegram;
use GuzzleHttp\Client;
use Yangyao\TelegramBot\Commands\Schedule;
use Yangyao\TelegramBot\Database\Factory;

try {
    $client = new Client([
        'base_uri' => Telegram::$api_base_uri,
        'proxy'=> 'http://127.0.0.1:1080'
    ]);
    $schedule = new Schedule();
    $schedule->setCommandNamespace('\\Yangyao\\Telegram\\Commands\\');
    $schedule->enableAdmins([]);
    $telegram = new Telegram(getenv('BOT_TOKEN'), getenv('BOT_USERNAME'), $client, $schedule);
    $handler = Factory::handler(getenv('DB_CONNECTION'),$config = ['database' => dirname(dirname(__DIR__))."/storage/db/telegram.db"]);
    $telegram->setDatabaseHandler($handler);
    $telegram->handleWebhook();
} catch (Yangyao\TelegramBot\Exception\TelegramException $e) {
    echo $e->getMessage();
}