<?php
namespace Checkfront_Bundles;

final class Tour {

	const TOURS = [
		4  => 'Aloha Plate Food Tour',
		18 => 'Custom Tour',
		34 => 'Escape Waikiki EZ Waterfall Tour',
		32 => 'Kauai Jeep Tour',
		40 => 'Kona Culture & Coffee Tour',
		29 => 'Oahu is for Lovers - Couples Jeep Tour',
		21 => 'Pearl Harbor Experience - 4 in 1 Bundle',
		28 => 'Pearl Harbor Experience - Mighty Mo',
		20 => 'Pearl Harbor Experience - Pacific Aviation Museum Bundle',
		39 => 'The Lava Experience',
		43 => 'Big Island Off Roading Tour',
		12 => 'Hawaii 50 Super Fan Tour',
		5  => 'Hawaii 50 Tour',
		1  => 'Oahu Circle Island Tour',
		6  => 'Oahu Half Circle Island Tour',
		25 => 'LLOL Tour',
		24 => 'Ultimate SOTB 2015 SuperFan Tour',
	];
	
	public static function title( $id ) {
		
		return self::TOURS[$id];
	}
}
