<?php
Require 'RedisCounter.class.php';
// redis连接设定
$config = array( 
   'host' => 'localhost',
   'port' => 6379,
   'index' => 0,
   'auth' => '',
   'timeout' => 1,
   'reserved' => NULL,
   'retry_interval' => 100,
   );
   // 创建RedisCounter对象
  $oRedisCounter = new RedisCounter($config);
  // 定义保存计数的健值
   $key = 'mycounter';
   // 执行自增计数，获取当前计数，重置计数
   echo $oRedisCounter->get($key).PHP_EOL;
    // 0echo 
     $oRedisCounter->incr($key).PHP_EOL;
      // 1echo 
     $oRedisCounter->incr($key, 10).PHP_EOL; 
     // 11echo 
     $oRedisCounter->reset($key).PHP_EOL; 
     // 1echo 
     $oRedisCounter->get($key).PHP_EOL;
      // 0 
?>