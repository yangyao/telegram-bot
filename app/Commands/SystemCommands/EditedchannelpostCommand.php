<?php


namespace Yangyao\TelegramBot\Commands\SystemCommands;

use Yangyao\TelegramBot\Commands\SystemCommand;

/**
 * Edited channel post command
 */
class EditedchannelpostCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'editedchannelpost';

    /**
     * @var string
     */
    protected $description = 'Handle edited channel post';

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
        //$edited_channel_post = $this->getUpdate()->getEditedChannelPost();

        return parent::execute();
    }
}
