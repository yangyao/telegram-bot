<?php
namespace Yangyao\TelegramBot\Database\Connector;
use Yangyao\TelegramBot\Database\Connector;
use Memcached;
use Exception;
class Memcache extends Connector {

    private static $instance = array();

    private static function key($host, $port) {
        return md5($host . ':' . $port);
    }

    public static function connect(array $config){
        $host = $config['host'];
        $port = $config['port'];
        $key = self::key($host, $port);
        if (!isset(self::$instance[$key])) {
            $memcache = new Memcached($host . $port);
            if(count($memcache->getServerList()) == 0){
                $ret = $memcache->addServer($host, $port);
                if (!$ret) {
                    throw new Exception( __METHOD__. "connect to memcache failed, host={$host}, port={$port}");
                }
                $memcache->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
                $memcache->setOption(Memcached::OPT_SERIALIZER, Memcached::SERIALIZER_IGBINARY);
                $memcache->setOption(Memcached::OPT_TCP_NODELAY, true);
            }
            self::$instance[$key] = $memcache;
        }

        return self::$instance[$key];
    }

    public static function close($host, $port){
        //no single close api?
        $key = self::key($host, $port);
        unset(self::$instance[$key]);
    }

    public static function closeAll() {
        foreach(self::$instance as $k=>$v) {
            unset(self::$instance[$k]);
        }
    }
}