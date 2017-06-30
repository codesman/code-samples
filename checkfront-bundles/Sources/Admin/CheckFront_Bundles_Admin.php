<?php

final class CheckFront_Bundles_Admin {

	private $slug = "checkfront_bundles";
	private $options;

	/**
	 * CheckFront_Bundles_Admin constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'create_menu_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_init', [ $this, 'add_auth_section' ] );
	}


	/**
	 * Adds a Menu Page in the admin to store CheckFront Auth Credentials
	 */
	public function create_menu_page() {
		add_options_page(
			'CheckFront Bundles',
			'CheckFront Bundles',
			'manage_options',
			$this->slug,
			[ $this, 'menu_page_html' ]
		);
	}


	/**
	 * The HTML Output for the menu page
	 */
	public function menu_page_html() {

		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// add error/update messages
		// check if the user have submitted the settings
		// wordpress will add the "settings-updated" $_GET parameter to the url
		if ( isset( $_GET['settings-updated'] ) ) {
			// add settings saved message with the class of "updated"
			add_settings_error( "{$this->slug}_messages", "{$this->slug}_message", __( 'Settings Saved', $this->slug ),
				'updated' );
		}

		// show error/update messages
		settings_errors( $this->slug );
		// output save settings button);
		?>
        <div class="wrap">
        <h1><?= esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
			<?php
			// output security fields for the registered setting
			settings_fields( $this->slug );
			// output setting sections and their fields
			do_settings_sections( $this->slug );
			// output save settings button
			submit_button( 'Save Settings' );
			?>
        </form>
        </div><?php
	}

	/**
	 * Register our custom settings
	 */
	public function register_settings() {
		register_setting( "{$this->slug}", "{$this->slug}_auth" );
	}

	/**
	 * Adds a section & fields to our admin page
	 */
	public function add_auth_section() {
		add_settings_section(
			"{$this->slug}_auth_section",
			__( 'API Auth Credentials', $this->slug ),
			'auth_section_callback',
			$this->slug
		);

		add_settings_field(
			'key',
			'CheckFront Client ID',
			[ $this, 'auth_key_html' ],
			$this->slug,
			"{$this->slug}_auth_section"
		);

		add_settings_field(
			'secret',
			'CheckFront Client Secret',
			[ $this, 'auth_secret_html' ],
			$this->slug,
			"{$this->slug}_auth_section"
		);
	}

	/**
     * Outputs the auth_key Field HTML
     * 
	 * @param $args
	 */
	public function auth_key_html( $args ) {

		$option = get_option( "{$this->slug}_auth" );
		$value  = '' !== $option["key"] ? $option["key"] : "";

		echo "<input type='text' name='{$this->slug}_auth[key]' value='$value' class='regular-text'>";
	}

	/**
     * Outputs the auth_secret Field HTML
     * 
	 * @param $args
	 */
	public function auth_secret_html( $args ) {

		$option = get_option( "{$this->slug}_auth" );
		$value  = '' !== $option["secret"] ? $option["secret"] : "";

		echo "<input type='text' name='{$this->slug}_auth[secret]' value='$value' class='regular-text'>";
	}
}

new CheckFront_Bundles_Admin();
