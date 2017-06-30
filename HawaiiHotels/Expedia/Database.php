<?php

namespace HawaiiAloha\PostType\HawaiiHotels\Expedia;

class Database {
	
	public static function connection(): \wpdb {
		
		return new \wpdb(
			env( "EXPEDIA_DB_USER" ),
			env( "EXPEDIA_DB_PASSWORD" ),
			env( "EXPEDIA_DB_NAME" ),
			env( "EXPEDIA_DB_HOST" )
		);
	}
}
