<?php

namespace MetaBox\WPAI\Fields;

class FileInputHandler extends FileHandler {
	public function get_value() {
		$attachment = parent::get_value();

		if ( ! is_array( $attachment ) ) {
			return;
		}

		$output = [];

		foreach ( $attachment as $clone_index => $ids ) {
			foreach ( $ids as $i => $id ) {
				if ( ! $id ) {
					continue;
				}

				$output[ $i ] = get_post( $id )->guid;
			}
		}

		return $output;
	}
}
