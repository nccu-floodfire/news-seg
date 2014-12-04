<?php
use \Illuminate\Support\Facades\Cache;

class NewsSegController extends BaseController
{

	private function _changeResStruct($theRes, &$black_set, $use_blacklist = false)
	{
		$totalScore = 0;
		if ($use_blacklist) {
			foreach ($theRes as $element) {
				if (!isset($black_set[$element[0]]) && !is_numeric($element[0])) {
					$totalScore += $element[1];
				}
			}
		} else {
			foreach ($theRes as $element) {
				$totalScore += $element[1];
			}
		}

		$count = 0;
		$newRes = array();
		foreach ($theRes as $element) {
			if (!$use_blacklist) {
				$element['term'] = $element[0];
				$element['score'] = $element[1];
				$element['rank'] = ++$count;
				$element['rate'] = $element['score'] / $totalScore;
				$newRes[$element[0]] = $element;
			} else if (!isset($black_set[$element[0]]) && !is_numeric($element[0])) {
				$element['term'] = $element[0];
				$element['score'] = $element[1];
				$element['rank'] = ++$count;
				$element['rate'] = $element['score'] / $totalScore;
				$newRes[$element[0]] = $element;
			}
		}
		return $newRes;
	}

	public function index($date = null, $all_terms = null)
	{
		if ($all_terms === null) {
			$all_terms = Input::get('all');
		}

		// 產生每日 json file
		$generate_json_report = Input::get('json', false);
		if ($generate_json_report) {
			$generate_json_report = true;
		}

		return $this->_yieldView($date, $all_terms, null, null, $generate_json_report);
	}

	// api
	public function hotlinks($date = null) {
		$generate_json_report = true;

		return $this->_yieldView($date, null, null, null, $generate_json_report);
	}

	public function apiAllTerms($date)
	{
		$generate_json_report = true;
		return $this->_yieldView($date, null, null, null, $generate_json_report, 0);
	}

	public function clearcache($date = null) {
		if ($date === null) {
			$date = date('Y-m-d', time());
		}
		$cache_key = 'api-hotlinks-' . $date;
		$res = Cache::pull($cache_key);
		return $res;
	}

	public function keywordTerms($keyword = null, $display = null, $date = null)
	{
		return $this->_yieldView($date, null, $keyword, $display);
	}

