<?php

namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\MetaBoxService;

class TextListHandler extends FieldHandler {
	public function import( $importData, $args = [] ) {
		$this->importData = $importData;

		$value = $this->get_value();

		if ( ! $this->field['clone'] ) {
			MetaBoxService::set_meta( $this, $this->get_post_id(), $this->field['id'], $value );
			return true;
		} else {
			$output = $value;
			MetaBoxService::set_meta( $this, $this->get_post_id(), $this->field['id'], $output );
		}
	}

	public function get_value() {
		$xpaths = $this->get_xpaths();

		$values = [];

		foreach ( $xpaths as $clone_index => $xpath ) {
			if ( is_string( $xpath ) ) {
				$values[ $clone_index ] = $this->get_value_by_xpath( $xpath );
				continue;
			}

			if ( is_array( $xpath ) ) {
				foreach ( $xpath as $index => $value ) {
					$values[ $clone_index ][ $index ] = $this->get_value_by_xpath( $value );
				}
			}
		}

		$output = [];
        foreach ($values as $index => $row) {
            $output = array_merge($output, $row);
        }

		$output = $this->combine_values( $output );
		
		return $this->field['clone'] ? $output : $output[0] ?? null;
	}

	private function combine_values( array $array ) {
		$result = [];
		$keys   = array_keys( $array );

		foreach ( $array as $key => $values ) {
			foreach ( $values as $index => $value ) {
				foreach ( $keys as $k ) {
					$result[ $index ][ $k ] = $array[ $k ][ $index ] ?? null;
				}
			}
		}

		return $result;
	}
}
