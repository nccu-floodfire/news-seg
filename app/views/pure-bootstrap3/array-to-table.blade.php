@extends('pure-bootstrap3.layouts.default')
<style>
table a:link	{color:#999;}
table a:visited {color:#999;}
table a:hover   {color:#999;}
table a:active  {color:#999;}
.hot {color: #B71C1C;}
</style>
@section('main')
<?php
	if ($display === 'day') {
		$prev = date('Y-m-d', strtotime("{$date} - 1 days"));
		$next = date('Y-m-d', strtotime("{$date} + 1 days"));
	} else if ($display === 'week') {
		$prev = date('Y-m-d', strtotime("{$date} - 7 days"));
		$next = date('Y-m-d', strtotime("{$date} + 7 days"));
		$startDate = date('Y-m-d', strtotime("{$date} - 6 days"));
		$date = "$startDate ~ $date";
	}
	if (isset($keyword)) {
		$prev = "keyword-terms/$keyword/$display/$prev";
		$next = "keyword-terms/$keyword/$display/$next";
	}
	$date_search = str_replace('-', '/', $date);
?>
<button class="btn" data-toggle="modal" data-target="#help-modal">
	help
</button>

<div class="row">
	<div class="col-md-12">
		@if (isset($keyword))
			@if ($display == "day")
				<span>日顯示</span>
				<a href="/keyword-terms/{{{ $keyword }}}/week">週顯示</a>
			@elseif ($display == "week")
				<a href="/keyword-terms/{{{ $keyword }}}/day">日顯示</a>
				<span>週顯示</span>
			@endif
		@endif
		<h3>
			<a href="/{{{$prev}}}">prev <span class="glyphicon glyphicon-chevron-left"></span></a>
			{{{ $date }}}
			<a href="/{{{$next}}}"><span class="glyphicon glyphicon-chevron-right"></span> next </a>
		</h3>
		<?php
		if (count($data) > 300) {
			$data_rate = array_map(function ($ar) {return $ar['rate']*100;}, $data);
			$data_rate = array_slice($data_rate, 0, 300);
			$sd_rate = round(sd($data_rate), 2);
			$average = round(array_sum($data_rate) / count($data_rate), 2);
		} else {
			$sd_rate = 'N/A';
			$average = 'N/A';
		}

		//$data_heat = array_map(function ($ar) {return $ar['heatScore'];}, $data);
		?>
		<h4>出現率標準差: <b>{{{ $sd_rate }}}%</b></h4>
		<h4>出現率平均數: <b>{{{ $average }}}%</b></h4>
		<div style="line-height: 200%;">
		@foreach ($data as $key => $val)
		<?php
		$heat = $val['heatScore'];
		$gb = 38;
		$r = 205;
		if ($heat == 'NEW') {
			$gb = 0;
			$r = 100;
		} else {
			$gb = 205 - (205 / 96 * $heat * $val['rate'] * 10000 / 5);
		}
		$gb = round($gb);
		$g = $gb;
		$b = $gb;
		if ($gb < 0) {
			$b = -$gb;
			$g = 0;
		}
		if ($b > 204) {
			$r = $r - ($b - 204);
			$b = 204;
			if ($r < 100) {
				$r = 100;
			}
		}
		if ($heat == 'NEW') {
			$r = 100;
			$g = 0;
			$b = 204;
        }
		?>
		@if (( $val['heatScore'] >= 4 || $val['heatScore'] == 'NEW') && $val['rank'] <= 300)
		<span class="label" style="background-color: rgb({{{$r}}},{{{$g}}},{{{$b}}});" onclick="window.location.href='#{{{$val['rank']}}}'">{{{$val['term']}}}</span>
		@endif
		@endforeach
		</div>
		<hr />
		@if (count($data) > 0 )
		<table class="table">
			<tr>
				<th>#</th>
				<th>+/-</th>
				<th>關鍵詞</th>
				<th>詞頻</th>
				<th>出現率</th>
				<th>熱度</th>
			</tr>
			<?php $index = 0;?>
			@foreach ($data as $key => $val)
			<?php
				if ($val['rank'] > 300) continue;
				if ($index++ > 300) {
					break;
				}

				$keys_in_array = array_keys($val);
				$rankDiff = $val['rankDiff'];
				$class = ''; $image = '<span class="glyphicon glyphicon-minus"></span>';
				if (is_numeric($rankDiff)) {
					if ($rankDiff > 0) {
						$image = '<span class="glyphicon glyphicon-arrow-up"></span>';
					} else if ($rankDiff < 0) {
						$image = '<span class="glyphicon glyphicon-arrow-down"></span>';
					}

					if ($val['isHot'])
						$class = 'hot';

#					if ($rankDiff > 0) {
#						$class = 'warning';
#					}
#					if ($rankDiff < 0) {
#						$class = 'active';
#					}
#					if ($rankDiff > 30) {
#						$class = 'danger';
#					}
				} else {
					$class = 'hot';
					$image = '<span class="glyphicon glyphicon-arrow-up"></span>';
				}
			?>
			<tr class={{{ $class }}}>
				<td><a name="{{{$val['rank']}}}"></a>{{{ $val['rank'] }}}</td>
				<td>{{ $image }} {{{ $rankDiff }}}</td>
				<td><a href='https://www.google.com.tw/search?hl=zh-TW&gl=tw&tbm=nws&tbs=cdr:1,cd_min:{{{$date_search}}},cd_max:{{{$date_search}}}&q={{{ $val['term'] }}}' target="_blank"><span class="glyphicon glyphicon-share-alt"></span></a> {{{ $val['term'] }}}</td>
				<td>{{{ $val['score'] }}}</td>
				<td>{{{ round($val['rate'] * 100, 2)}}}%</td>
				<td>{{{ $val['heatScore'] }}}</td>
			</tr>
			@endforeach
			<tr>
				<th>#</th>
				<th>+/-</th>
				<th>關鍵詞</th>
				<th>詞頻</th>
				<th>出現率</th>
				<th>熱度</th>
			</tr>
		</table>
		@else
		<div class="alert alert-info">No data</div>
		@endif


		<hr />
	</div>
</div>
@stop

@include('pure-bootstrap3.modal')
