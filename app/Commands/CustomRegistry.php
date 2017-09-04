<?php
/**
 * Created by PhpStorm.
 * User: yangy
 * Date: 2017/8/31
 * Time: 22:12
 */

namespace Yangyao\TelegramBot\Commands;


use Yangyao\TelegramBot\Commands\Registry;
use Yangyao\TelegramBot\Telegram;
use Yangyao\TelegramBot\Commands\SystemCommands;
use Yangyao\TelegramBot\Commands\UserCommands;
class CustomRegistry extends Registry{

    public function __construct(Telegram $telegram){
        parent::__construct($telegram);
    }

    protected function registerCustomCommands(){

        $this->command_list = array_merge($this->command_list,[
            new SystemCommands\StartCommand($this->telegram),
            new UserCommands\WhoamiCommand($this->telegram),
            new UserCommands\HelpCommand($this->telegram)
        ]);

    }

} 