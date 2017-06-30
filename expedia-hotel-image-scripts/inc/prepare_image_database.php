<?php

$hotelImages = new HotelImageList();
$file        = $hotelImages->fileName;

// Remove the file if it exists
exec( "cd $mysql_path && rm -f $file.zip && rm -f $file.txt" );

// Get a fresh file from EAN & unzip it
exec( "wget -P $mysql_path {$hotelImages->eanAPIURL}$file.zip" );
exec( "cd $mysql_path && unzip -o $file.zip" );
exec( "cd $mysql_path && chown mysql:mysql $file.txt" );

// For testing only
// exec("cp $file.txt $mysql_path && cd $mysql_path && chown mysql:mysql $file.txt");

// Prepare the DB
$db->query( $hotelImages->dropTable() );
$db->query( $hotelImages->createTable() );

// Import the txt file
exec( $hotelImages->importTxt(), $return );

// Clean up
exec( "cd $mysql_path && rm -f $file.zip && rm -f $file.txt" );

$hawaiiHotelImages = new HawaiiHotelImageList();

// Create Hawaii Hotel Images Table
$db->query( $hawaiiHotelImages->dropTable() );
$db->query( $hawaiiHotelImages->createTable() );

// Fill the Hawaii Table
$db->query( $hawaiiHotelImages->selectInto() );

// // Clean up the DB
$db->query( $hotelImages->dropTable() );
