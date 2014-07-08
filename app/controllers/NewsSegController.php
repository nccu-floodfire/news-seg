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

		$redis = \RedisL4::connection();
		$dataNum = 1000;
		$res = $redis->zRevRange("CKIP:TERMS:$date", 0, $dataNum, 'WITHSCORES');
		if (count($res) > 0) {
			$prevDate = date('Y-m-d', strtotime("$date - 1 days"));
			$prevRes = $redis->zRevRange("CKIP:TERMS:$prevDate", 0, $dataNum, 'WITHSCORES');

			foreach ($redis->sMembers('CKIP:TERMS:BLACK_SET') as $element)
				$black_set[$element] = '';

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
				$theRes = $this->_changeResStruct($theRes, $black_set, true);
				$pastTimeResourses[] = $theRes;
			}

			foreach ($res as &$element) {
				$aveRate = 0;
				foreach ($pastTimeResourses as $pastTimeRes) {
					if (isset($pastTimeRes[$element['term']]))
						$aveRate += $pastTimeRes[$element['term']]['rate'];
				}
				$aveRate /= count($pastTimeResourses);
				if ($aveRate == 0 or $element['rate'] / $aveRate >= 4)
					$element['isHot'] = true;
				else
					$element['isHot'] = false;

				if (isset($prevRes[$element['term']]))
					$element['rankDiff'] = $prevRes[$element['term']]['rank'] - $element['rank'];
				else
					$element['rankDiff'] = '---';
			}
		} else {
			// no data
		}

		return View::make('pure-bootstrap3.array-to-table', array('data' => $res, 'date' => $date));
	}

}
