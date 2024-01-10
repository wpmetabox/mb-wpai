<?php

namespace MetaBox\WPAI\Fields;

class KeyValueHandler extends FieldHandler {

	public function get_value() {
		$xpath = $this->get_xpaths();

		$output = [];

		foreach ( $xpath as $xpaths ) {
			$keys   = $this->get_value_by_xpath( $xpaths[0] );
			$values = $this->get_value_by_xpath( $xpaths[1] );

			for ( $i = 0; $i <= count( $keys ); $i++ ) {
				if ( ! array_key_exists( $i, $keys ) ) {
					continue;
				}

				$output[ $i ] = [ $keys[ $i ], $values[ $i ] ?? '' ];
			}
		}

		return $output;
	}
}
