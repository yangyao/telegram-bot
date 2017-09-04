<?php
// +----------------------------------------------------------------------
// | Author: 杨尧 <yangyao@sailvan.com>
// +----------------------------------------------------------------------

namespace Yangyao\TelegramBot\Database\Handler;
use PDO;
class MysqlHandler extends PdoHandler
{
    public function __construct(PDO $db) {
            parent::__construct($db);
    }
}