<?php
namespace Us\EventHandler;

class NewsHandler {
	public static function onCkipCallback($terms) {
		static $redis = null;
		if ($redis === null){
			$redis = \RedisL4::connection();
		}

		if (!is_array($terms) || count($terms) < 1 ) {
			\Log::error('Incorrect terms');
			return;
		}

		foreach ($terms as $item) {
			$term = $item['term'];
			// FIXME how to determine time
			$today = date('Y-m-d');
			// redis sorted set
			if (mb_strlen($term, 'utf8') < 2 ) {
				continue; // XXX skip single character term
			}
			$redis->zIncrBy('CKIP:TERMS:' . $today, 1, $term);

			// FIXME support blacklist
		}
		//$redis->incr('CKIP:STATUS:' . $today);
	}
}