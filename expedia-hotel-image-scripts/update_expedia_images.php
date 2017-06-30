<?php

require_once 'src/db.php';
require_once 'src/wpdb.php';
require_once 'src/EANPropertyAbstract.php';
require_once 'src/HATExpediaHotels.php';
require_once "src/HotelImageList.php";
require_once "src/HawaiiHotelImageList.php";
require_once "src/ActivePropertyList.php";
require_once "src/HawaiiPropertyList.php";

$mysql_path = "/var/lib/mysql";

// Convenience function so I don't have to add line returns to every echo
function println($text) {
	echo PHP_EOL . "$text" . PHP_EOL;
}

println( "Updating Images" );
println( "Building Hotel List from WordPress Database" );

// Instantiate the class
$expediaHotels = new HATExpediaHotels();

// Prepare the DB
$db->query($expediaHotels->drop_table());
$db->query($expediaHotels->create_table());

// Build a list of Hotel IDs from WP Database
$hotels = $wpdb->query($expediaHotels->select_hotel_ids());
$ids = $hotels->fetchAll();

// Seed the values into this database so we can use them to extract image urls
$values = $expediaHotels->make_values($ids);
$db->query($expediaHotels->seed_database($values));

println( "Seeded Database with Hotel IDs" );


println( "Preparing Image Database" );

require_once 'inc/prepare_image_database.php';

println( "Image Database Prepared" );


println( "Preparing Property Database" );

require_once 'inc/prepare_property_database.php';

println( "Property Database Prepared" );


println( "Verifying Large Images - This may take an hour or more!!" );

require_once 'inc/verify_large_images.php';

println( "Images Verified" );


println( "Image Update Complete" );