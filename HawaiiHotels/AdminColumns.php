<?php
namespace HawaiiAloha\PostType\HawaiiHotels;

final class AdminColumns {

	/**
	 * AdminMenu constructor.
	 */
	public function __construct() {
		
		add_action( 'admin_init', [ $this, 'adminRemoveColumns' ], 999 );
	}

	public function adminRemoveColumns() {
		
		add_filter( 'manage_hawaii-hotels_posts_columns', [ $this, 'removeWPSEO' ], 999, 1 );
	}
	
	public function removeWPSEO( array $columns ) {

		unset( $columns['wpseo-title'] );
		unset( $columns['wpseo-metadesc'] );
		unset( $columns['wpseo-focuskw'] );
		unset( $columns['wpseo-score-readability'] );

		return $columns;
	}
}
