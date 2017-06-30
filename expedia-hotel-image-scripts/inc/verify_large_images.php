<?php

$hotelImageURLs = new HawaiiHotelImageList();
$filename       = 'image_urls.json';

if ( ! file_exists( $filename ) ) {
	$query = $db->prepare( $hotelImageURLs->selectURLs() );
	$query->execute();

	$result = $query->fetchAll();
	file_put_contents( $filename, json_encode( $result ) );

	echo "File Created & Hydrated\n";

} else {

	do {
		$data = json_decode( file_get_contents( $filename ) );

		$chunk = array_splice( $data, 0, 10 );

		checkURLs( $chunk );

		file_put_contents( $filename, json_encode( $data ) );

		echo count( $data ) . " Images to be checked.\n";

	} while ( 0 != count( $data ) );

	unlink( $filename );

	echo "Process Complete\n";
	exit();
}

function checkURLs( $chunk ) {

	global $db;
	$good = [];
	$bad  = [];

	foreach ( $chunk as $hunk ) {
		if ( isValidImage( $hunk ) ) {
			array_push( $good, [ 'ID' => $hunk->ID, 'URL' => $hunk->URL ] );
		} else {
			array_push( $bad, [ 'ID' => $hunk->ID ] );
		}
	}

	$imageList = new HawaiiHotelImageList();

	if ( 0 != count( $good ) ) {
		$db->query( $imageList->updateURLs( $good ) );
		echo $imageList->updateURLs( $good );
	}

	if ( 0 != count( $bad ) ) {
		$db->query( $imageList->deleteImages( $bad ) );
		echo $imageList->deleteImages( $bad );
	}
}

function isValidImage( stdClass $hunk ) {

	$url     = str_ireplace( [ 'http://', 'b.jpg' ], [ 'https://', 'z.jpg' ], $hunk->URL );
	$headers = get_headers( $url );

	$isGood = false;

	if ( strpos( $headers[0], '200' ) ) {
		$isGood = true;
	}

	usleep( 100000 );

	return $isGood;
}
