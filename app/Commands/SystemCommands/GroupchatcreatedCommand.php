<?php


namespace Yangyao\TelegramBot\Commands\SystemCommands;

use Yangyao\TelegramBot\Commands\SystemCommand;

/**
 * Group chat created command
 */
class GroupchatcreatedCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'groupchatcreated';

    /**
     * @var string
     */
    protected $description = 'Group chat created';

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
        //$group_chat_created = $message->getGroupChatCreated();

        return parent::execute();
    }
}
