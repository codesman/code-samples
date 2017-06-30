<?php

namespace Checkfront_Bundles;

final class Pricing {

	/**
	 * Non-discounted bundle pricing
	 * Used to calculate discount.
	 *
	 * @param $name
	 *
	 * @return array The non-discounted bundle pricing.
	 */
	public static function prices() {

		// The CheckFront API makes getting this info kinda tricky.
		// Prices rarely change, so we decided to hardcode this instead.

		return [
			2845 => [ // Oahu & Kauai 
				'adult' => 298,
				'child' => 198,
			],
			2856 => [ // Circle Island & Pearl Harbor
				'adult' => 338,
				'child' => 198,
			],
			2851 => [ // Triple Island
				'adult' => 437,
				'child' => 317,
			],
		];
	}

	/**
	 * HTML Output to indicate savings over non-bundled pricing.
	 *
	 * @return string HTML
	 */
	public static function html() {

		return "<div class='bundle-pricing'><strong>Total: </strong><span id='bundle_regular'></span> <strong> <span id='bundle_discount'></span><br><strong>You save <span id='bundle_savings'></span></strong> <sup>*After sales tax is calculated</sup></div>";
	}
}
