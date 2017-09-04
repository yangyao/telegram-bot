<?php
// +----------------------------------------------------------------------
// | Author: 杨尧 <yangyao@sailvan.com>
// +----------------------------------------------------------------------

namespace Yangyao\TelegramBot\Database;


class Factory
{

    /**
     * @param $driver string
     * @param $config array
     * @return \Yangyao\TelegramBot\Database\Handler\HandlerInterface
     */
    public static function handler($driver,$config){
        /**@var \Yangyao\TelegramBot\Database\Handler\HandlerInterface $handlerClass */
        $handlerClass = '\\Yangyao\\TelegramBot\\Database\\Handler\\'.ucfirst($driver).'Handler';
        /**@var \Yangyao\TelegramBot\Database\Connector $connectorClass */
        $connectorClass = '\\Yangyao\\TelegramBot\\Database\\Connector\\'.ucfirst($driver);

        return new $handlerClass($connectorClass::connect($config));

    }

}