<?php


namespace Yangyao\TelegramBot\Commands\SystemCommands;

use Yangyao\TelegramBot\Commands\SystemCommand;

/**
 * Channel chat created command
 */
class ChannelchatcreatedCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'channelchatcreated';

    /**
     * @var string
     */
    protected $description = 'Channel chat created';

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
        //$channel_chat_created = $message->getChannelChatCreated();

        return parent::execute();
    }
}
