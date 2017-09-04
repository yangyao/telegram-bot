<?php


namespace Yangyao\TelegramBot\Commands;

abstract class AdminCommand extends Command
{
    /**
     * @var bool
     */
    protected $private_only = true;
}
