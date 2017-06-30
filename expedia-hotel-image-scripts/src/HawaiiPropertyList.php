<?php

final class HawaiiPropertyList {
	public $fileName = "HawaiiPropertyList";

	public function dropTable() {
		return "DROP TABLE IF EXISTS {$this->fileName}";
	}

	public function createTable() {
		return "CREATE TABLE `{$this->fileName}` (
		`EANHotelID` int(11) DEFAULT NULL,
		`SequenceNumber` int(11) DEFAULT NULL,
		`Name` varchar(255) DEFAULT NULL,
		`Address1` varchar(255) DEFAULT NULL,
		`Address2` varchar(255) DEFAULT NULL,
		`City` varchar(255) DEFAULT NULL,
		`StateProvince` varchar(255) DEFAULT NULL,
		`PostalCode` varchar(255) DEFAULT NULL,
		`Country` varchar(255) DEFAULT NULL,
		`Latitude` varchar(255) DEFAULT NULL,
		`Longitude` varchar(255) DEFAULT NULL,
		`AirportCode` varchar(255) DEFAULT NULL,
		`PropertyCategory` int(11) DEFAULT NULL,
		`PropertyCurrency` varchar(255) DEFAULT NULL,
		`StarRating` varchar(255) DEFAULT NULL,
		`Confidence` varchar(255) DEFAULT NULL,
		`SupplierType` varchar(255) DEFAULT NULL,
		`Location` varchar(255) DEFAULT NULL,
		`ChainCodeID` varchar(255) DEFAULT NULL,
		`RegionID` int(11) DEFAULT NULL,
		`HighRate` varchar(255) DEFAULT NULL,
		`LowRate` varchar(255) DEFAULT NULL,
		`CheckInTime` varchar(255) DEFAULT NULL,
		`CheckOutTime` varchar(255) DEFAULT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	}

	public function selectInto() {
		return "INSERT INTO HawaiiPropertyList
				SELECT * FROM ActivePropertyList
				WHERE `StateProvince` = 'HI'";
	}
}
