<?php
// +----------------------------------------------------------------------
// | Author: 杨尧 <yangyao@sailvan.com>
// +----------------------------------------------------------------------

namespace Yangyao\TelegramBot\Database;


abstract  class Connector
{
    /**
     * @param array $config
     * @return self
     */
    public static function connect(array $config){}

    public static function close(array $config){}

    public static function closeAll(){}

}