<?php
/**
 * @var $handler
 * @var $field
 */
// For every field type, we just need to create a simple text field with no attributes
$wpai_attr    = $field['_wpai'];
$xpaths       = $wpai_attr['xpath'] ?? [ [] ];
$field['_id'] = $field['_id'] ?? 'fields[' . $field['id'] . '][xpath]';
$clone        = $field['clone'] ?? false;
?>
<div class="rwmb-field rwmb-group-wrapper">
	<div class="rwmb-label">
		<label>
			<?= esc_html( $field['name'] ) ?>
		</label>
	</div>
	<div class="rwmb-input">
		<?php foreach ( $xpaths as $index => $xpath ) : ?>
			<div class="rwmb-clone <?= $clone ? 'rwmb-group-clone' : '' ?>">
				<div class="rwmb-field">
					<div class="rwmb-label">
						<label>For each</label>
					</div>
					<div class="rwmb-input">
						<input type="text" name="<?= esc_attr( $field['_id'] ) ?>[<?= intval( $index ) ?>][foreach]"
							value="<?= esc_attr( $wpai_attr['xpath'][ $index ]['foreach'] ?? '' ) ?>" />
					</div>
				</div>

				<?php
				foreach ( $field['fields'] as $child ) {
					$child['_id']   = $field['_id'] . '[' . $index . '][' . $child['id'] . ']';
					$child['std']   = $wpai_attr['xpath'][ $index ][ $child['id'] ] ?? '';
					$child['_wpai'] = [
						'xpath' => $wpai_attr['xpath'][ $index ][ $child['id'] ] ?? '',
					];

					$handler->render_field( $child );
				}
				?>
			</div>
		<?php endforeach; ?>

		<?php if ( $clone ) : ?>
			<a href="#" class="rwmb-button button-primary add-clone">+ Add more</a>
		<?php endif; ?>
	</div>
	<input type="hidden" name="fields[<?= esc_attr( $field['id'] ) ?>][reference]"
		value="<?= esc_attr( $wpai_attr['reference'] ?? '' ) ?>" />
</div>