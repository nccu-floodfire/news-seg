// Gulpfile.js
// Require the needed packages
var gulp = require('gulp');
var stylus = require('gulp-stylus');
var liveScript = require('gulp-livescript');
var watch = require('gulp-watch');
var gutil = require('gulp-util');
var nib = require('nib');

var $dir = {};
var $targets = [
	'home',
];

for (var i in $targets) {
	if ($targets.hasOwnProperty(i)) {
		var target = $targets[i];
		$dir[target] = {
			stylus: {},
			ls: {}
		}
	}
}

$dir.home.stylus.src =  'app/views/home/styl/*.styl';
$dir.home.stylus.dest =  'public/assets/css/home';




function watch_stylus(obj) {
	if (! obj.src) {
		return;
	}
	gulp.src(obj.src)
		.pipe(watch(function(files) {
			console.log('watch for ' +  files);
			return files.pipe(stylus({errors: true}))
				.pipe(gulp.dest(obj.dest))
		}))
}

function watch_ls(obj) {
	if (! obj.src) {
		return;
	}
	gulp.src(obj.src)
		.pipe(watch(function(files) {
			console.log('watch for ' + files);
			return files.pipe(liveScript({bare: true}))
				.pipe(gulp.dest(obj.dest));
		}));
}

function build_stylus(obj) {
	if (! obj.src) {devDependencies
		return;
	}
	console.log('build ' + obj.src);
	var options = {
		resolveUrl: true, // XXX not work
		'resolve url': true, // XXX not work
		'resolveUrl': true, // XXX not work
		use: nib()
	};
	gulp.src(obj.src)
		.pipe(stylus(options))
		.pipe(gulp.dest(obj.dest));
}

function build_ls(obj) {
	if (! obj.src) {
		return;
	}
	gulp.src(obj.src)
		.pipe(liveScript({bare:true})
			.on('error', gutil.log))
		.pipe(gulp.dest(obj.dest));
}

// Get and rnender all .styl files recursively
gulp.task('stylus-watch', function () {
	for (var prop in $dir) {
		if ($dir.hasOwnProperty(prop) && $dir[prop].hasOwnProperty('stylus')) {
			watch_stylus($dir[prop].stylus);
		}
	}
});

gulp.task('ls-watch', function () {
	for (var prop in $dir) {
		if ($dir.hasOwnProperty(prop) && $dir[prop].hasOwnProperty('ls')) {
			watch_ls($dir[prop].ls);
		}
	}
});

gulp.task('stylus-build', function () {
	for (var prop in $dir) {
		console.log ('prepare for ' + $dir[prop].stylus.src);
		if ($dir.hasOwnProperty(prop) && $dir[prop].hasOwnProperty('stylus')) {
			build_stylus($dir[prop].stylus);
		}
	}
});

gulp.task('ls-build', function () {
	for (var prop in $dir) {
		if ($dir.hasOwnProperty(prop) && $dir[prop].hasOwnProperty('ls')) {
			build_ls($dir[prop].ls);
		}
	}
});

// Default gulp task to run
gulp.task('default',
	[
		'stylus-build',
		'ls-build'
	]
);

gulp.task('watch',
	[
		'stylus-watch',
		'ls-watch'
	]
);
