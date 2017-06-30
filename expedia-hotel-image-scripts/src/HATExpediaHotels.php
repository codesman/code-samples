<?php

final class HATExpediaHotels {

	private $table = "HAT_EAN_Hotels";
	private $key = 'EANHotelID';
	private $filename = 'hotel_ids.csv';

	private function path() {
		return "./{$this->filename}";
	}

	public function filename() {
		return $this->filename;
	}

	public function drop_table() {
		return "DROP TABLE IF EXISTS {$this->table}";
	}

	public function create_table() {
		return "CREATE TABLE {$this->table} (
  				{$this->key} int(11) NOT NULL,
  				PRIMARY KEY ({$this->key})
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				";
	}


	public function select_hotel_ids() {
		return "SELECT meta_value AS {$this->key}
				FROM aloha_postmeta
				WHERE meta_key = '_expedia_hotel_id';
				";
	}


	public function make_values( $data ) {

		$values = [];

		foreach ( $data as $key => $value ) {
			$values[] = "($value[0])";
		}

		return implode( ",", $values );
	}


	public function seed_database( $values ) {
		return "INSERT INTO {$this->table} ({$this->key}) VALUES $values";
	}

	/*
	 * Ended up not needing this, but will keep it around just in cases
	 */
	public function make_csv( $data ) {

		// Delete the file if it exists
		unlink( $this->path() );

		// Open the file for writing
		$handle = fopen( $this->path(), 'w' );

		// Write the header
		fwrite( $handle, "{$this->key}" . PHP_EOL );

		// Iterate through the data and write to file
		foreach ( $data as $key => $value ) {
			fwrite( $handle, $value[0] . PHP_EOL );
		}

		// Close the file for writing
		fclose( $handle );
	}
}
