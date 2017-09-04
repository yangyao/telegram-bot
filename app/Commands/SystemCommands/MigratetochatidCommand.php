<?php


namespace Yangyao\TelegramBot\Commands\SystemCommands;

use Yangyao\TelegramBot\Commands\SystemCommand;

/**
 * Migrate to chat id command
 */
class MigratetochatidCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'migratetochatid';

    /**
     * @var string
     */
    protected $description = 'Migrate to chat id';

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
        //$migrate_to_chat_id = $message->getMigrateToChatId();

        return parent::execute();
    }
}
