

Redis是一个开源的使用ANSI C语言编写、支持网络、可基于内存亦可持久化的日志型、Key-Value数据库，并提供多种语言的API。

本文将使用其incr(自增)，get(获取)，delete(清除)方法来实现计数器类。 


1.Redis计数器类代码及演示实例
RedisCounter.class.php

<?php
/** 
 *  PHP基于Redis计数器类
 *  Date:    2017-10-28 *
 *  Author:  fdipzone 
 *  Version: 1.0
 *  Descripton:
 *  php基于Redis实现自增计数，主要使用redis的incr方法，并发执行时保证计数自增唯一。
 *  Func: * public  incr    执行自增计数并获取自增后的数值
 *  public  get     获取当前计数 
 *  public  reset   重置计数
 *  private connect 创建redis连接 
 */
class RedisCounter{
 // class start    
	private $_config; 
   	private $_redis; 
   	/** 
   	 * 初始化
   	 * @param Array $config redis连接设定
   	 */ 
   	public function __construct($config){ 
   	    $this->_config = $config;
   	    $this->_redis = $this->connect();
   	} 
   	/**
   	 * 执行自增计数并获取自增后的数值 
   	 * @param  String $key  保存计数的键值
   	 * @param  Int    $incr 自增数量，默认为1 
   	 * @return Int    
   	 */ 
   	public function incr($key, $incr=1){ 
   	       return intval($this->_redis->incr($key, $incr));
   	}
   	/**
   	  * 获取当前计数
   	  *  @param  String $key 保存计数的健值
   	  *  @return Int
   	   */
   	public function get($key){
   	    return intval($this->_redis->get($key));
   	}   
   	/**
   	 * 重置计数
   	 * @param  String  $key 保存计数的健值 
   	 * @return Int
   	 */
   	public function reset($key){
   	    return $this->_redis->delete($key);
   	}
   	/**
   	 *创建redis连接
   	 *@return Link 
   	 */ 
   	private function connect(){
   	    try{
   	       $redis = new Redis();
   	       $redis->connect($this->_config['host'],$this->_config['port'],$this->_config['timeout'],$this->_config['reserved'],$this->_config['retry_interval']);
   	        if(empty($this->_config['auth'])){
   	            $redis->auth($this->_config['auth']);
   	         }
   	        $redis->select($this->_config['index']);
   	    }catch(RedisException $e){            throw new Exception($e->getMessage());            return false;        }return $redis;
   	    }}
   	    // class end
 ?>
 */
demo.php

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
输出：

011110
2.并发调用计数器，检查计数唯一性
测试代码如下：

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
测试并发执行，我们使用ab工具进行测试，设置执行150次，15个并发。

ab -c 15 -n 150 http://localhost/test.php
执行结果：

ab -c 15 -n 150 http://localhost/test.php
This is ApacheBench, Version 2.3 <$Revision: 1554214 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/Benchmarking home.rabbit.km.com (be patient).....doneServer Software:        nginx/1.6.3Server Hostname:        localhostServer Port:            80Document Path:          /test.phpDocument Length:        0 bytesConcurrency Level:      15Time taken for tests:   0.173 secondsComplete requests:      150Failed requests:        0Total transferred:      24150 bytesHTML transferred:       0 bytesRequests per second:    864.86 [#/sec] (mean)Time per request:       17.344 [ms] (mean)Time per request:       1.156 [ms] (mean, across all concurrent requests)Transfer rate:          135.98 [Kbytes/sec] receivedConnection Times (ms)              min  mean[+/-sd] median   maxConnect:        0    0   0.2      0       1Processing:     3   16   3.2     16      23Waiting:        3   16   3.2     16      23Total:          4   16   3.1     17      23Percentage of the requests served within a certain time (ms)  50%     17  66%     18  75%     18  80%     19  90%     20  95%     21  98%     22  99%     22 100%     23 (longest request)
检查计数是否唯一

生成的总计数wc -l /tmp/mytest_result.log      150 /tmp/mytest_result.log生成的唯一计数sort -u /tmp/mytest_result.log | wc -l     150
可以看到在并发调用的情况下，生成的计数也保证唯一。 
