<?php
/**
 * Created by PhpStorm.
 * User: yangy
 * Date: 2017/8/31
 * Time: 21:26
 */

namespace Yangyao\TelegramBot\Commands;

use Yangyao\TelegramBot\Telegram;

abstract class Registry {

    public $telegram = null;
    public $command_list = [];


    public function __construct(Telegram $telegram){
        $this->telegram = $telegram;
        $this->registerBuiltinCommands();
        $this->registerCustomCommands();
    }


    public function registerBuiltinCommands(){

        $this->command_list = [
          new AdminCommands\ChatsCommand($this->telegram),
          new AdminCommands\CleanupCommand($this->telegram),
          new AdminCommands\DebugCommand($this->telegram),
          new AdminCommands\SendtoallCommand($this->telegram),
          new AdminCommands\SendtochannelCommand($this->telegram),
          new AdminCommands\WhoisCommand($this->telegram),
          new AdminCommands\ChatsCommand($this->telegram),
          new SystemCommands\CallbackqueryCommand($this->telegram),
          new SystemCommands\ChannelchatcreatedCommand($this->telegram),
          new SystemCommands\ChannelpostCommand($this->telegram),
          new SystemCommands\ChoseninlineresultCommand($this->telegram),
          new SystemCommands\DeletechatphotoCommand($this->telegram),
          new SystemCommands\EditedchannelpostCommand($this->telegram),
          new SystemCommands\EditedmessageCommand($this->telegram),
          new SystemCommands\GenericCommand($this->telegram),
          new SystemCommands\GenericmessageCommand($this->telegram),
          new SystemCommands\GroupchatcreatedCommand($this->telegram),
          new SystemCommands\InlinequeryCommand($this->telegram),
          new SystemCommands\LeftchatmemberCommand($this->telegram),
          new SystemCommands\MigratefromchatidCommand($this->telegram),
          new SystemCommands\MigratetochatidCommand($this->telegram),
          new SystemCommands\NewchatmembersCommand($this->telegram),
          new SystemCommands\NewchatphotoCommand($this->telegram),
          new SystemCommands\NewchattitleCommand($this->telegram),
          new SystemCommands\PinnedmessageCommand($this->telegram),
          new SystemCommands\StartCommand($this->telegram),
          new SystemCommands\SupergroupchatcreatedCommand($this->telegram),
        ];
    }

    protected function registerCustomCommands(){}

} 