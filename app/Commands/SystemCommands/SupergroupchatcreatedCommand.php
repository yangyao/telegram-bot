<?php


namespace Yangyao\TelegramBot\Commands\SystemCommands;

use Yangyao\TelegramBot\Commands\SystemCommand;

/**
 * Super group chat created command
 */
class SupergroupchatcreatedCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'supergroupchatcreated';

    /**
     * @var string
     */
    protected $description = 'Super group chat created';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Command execute method
     *
     * @return mixed
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        //$message = $this->getMessage();
        //$supergroup_chat_created = $message->getSuperGroupChatCreated();

        return parent::execute();
    }
}
