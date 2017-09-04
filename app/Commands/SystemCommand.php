<?php


namespace Yangyao\TelegramBot\Commands;

use Yangyao\TelegramBot\Request;

abstract class SystemCommand extends Command
{
    /**
     * A system command just executes
     *
     * Although system commands should just work and return a successful ServerResponse,
     * each system command can override this method to add custom functionality.
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     */
    public function execute()
    {
        //System command, return empty ServerResponse by default
        return Request::emptyResponse();
    }
}
