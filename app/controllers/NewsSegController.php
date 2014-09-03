<?php

class NewsSegController extends BaseController {

	private function _changeResStruct($theRes, &$black_set, $use_blacklist = false) {
		$totalScore = 0;
		foreach ($theRes as $element)
			$totalScore += $element[1];

		$count = 0;
		$newRes = array();
		foreach ($theRes as $element) {
			if ( !$use_blacklist ) {
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

	public function index($date = null, $all_terms = null) {
		if ($all_terms === null) {
			$all_terms = Input::get('all');
		}

		return $this->_yieldView($date, $all_terms, null, null);
	}

	public function keywordTerms($keyword = null, $display = null, $date = null) {
		return $this->_yieldView($date, null, $keyword, $display);
	}

	private function _yieldView($date, $all_terms, $keyword, $display) {
		$black_set = array();

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
				for ($i=1; $i<=6; $i++) {
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

			for ($i=30; $i>=1; $i--) {
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
				$aveRate /= count($pastTimeResourses);

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

		return View::make('pure-bootstrap3.array-to-table', array('data' => $res, 'date' => $date, 'keyword' => $keyword, 'display' => $display));
   }

}
