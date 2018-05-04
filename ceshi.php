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
  $key = 'mytestcounter';
  // 执行自增计数并返回自增后的计数，记录入临时文件
  file_put_contents('/tmp/mytest_result.log', $oRedisCounter->incr($key).PHP_EOL, FILE_APPEND);
?>