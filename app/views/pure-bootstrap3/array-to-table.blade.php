@extends('pure-bootstrap3.layouts.default')
<style>
table a:link    {color:#999;}
table a:visited {color:#999;}
table a:hover   {color:#999;}
table a:active  {color:#999;}
</style>
@section('main')
<?php
	$prev = date('Y-m-d', strtotime("{$date} - 1 days"));
	$next = date('Y-m-d', strtotime("{$date} + 1 days"));
?>
<div class="row">
	<div class="col-md-12">
		<h3><a href="/{{{$prev}}}">prev <span class="glyphicon glyphicon-chevron-left"></span></a> {{{ $date }}} <a href="/{{{$next}}}"><span class="glyphicon glyphicon-chevron-right"></span> next</a></h3>
		@if (count($data) > 0 )
		<table class="table">
			<tr>
				<th>#</th>
				<th>Rank Change</th>
				<th>Term</th>
				<th>Score</th>
				<th>Heat Score</th>
			</tr>
			@foreach ($data as $key => $val)
			<?php
				if ($val['rank'] > 200) {
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
				<td>{{{ $val['heatScore'] }}}</td>
			</tr>
			@endforeach
			<tr>
				<th>#</th>
				<th>Rank Change</th>
				<th>Term</th>
				<th>Score</th>
				<th>Heat Score</th>
			</tr>
		</table>
		@else
		<div class="alert alert-info">No data</div>
		@endif


        <hr />
	</div>
</div>
@stop