	private function _yieldView($date, $all_terms, $keyword, $display, $is_generate_json_report = false, $heat_score_limitation = 4)
	{
		$black_set = array();
		$pastTimeResourses = array();

		if (!$display) {
			$display = 'day';
		}

		if (!$date) {
			$date = Input::get('date');
			$date = trim($date);
		}

		if (!$date) {
			$date = date('Y-m-d');
		}

		$redis = \RedisL4::connection();
		$dataNum = 1000;
		if ($display === 'day') {
			if (isset($keyword)) {
				$res = $redis->zRevRange("CKIP:TERMS:$keyword:$date", 0, $dataNum, 'WITHSCORES');
			} else {
				$res = $redis->zRevRange("CKIP:TERMS:$date", 0, $dataNum, 'WITHSCORES');
			}
		} else if ($display === 'week') {
			if (isset($keyword)) {
				$redis->zUnionStore('CKIP:TERMS:TEMP', 1, "CKIP:TERMS:$keyword:$date");
				for ($i = 1; $i <= 6; $i++) {
					$nowDate = date('Y-m-d', strtotime("$date - $i days"));
					$redis->zUnionStore('CKIP:TERMS:TEMP', 2, 'CKIP:TERMS:TEMP', "CKIP:TERMS:$keyword:$nowDate");
				}
				$res = $redis->zRevRange("CKIP:TERMS:TEMP", 0, $dataNum, 'WITHSCORES');
			} else {
				$res = $redis->zRevRange("CKIP:TERMS:$date", 0, $dataNum, 'WITHSCORES');
			}
		}
		if (count($res) > 0) {
			$prevDate = date('Y-m-d', strtotime("$date - 1 days"));
			if (isset($keyword)) {
				$prevRes = $redis->zRevRange("CKIP:TERMS:$keyword:$prevDate", 0, $dataNum, 'WITHSCORES');
			} else {
				$prevRes = $redis->zRevRange("CKIP:TERMS:$prevDate", 0, $dataNum, 'WITHSCORES');
			}

			foreach ($redis->sMembers('CKIP:TERMS:BLACK_SET') as $element) {
				$black_set[$element] = '';
			}

			if ($all_terms) {
				$prevRes = $this->_changeResStruct($prevRes, $black_set);
				$res = $this->_changeResStruct($res, $black_set);
			} else {
				$prevRes = $this->_changeResStruct($prevRes, $black_set, true);
				$res = $this->_changeResStruct($res, $black_set, true);
			}

			for ($i = 30; $i >= 1; $i--) {
				$theDate = date('Y-m-d', strtotime("$date - $i days"));
				$theRes = $redis->zRevRange("CKIP:TERMS:$theDate", 0, $dataNum, 'WITHSCORES');
				if (count($theRes) != 0) {
					$theRes = $this->_changeResStruct($theRes, $black_set, true);
					$pastTimeResourses[] = $theRes;
				}
			}

			foreach ($res as &$element) {
				$aveRate = 0;
				foreach ($pastTimeResourses as $pastTimeRes) {
					if (isset($pastTimeRes[$element['term']]))
						$aveRate += $pastTimeRes[$element['term']]['rate'];
				}
				$countPastTimeResources = count($pastTimeResourses);
				if ($countPastTimeResources) {
					$aveRate /= $countPastTimeResources;
				}

				if ($aveRate == 0) {
					$element['heatScore'] = 'NEW';
				} else {
					$element['heatScore'] = round($element['rate'] / $aveRate, 2);
				}

				if ($element['heatScore'] == 0 or $element['heatScore'] >= 4) {
					$element['isHot'] = true;
				} else {
					$element['isHot'] = false;
				}

				if (isset($prevRes[$element['term']])) {
					$element['rankDiff'] = $prevRes[$element['term']]['rank'] - $element['rank'];
				} else {
					$element['rankDiff'] = '---';
				}
			}
		} else {
			// no data
		}

		// JSON API

		$json = array();
		$json['is_ready'] = false;
		$use_cache = false;
		if ($is_generate_json_report && $heat_score_limitation !== 0) {
			$use_cache = true;
		}
		if ($is_generate_json_report) {
			$with_link = Input::get('with-link', false);
			$cache_key = 'api-hotlinks-' . $date;
			if (Cache::has($cache_key) && $use_cache) {
				$json = Cache::get($cache_key);
				$json['from_cache'] = true;
				return Response::json($json);
			}
			$res_data = array();
			foreach ($res as $item) {
				if ($item['heatScore'] == 'NEW') {
					$item['heatScore'] = 300;
				}
				if ($item['heatScore'] >= $heat_score_limitation && $item['rank'] <= 200) {
					unset($item[0]);
					unset($item[1]);
					unset($item['isHot']);
					$item['count'] = (int)$item['score'];

					unset($item['score']);

					if ($item['rankDiff'] == '---') {
						$item['rankDiff'] = 0;
					}
					$item['rank_diff'] = $item['rankDiff'];
					unset($item['rankDiff']);

					$item['heat'] = $item['heatScore'];
					unset($item['heatScore']);

					array_push($res_data, $item);
				}

			}

			usort($res_data, function($a, $b) {
				return ($b['heat'] - $a['heat']);
			});
			foreach ($res_data as $k => $item) {
				if (!$with_link) {
					break;
				}
				$term = $item['term'];
				$res_data[$k]['news'] = array();
				$today = date('Y-m-d', time());
				$start_ts = strtotime($today . ' - 1 day');
				$end_ts = $start_ts + 86400 - 1;


				$data = NewsInfo::with('news')
					->whereRaw("time between $start_ts and $end_ts")
					->where('title', 'like', "%$term%") // fulltext search on title only, to improve performance.
					->get();

				foreach ($data as $each_search_res) {
					$d = $each_search_res->toArray();
					$news = array(
						'title' => $d['title'],
						'time' => (int) $d['time'],
					);
					if (array_key_exists('news', $d)) {
						$news['url'] = $d['news']['url'];
						$news['source'] = (int) $d['news']['source'];
						$news['share_count'] = $d['news']['share_count'];
						$news['comment_count'] = $d['news']['comment_count'];
					}
					array_push($res_data[$k]['news'], $news);

				}
			}

			$json['date'] = $date;
			$json['data'] = $res_data;
			if ($with_link && $use_cache) {
				$json['is_ready'] = true;
				Cache::forever($cache_key, $json);
			}
			return Response::json($json);
		}

		return View::make('pure-bootstrap3.array-to-table', array('data' => $res, 'date' => $date, 'keyword' => $keyword, 'display' => $display));
	}

}
