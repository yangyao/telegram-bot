<?php
// +----------------------------------------------------------------------
// | Author: 杨尧 <yangyao@sailvan.com>
// +----------------------------------------------------------------------

namespace Yangyao\TelegramBot\Console;


use Yangyao\TelegramBot\Database;
use Yangyao\TelegramBot\Console\BaseCommand as Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yangyao\TelegramBot\Exception\TelegramException;
use Yangyao\TelegramBot\Database\Factory;

class GetUpdateCommand extends Command
{
    public function configure()
    {
        $this->setName("update:get")
            ->setDescription("get update form telegram server only once !");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $handler = Factory::handler(getenv('DB_CONNECTION'),$config = ['database' => dirname(dirname(__DIR__))."/storage/db/telegram.db"]);
            $this->telegram->setDatabaseHandler($handler);
            // Handle telegram getUpdates request
            $server_response = $this->telegram->handleUpdates();
            if ($server_response->isOk()) {
                $update_count = count($server_response->getResult());
                echo date('Y-m-d H:i:s', time()) . ' - Processed ' . $update_count . ' updates';
            } else {
                echo date('Y-m-d H:i:s', time()) . ' - Failed to fetch updates' . PHP_EOL;
                echo $server_response->printError();
            }
        } catch (TelegramException $e) {
            echo $e->getMessage();
        }

    }

}