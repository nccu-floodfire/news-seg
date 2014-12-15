@extends('pure-bootstrap3.layouts.default')
<style>
table a:link	{color:#999;}
table a:visited {color:#999;}
table a:hover   {color:#999;}
table a:active  {color:#999;}
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
			$sd_rate = number_format(sd($data_rate), 5);
		} else {
			$sd_rate = 'N/A';
		}

		//$data_heat = array_map(function ($ar) {return $ar['heatScore'];}, $data);
		?>
		<h4>Standard deviation of 出現率: <b>{{{ $sd_rate }}}</b></h4>
		<div style="line-height: 160%;">
		@foreach ($data as $key => $val)
		@if ($val['heatScore'] >= 4 && $val['rank'] <= 300)
		<span class="label label-danger">{{{$val['term']}}}</span>
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
			@foreach ($data as $key => $val)
			<?php
				if ($val['rank'] > 300) {
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
						$class = 'danger';

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
					$class = 'danger';
					$image = '<span class="glyphicon glyphicon-arrow-up"></span>';
				}
			?>
			<tr class={{{ $class }}}>
				<td>{{{ $val['rank'] }}}</td>
				<td>{{ $image }} {{{ $rankDiff }}}</td>
				<td><a href='https://www.google.com.tw/search?hl=zh-TW&gl=tw&tbm=nws&q={{{ $val['term'] }}}' target="_blank"><span class="glyphicon glyphicon-share-alt"></span></a> {{{ $val['term'] }}}</td>
				<td>{{{ $val['score'] }}}</td>
				<td>{{{ number_format($val['rate'] * 100, 2)}}}%</td>
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
