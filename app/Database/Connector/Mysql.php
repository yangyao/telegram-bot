<?php
namespace Yangyao\TelegramBot\Database\Connector;
use Yangyao\TelegramBot\Database\Connector;
use PDO;
class Mysql extends Connector{

    private static $instance = array();

    private static function key($host, $port, $user) {
        return md5($host . $port . $user);
    }

    public static function connect(array $config){
        $host = $config['host'];
        $port = $config['port'];
        $user = $config['user'];
        $pass = $config['password'];
        $key = self::key($host, $port, $user);
        if (!isset(self::$instance[$key])) {
            try {
                $dsn = 'mysql:host=' . $host . ';port=' . $port.';dbname=' . $config['database'];
                $pdo = new PDO($dsn, $user, $pass, array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance[$key] = $pdo;
            }catch(\Exception $e) {
                throw new \Exception( __METHOD__. "connect to mysql failed, host={$host}, port={$port}|".$e->getMessage());
            }
        }

        return self::$instance[$key];
    }

    public static function close(array $config){
        $host = $config['host'];
        $port = $config['port'];
        $user = $config['user'];
        $key = self::key($host, $port, $user);
        unset(self::$instance[$key]);
    }

    public static function closeAll() {
        foreach(self::$instance as $k=>$v) {
            unset(self::$instance[$k]);
        }
    }
}