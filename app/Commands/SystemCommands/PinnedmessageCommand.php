<?php


namespace Yangyao\TelegramBot\Commands\SystemCommands;

use Yangyao\TelegramBot\Commands\SystemCommand;

/**
 * Pinned message command
 */
class PinnedmessageCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'pinnedmessage';

    /**
     * @var string
     */
    protected $description = 'Message was pinned';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Execute command
     *
     * @return mixed
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        //$message = $this->getMessage();
        //$pinned_message = $message->getPinnedMessage();

        return parent::execute();
    }
}
