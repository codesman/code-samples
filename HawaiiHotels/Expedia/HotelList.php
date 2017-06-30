<?php

namespace HawaiiAloha\PostType\HawaiiHotels\Expedia;

use Timber\Timber;

class HotelList {
	
	private $post_type = 'hawaii-hotels';

	/**
	 * HotelList constructor.
	 */
	public function __construct() {

		$this->db = Database::connection();
		
		add_action('admin_menu', [$this, 'submenu']);
	}

	public function submenu() {
		
		add_submenu_page(
			'edit.php?post_type=hawaii-hotels',
			'Expedia Hawaii Hotels Info',
			'Expedia',
			'manage_options',
			'expedia-hotels',
			 [$this, 'html']
		);
	}
	
	public function html() {
		$context = [
			'title' => get_admin_page_title(),
			'header_1' => 'ID',
			'header_2' => 'Name',
			'header_3' => 'City',
			'list' => $this->get_hotel_list()
		];

		Timber::render( 'expedia-submenu-page.twig', $context );
	}
	
	private function get_hotel_list() {
		
		$query = "SELECT * FROM HawaiiPropertyList";
		return $this->db->get_results( $query );
	}
}
