<?php
/**
 * Created by PhpStorm.
 * User: yangy
 * Date: 2017/8/31
 * Time: 21:26
 */

namespace Yangyao\TelegramBot\Commands;


use Yangyao\TelegramBot\Commands\AdminCommands\ChatsCommand;
use Yangyao\TelegramBot\Commands\AdminCommands\CleanupCommand;
use Yangyao\TelegramBot\Commands\AdminCommands\DebugCommand;
use Yangyao\TelegramBot\Commands\AdminCommands\SendtoallCommand;
use Yangyao\TelegramBot\Commands\AdminCommands\SendtochannelCommand;
use Yangyao\TelegramBot\Commands\AdminCommands\WhoisCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\ChannelchatcreatedCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\ChannelpostCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\ChoseninlineresultCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\DeletechatphotoCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\EditedmessageCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\GenericCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\GenericmessageCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\GroupchatcreatedCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\InlinequeryCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\LeftchatmemberCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\MigratetochatidCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\NewchatmembersCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\NewchatphotoCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\NewchattitleCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\PinnedmessageCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\StartCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\SupergroupchatcreatedCommand;
use Yangyao\TelegramBot\Entities\CallbackQuery;
use Yangyao\TelegramBot\Telegram;
use Yangyao\TelegramBot\Commands\SystemCommands\EditedchannelpostCommand;
use Yangyao\TelegramBot\Commands\SystemCommands\MigratefromchatidCommand;
class BuiltinRegistry extends Registry{

    public $telegram = null;

    public function __construct(Telegram $telegram){
        parent::__construct($telegram);
    }


} 