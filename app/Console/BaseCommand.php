<?php
// +----------------------------------------------------------------------
// | Author: 杨尧 <yangyao@sailvan.com>
// +----------------------------------------------------------------------

namespace Yangyao\TelegramBot\Console;


use Yangyao\TelegramBot\Commands\Schedule;
use Symfony\Component\Console\Command\Command;
use Yangyao\TelegramBot\Limiter;
use Yangyao\TelegramBot\Telegram;
use GuzzleHttp\Client;
use Yangyao\TelegramBot\Request;
use Yangyao\TelegramBot\Database\Factory;
class BaseCommand extends Command
{
    /**@var Telegram $telegram */
    public $telegram = null;

    public function __construct($name = null)
    {
        $client = new Client([
            'base_uri' => Telegram::$api_base_uri,
            'proxy'=> 'http://127.0.0.1:1080'
        ]);
        $schedule = new Schedule();
        $schedule->setCommandNamespace('\\Yangyao\\Telegram\\Commands\\');
        $schedule->enableAdmins([]);
        $this->telegram = new Telegram(getenv('BOT_TOKEN'), getenv('BOT_USERNAME'));
        $handler = Factory::handler(getenv('DB_CONNECTION'),$config = ['database' => dirname(dirname(__DIR__))."/storage/db/telegram.db"]);
        $this->telegram->setDatabaseHandler($handler);
        Request::initialize($this->telegram,$client);
        Request::setLimiter(new Limiter($handler));
        parent::__construct($name);
    }

}