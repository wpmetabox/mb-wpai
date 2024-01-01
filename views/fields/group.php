<?php
/**
 * @var $handler
 * @var $field
 */
// For every field type, we just need to create a simple text field with no attributes
$wpai_attr    = $field['_wpai'];
$field['_id'] = $field['_id'] ?? 'fields[' . $field['id'] . '][xpath]';
?>
<div class="rwmb-field rwmb-group-wrapper  rwmb-group-non-cloneable">
	<div class="rwmb-label">
		<label>
			<?= esc_html( $field['name'] ) ?>
		</label>
	</div>
	<div class="rwmb-input">
		<div class="rwmb-field">
			<div class="rwmb-label">
				<label>For each</label>
			</div>
			<div class="rwmb-input">
				<input type="text" name="<?= esc_attr( $field['_id'] ) ?>[foreach]"
					value="<?= esc_attr( $wpai_attr['xpath']['foreach'] ) ?>" />
			</div>
		</div>

		<?php
		foreach ( $field['fields'] as $child ) {
			$child['_id']   = $field['_id'] . '[' . $child['id'] . ']';
			$child['std']   = $wpai_attr['xpath'][ $child['id'] ] ?? '';
			$child['_wpai'] = [ 
				'xpath' => $wpai_attr['xpath'][ $child['id'] ] ?? '',
			];

			$handler->render_field( $child );
		}
		?>
	</div>
	<input type="hidden" name="fields[<?= esc_attr( $field['id'] ) ?>][reference]"
		value="<?= esc_attr( $wpai_attr['reference'] ?? '' ) ?>" />
</div>