<fieldset>
	<label>
		<?= esc_html( $field['name'] ) ?>
	</label>
	<input type="text" value="<?= esc_attr( $field['std'] ) ?>" name="<?= esc_attr( $field_name ) ?>" />
</fieldset>