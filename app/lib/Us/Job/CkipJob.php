<?php
namespace Us\Job;
use Us\Helper\CKIPClient;
use Config;

class CkipJob {
	public function fire($job, $dataArr) {
		$ckip_client_obj = new CKIPClient(
			Config::get('ckip.CKIP_SERVER'),
			Config::get('ckip.CKIP_PORT'),
			Config::get('ckip.CKIP_USERNAME'),
			Config::get('ckip.CKIP_PASSWORD')
		);

		$ckip_client_obj->send($dataArr['text']);

		$return_term = $ckip_client_obj->getTerm();
		$event = \Event::fire('ckip.callback', array($return_term));
		$job->delete();
		sleep(2);
	}
}
