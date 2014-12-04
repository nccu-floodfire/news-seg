<?php

$date = date('Y-m-d', strtotime('yesterday'));
if (array_key_exists(1, $argv)) {
	$date = $argv[1];
}
$redis = new Redis();

$redis->connect('127.0.0.1', 6379);
$key = 'CKIP:TERMS:' . $date;
$count = $redis->zCard($key);
$max_score_arr = $redis->zRevRange($key, 0, 0);
$max_score = 'unknown';
$max_score_term = 'unknown';
if (count($max_score_arr) === 1) {
	$max_score_term = $max_score_arr[0];
	$max_score = $redis->zScore($key, $max_score_arr[0]);
}
fwrite(STDERR, " - key=|$key|, count=$count, max_score_term={$max_score_term}, max_score=$max_score\n");
$res = $redis->zRemRangeByScore($key, 0, 20);
fwrite(STDERR, "$res terms removed\n\n");
exit (0);
