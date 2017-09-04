<?php
namespace Yangyao\TelegramBot\Database\Connector;
use Yangyao\TelegramBot\Database\Connector;
use PDO;
class Sqlite extends Connector{

    private static $instance = array();

    private static function key($database) {
        return md5($database);
    }

    public static function connect(array $config){
        $database = $config['database'];
        $key = self::key($database);
        if (!isset(self::$instance[$key])) {
            try {
                $dsn = 'sqlite:' . $config['database'];
                if(isset($config['option'])){
                    $pdo = new PDO($dsn,$config['option']);
                }else{
                    $pdo = new PDO($dsn);
                }
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance[$key] = $pdo;
            }catch(\Exception $e) {
                throw new \Exception( __METHOD__. "connect to sqlite failed, database = $database|".$e->getMessage());
            }
        }

        return self::$instance[$key];
    }

    public static function close(array $config){
        $key = self::key($config['database']);
        unset(self::$instance[$key]);
    }

    public static function closeAll() {
        foreach(self::$instance as $k=>$v) {
            unset(self::$instance[$k]);
        }
    }
}