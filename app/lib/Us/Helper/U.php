<?php namespace Us\Helper;
class U {
	public static function d_enabled() {
		static $enabled = null;
		if ($enabled === null) {
			$enabled = Config::get('laravel-debugbar::config.enabled');
		}
		return $enabled;
	}

	public static function info($object) {
		\Debugbar::info($object);
	}

	public static function error($object) {
		\Debugbar::error($object);
	}

	public static function warning($object) {
		\Debugbar::warning($object);
	}
}