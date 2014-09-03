<!doctype html>
<html lang="zh-TW">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>NewsSeg 新聞每日詞頻統計</title>
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="shortcut icon" href="assets/ico/favicon.ico">

	{{ HTML::style('assets/css/bs/bootstrap.min.css') }}
	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
	<nav class="navbar navbar-default navbar-static-top" role="navigation">
		<div class="container">
			<div class="row">
				<div class="navbar-header">
					<a href="/" class="navbar-brand">NewsSeg</a>
				</div>
				<ul class="nav navbar-nav">
					<li><a href="/credit-and-thanks">About</a></li>
					<li><a href="/keyword-terms/連勝文">連勝文</a></li>
					<li><a href="/keyword-terms/柯文哲">柯文哲</a></li>
					<li><a href="/keyword-terms/馮光遠">馮光遠</a></li>
				</ul>
			</div>
		</div>
	</nav>
	<div class="container">
		@yield('main')
	</div>
</body>
</html>
