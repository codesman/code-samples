<?php

final class HotelImageList extends EANPropertyAbstract {
	public $fileName = "HotelImageList";

	public function dropTable() {
		return "DROP TABLE IF EXISTS {$this->fileName}";
	}

	public function createTable() {
		return "CREATE TABLE `{$this->fileName}` (
				`EANHotelID` int(11) DEFAULT NULL,
				`Caption` varchar(255) DEFAULT NULL,
				`URL` varchar(255) DEFAULT NULL,
				`Width` int(11) DEFAULT NULL,
				`Height` int(11) DEFAULT NULL,
				`ByteSize` varchar(255) DEFAULT NULL,
				`ThumbnailURL` varchar(255) DEFAULT NULL,
				`DefaultImage` int(11) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	}

	public function importTxt() {
		return "mysqlimport -u root --delete --fields-terminated-by='|' --ignore-lines=1 '{$this->dbName()}' '/var/lib/mysql/{$this->fileName}.txt'";
	}
}
