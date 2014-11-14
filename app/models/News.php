<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class News extends Eloquent
{
	public static $rules = array();

	public function newsinfo() {
		return $this->hasOne('NewsInfo');
	}
}
