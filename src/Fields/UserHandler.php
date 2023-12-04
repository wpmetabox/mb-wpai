<?php
namespace MetaBox\WPAI\Fields;

class UserHandler extends FieldHandler {

	public function get_value() {
		$by    = [ 'login', 'slug', 'email', 'id' ];
		$value = parent::get_value();

		if ( ! is_array( $value ) ) {
			$value = [ $value ];
		}

		$ids = [];

		foreach ( $value as $v ) {
			foreach ( $by as $column ) {
				$user = get_user_by( $column, $v );

				if ( ! empty( $user ) ) {
					$ids[] = $user->ID;
				}
			}
		}

		return $ids;
	}
}