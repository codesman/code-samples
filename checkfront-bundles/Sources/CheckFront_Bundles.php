<?php
namespace Checkfront_Bundles;

final class CheckFront_Bundles {
	
	protected $api_url = "https://jeeptourshawaii.checkfront.com/api/3.0/";
	protected $api_key;
	protected $api_secret;
	protected $auth_header;

	/**
	 * CheckFront_Bundles constructor.
	 */
	public function __construct() {
		
		$option = get_option('checkfront_bundles_auth');
		
		$this->api_key = '' !== $option["key"] ? $option["key"] : "";
		$this->api_secret = '' !== $option["secret"] ? $option["secret"] : "";
		
		add_action('init', [$this, 'register_scripts']);
	}

	/**
	 * Let's register our dependencies!
	 * 
	 * @return void
	 */
	public function register_scripts(  ) {

		$public = plugin_dir_url('') . 'checkfront-bundles/Resources/Public/';

		wp_register_script(
			'checkfront-bundles',
			"{$public}js/checkfront-bundles.js",
			['jquery-ui-datepicker'],
			null,
			true
		);

		wp_register_style(
			'cangas-datepicker-css',
			"{$public}css/cangas.datepicker.css",
			null,
			null,
			false
		);

		wp_register_style(
			'jquery-ui-css',
			"{$public}css/jquery-ui.min.css",
			null,
			null,
			false
		);

		// Localize the script with new data
		wp_localize_script( 'checkfront-bundles', 'wordpress', ['ajaxurl' => admin_url( 'admin-ajax.php' )] );

		
	}

	/**
	 * @return string
	 */
	public function api_key(): string {
		return $this->api_key;
	}

	/**
	 * @return string
	 */
	public function api_secret(): string {
		return $this->api_secret;
	}

	/**
	 * @return string
	 */
	public function api_url() {
		return $this->api_url;
	}

	/**
	 * The authorization header to be used when calling the CheckFront API.
	 * 
	 * @return array
	 */
	public function auth_header() {
		
		return ['Authorization' => 'Basic ' . base64_encode( $this->api_key . ':' . $this->api_secret )];
	}
}

new CheckFront_Bundles();
