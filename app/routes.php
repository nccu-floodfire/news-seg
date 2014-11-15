<?php
/*
|--------------------------------------------------------------------------
| NewsSeg
|--------------------------------------------------------------------------
*/
Route::post('/push_to_ckip', function() {
	$dataArr = array();
	$text = Input::get('text');

	$tmp = \Us\Helper\StringHelper::split_article($text);
	$text_array = \Us\Helper\StringHelper::generate_split_article_array($tmp);
	foreach ($text_array as $article) {
		// skip non-chinese pieces
		if (!preg_match("/\p{Han}+/u", $article)) {
			continue;
		}
		$dataArr['text'] = $article;
		Queue::push('Us\\Job\\CkipJob', $dataArr);
	}
});

Route::get('/credit-and-thanks', function() {
	return View::make('pure-bootstrap3.credit-and-thanks');
});


Route::get('/api/news/v1/hot/{date}', array( 'as' => 'api.news.hotlinks', 'uses' =>'NewsSegController@hotlinks'));
Route::get('/api/news/v1/clearcache/{date}', array( 'as' => 'api.news.clearcache', 'uses' =>'NewsSegController@clearcache'));

Route::get('/', array( 'as' => 'home', 'uses' =>'NewsSegController@index'));

Route::get('/news-terms/{date}', array( 'as' => 'home', 'uses' =>'NewsSegController@index'));
Route::get('/keyword-terms/{keyword}', array( 'as' => 'home', 'uses' =>'NewsSegController@keywordTerms'));
Route::get('/keyword-terms/{keyword}/{display}', array( 'as' => 'home', 'uses' =>'NewsSegController@keywordTerms'));
Route::get('/keyword-terms/{keyword}/{display}/{date}', array( 'as' => 'home', 'uses' =>'NewsSegController@keywordTerms'));
Route::get('/{date}', array( 'as' => 'home', 'uses' =>'NewsSegController@index'));
Route::get('/{date}/{all}', array( 'as' => 'home', 'uses' =>'NewsSegController@index'));
