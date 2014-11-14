<?php
namespace Us\Job;
use Us\Helper\CKIPClient;
use Config;

class CkipJob {
	public function fire($job, $dataArr) {
		$redis = \RedisL4::connection();
		$today = date("Y-m-d", time());
		$yesterday = date("Y-m-d", strtotime(($today - ' 1 day')));

		$res = $redis->zRevRange("CKIP:TERMS:$yesterday", 0, 200, 'WITHSCORES');
		foreach ($res as $item) {
			$term = $item[0];
			$score = $item[1];
		}
	}
}
