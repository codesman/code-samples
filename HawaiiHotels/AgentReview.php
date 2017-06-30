<?php
namespace HawaiiAloha\PostType\HawaiiHotels;

class AgentReview {

	/**
	 * AgentReview constructor.
	 */
	public function __construct() {

		add_action( 'cmb2_admin_init', [ $this, 'metabox' ], 10 );
	}

	/**
	 * Bootstraps our Page Format metabox
	 * Hotel Pages can be either 'none' or 'island' format
	 *
	 * @return void
	 */
	public function metabox() {

		$metabox = new_cmb2_box( [
			'id'           => 'agent-review',
			'title'        => __( 'Agent Review', 'cmb2' ),
			'object_types' => [ 'hawaii-hotels' ], // Post type
			'context'      => 'normal',
			'priority'     => 'low',
			'show_names'   => true, // Show field names on the left
			'closed'       => true,
			'show_on_cb'    => [ 'HawaiiAloha\PostType\HawaiiHotels\HawaiiHotels', 'excludeFromPages' ],
		] );

		$metabox->add_field( array(
			'name'             => 'Review By:',
			'id'               => '_hotel_agent_review_author',
			'type'             => 'select',
			'show_option_none' => true,
			'default'          => 'none',
			'options_cb'          => [ $this, 'getReviewAuthors' ],
		) );
		
		$metabox->add_field( array(
			'name'          => 'Review Text:',
			'id'            => '_hotel_agent_review_text',
			'type'          => 'textarea_small',
		) );
	}

	public function getReviewAuthors() {
		
		$options = [];
		
		$args = array(
			'posts_per_page'   => -1,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'        => 'agent',
			'post_status'      => 'publish, private, draft',
			'suppress_filters' => true
		);
		$agents = get_posts( $args );

		foreach ($agents as $agent){
			
			$options["$agent->post_author"] = __( $agent->post_title, 'cmb2');
		}
		
		return $options;
	}
}
