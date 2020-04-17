<?php
namespace MBWPAI;

class Transformer {
	/**
	 * Transform cloneable group data.
	 *
	 * Input format.
	 * [
	 *     // Group clone 1.
	 *     [
	 *          field1 => [value_post1, value_post2],
	 *          field2 => [value_post1, value_post2],
	 *     ],
	 *     // Group clone 2.
	 *     [
	 *          field1 => [value_post1, value_post2],
	 *          field2 => [value_post1, value_post2],
	 *     ],
	 * ]
	 *
	 * Output format.
	 * [
	 *     // Post 1.
	 *     [
	 *          // Group clones.
	 *          [field1_value, field2_value],
	 *          [field1_value, field2_value],
	 *     ],
	 *     // Post 2.
	 *     [
	 *          // Group clones.
	 *          [field1_value, field2_value],
	 *          [field1_value, field2_value],
	 *     ],
	 * ]
	 *
	 * @param array $input Input group data.
	 * @return array
	 */
	public static function transform_cloneable_group( $input, $child_num ) {
		/**
		 *  Tách array 8x2 => 1x16
		 */
		foreach ( $input as $temp_k => $field ) {
			foreach ( $field as $field_k => $val ) {
				$temp_arr = [];
				foreach ( $val as $val_child ) {
					$temp_arr[ $field_k ] = $val_child;
					$temp_2[] = $temp_arr;
				}
			}
		}

		/**
		 *  2 data: price + title => divide into 2 array
		 */
		$output = [];
		for ( $i = 0; $i < $child_num; $i++ ) {
			$child = [];

			// đổi chỗ 0-1-2-3-4-5-6 => 0-2-4-6-1-3-5
			for ( $x = $i; $x < count( $temp_2 ); $x += $child_num ) {
				$child[] = $temp_2[$x];
			}

			// sắp xếp các phần tử 0-2-4-6 vào 1 mảng
			// 1-3-5-7 vào 1 mảng
			for ( $x = 0; $x <= count( $child ) + 1; $x += $child_num ) {
				for ( $y = 1; $y < $child_num; $y++ ) {
					$child[$x] = array_merge( $child[$x], $child[$x + $y] );
					unset( $child[$x + $y] );
				}
			}

			foreach ( $child as $k => $v ) {
				$child[$k] = array_filter( $child[$k] );

				if ( ! $child[$k] ) unset( $child[$k] );
			}

			$output[] = $child;
		}
		// l( $output );
		return $output;
	}

	public static function transform_checkbox_list( $field_id, $input, $child_num ) {
		$output = [];

		foreach ( $input as $k => $v ) {
			$temp = [];

			$temp[$field_id] = $input[$k];

			$output[] = $temp[$field_id];
		}

		l( $field_id );
		
		// l( $output );
		return $output;
	}
}