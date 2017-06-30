<?php

abstract class EANPropertyAbstract {
	public $eanAPIURL = "https://www.ian.com/affiliatecenter/include/V2/";

	public function dbName() {
		return "ean_content_prep";
	}
}
