<?php

$properties = new ActivePropertyList();
$file       = $properties->fileName;

// Remove the file if it exists
exec( "cd $mysql_path && rm -f $file.zip && rm -f $file.txt" );

// Get a fresh file from EAN & unzip it
exec( "wget -P $mysql_path {$properties->eanAPIURL}$file.zip" );
exec( "cd $mysql_path && unzip -o $file.zip" );
exec( "cd $mysql_path && chown mysql:mysql $file.txt" );

// Prepare the DB
$db->query( $properties->dropTable() );
$db->query( $properties->createTable() );

// Import the txt file
exec( $properties->importTxt(), $return );
var_dump( $return );

// Clean up
exec( "cd $mysql_path && rm -f $file.zip && rm -f $file.txt" );

$hawaiiProperties = new HawaiiPropertyList();

// Create Hawaii Properties Table
$db->query( $hawaiiProperties->dropTable() );
$db->query( $hawaiiProperties->createTable() );

// Fill the Hawaii Table
$db->query( $hawaiiProperties->selectInto() );

// Clean up the DB
$db->query( $properties->dropTable() );
