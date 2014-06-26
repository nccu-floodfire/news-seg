<?php

class NewsSegController extends BaseController {

	private function _changeResStruct($theRes, &$black_set, $use_blacklist = false) {
		$count = 0;
		$newRes = array();
		foreach ($theRes as $element) {
			if ( !$use_blacklist ) {
				$element['term'] = $element[0];
				$element['score'] = $element[1];
				$element['rank'] = ++$count;
				$newRes[$element[0]] = $element;
			} else if (!isset($black_set[$element[0]]) && !is_numeric($element[0])) {
				$element['term'] = $element[0];
				$element['score'] = $element[1];
				$element['rank'] = ++$count;
				$newRes[$element[0]] = $element;
			}
		}
		return $newRes;
	}

	public function index($date = null, $all_terms = null) {
		$black_set = array();

		if (!$date) {
			$date = Input::get('date');
			$date = trim($date);
		}

		if ($all_terms === null) {
			$all_terms = Input::get('all');
		}

		if (!$date) {
			$date = date('Y-m-d');
		}
		$prevDate = date('Y-m-d', strtotime("{$date} - 1 days"));

		$redis = \RedisL4::connection();
		$prevRes = array();

		$res = $redis->zRevRange("CKIP:TERMS:$date", 0, 600, 'WITHSCORES');

		if (count($res) > 0) {
			$prevRes = $redis->zRevRange("CKIP:TERMS:$prevDate", 0, 600, 'WITHSCORES');
			foreach ($redis->sMembers('CKIP:TERMS:BLACK_SET') as $element)
				$black_set[$element] = '';

			if ($all_terms) {
				$prevRes = $this->_changeResStruct($prevRes, $black_set);
				$res = $this->_changeResStruct($res, $black_set);
			} else {
				$prevRes = $this->_changeResStruct($prevRes, $black_set, true);
				$res = $this->_changeResStruct($res, $black_set, true);
			}

			foreach ($res as &$element) {
				if (isset($prevRes[$element['term']])) {
					$element['rankDiff'] = $prevRes[$element['term']]['rank'] - $element['rank'];
				} else {
					$element['rankDiff'] = '---';
				}
			}
		} else {
			// no data
		}

		return View::make('pure-bootstrap3.array-to-table', array('data' => $res, 'date' => $date));
	}

}