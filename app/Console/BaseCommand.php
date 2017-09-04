<?php
// +----------------------------------------------------------------------
// | Author: 杨尧 <yangyao@sailvan.com>
// +----------------------------------------------------------------------

namespace Yangyao\TelegramBot\Console;


use Yangyao\TelegramBot\Commands\Schedule;
use Symfony\Component\Console\Command\Command;
use Yangyao\TelegramBot\Telegram;
use GuzzleHttp\Client;
class BaseCommand extends Command
{
    protected $telegram = null;

    public function __construct($name = null)
    {
        $client = new Client([
            'base_uri' => Telegram::$api_base_uri,
            'proxy'=> 'http://127.0.0.1:1080'
        ]);
        $schedule = new Schedule();
        $schedule->setCommandNamespace('\\Yangyao\\Telegram\\Commands\\');
        $schedule->enableAdmins([]);
        $this->telegram = new Telegram(getenv('BOT_TOKEN'), getenv('BOT_USERNAME'), $client, $schedule);
        parent::__construct($name);
    }

}