<?php
namespace MetaBox\WPAI\Fields;

class UserHandler extends FieldHandler {

	/**
	 * Loop through the value and get user ID from login, slug, email or ID.
	 *
	 * @return int[]|null
	 */
	public function get_value() {
		$by    = [ 'login', 'slug', 'email', 'id' ];
		$value = parent::get_value();

		if ( ! is_array( $value ) ) {
			return;
		}

		$output = [];

		foreach ( $value as $clone_index => $users ) {
			if ( ! is_array( $users ) ) {
				$users       = $value;
				$clone_index = 0;
			}

			foreach ( $users as $user ) {
				if ( ! $user ) {
					continue;
				}

				foreach ( $by as $column ) {
					$userObject = get_user_by( $column, $user );

					if ( $userObject ) {
						$output[ $clone_index ][] = $userObject->ID;
						break;
					}
				}
			}
		}

		return $this->field['clone'] ? $output : reset( $output );
	}
}
