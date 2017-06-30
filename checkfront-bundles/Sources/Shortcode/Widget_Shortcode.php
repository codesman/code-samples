<?php
namespace Checkfront_Bundles;

final class Widget_Shortcode {
	/**
	 * Widget_Shortcode constructor.
	 */
	public function __construct() {
		add_shortcode( 'bundle_widget', [ $this, 'shortcode' ] );
	}

	/**
	 * The shortcode output
	 * @return void
	 */
	public function shortcode() {

		echo "<section class='alert-bundle'>
				<header><h1>Going to more than one island?<br>Check out our NEW Super Saver Combos!</h1></header>
				<ul class='bundle-wrapper'>
					<li><a href='/oahu-kauai-super-saver-combo/'></a>
					</li>
					<li><a href='/oahu-pearl-harbor-super-saver-combo/'></a>
					</li>
				</ul>
		</section>";
	}
}

new Widget_Shortcode();
