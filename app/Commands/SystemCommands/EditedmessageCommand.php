<?php


namespace Yangyao\TelegramBot\Commands\SystemCommands;

use Yangyao\TelegramBot\Commands\SystemCommand;

/**
 * Edited message command
 */
class EditedmessageCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'editedmessage';

    /**
     * @var string
     */
    protected $description = 'User edited message';

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
        //$update = $this->getUpdate();
        //$edited_message = $update->getEditedMessage();

        return parent::execute();
    }
}
