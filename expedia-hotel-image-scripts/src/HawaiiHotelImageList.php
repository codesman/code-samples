<?php

final class HawaiiHotelImageList extends EANPropertyAbstract {
	public $fileName = "HawaiiHotelImageList";

	public function dropTable() {
		return "DROP TABLE IF EXISTS {$this->fileName}";
	}

	public function createTable() {
		return "CREATE TABLE {$this->fileName} (
				`ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`EANHotelID` int(11) DEFAULT NULL,
				`Caption` varchar(255) DEFAULT NULL,
				`URL` varchar(255) DEFAULT NULL,
				`DefaultImage` int(11) DEFAULT NULL,
				PRIMARY KEY (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	}

	public function selectInto() {
		return "INSERT INTO {$this->fileName} (EANHotelID, Caption, URL, DefaultImage)
				SELECT EANHotelID, Caption, URL, DefaultImage
				FROM HotelImageList
				WHERE EANHotelID
				IN (
					SELECT EANHotelID
					FROM HAT_EAN_Hotels
				);";
	}

	public function selectURLs() {
		return "SELECT ID, URL FROM {$this->fileName}";
	}

	public function updateURL( $id, $url ) {
		return "UPDATE {$this->fileName} SET URL = $url WHERE ID = $id";
	}

	public function updateURLs( $urls ) {
		$query = "INSERT INTO {$this->fileName} (ID, URL) VALUES \n";

		foreach ( $urls as $url ) {
			$zURL = str_ireplace( [ 'http://', 'b.jpg' ], [ 'https://', 'z.jpg' ], $url['URL'] );

			$query .= "($url[ID], '$zURL'),\n";
		}

		$query = rtrim( rtrim( $query ), ',' );


		$query .= "\nON DUPLICATE KEY UPDATE ID=VALUES(ID),URL=VALUES(URL);\n";

		return $query;
	}

	public function deleteImages( $images ) {
		$bad = '';

		foreach ( $images as $image ) {
			$bad .= "$image[ID],";
		}

		$bad = rtrim( $bad, ',' );

		$query = "DELETE FROM {$this->fileName} WHERE ID IN ($bad);\n";

		return $query;
	}

	public function deleteImage( $id ) {
		return "DELETE FROM {$this->fileName} WHERE ID = $id";
	}
}
