<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class NewsInfo extends Eloquent
{
	protected $table = 'news_info';
	protected $primaryKey = 'news_id';
	public static $rules = array();

	public function news() {
		return $this->belongsTo('News');
	}
}
