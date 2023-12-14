<?php
// -- Header
?>
<?php
/**
 * @var array $field
 * @var string $field_name
 * @var string $field_value
 * @var string $current_is_multiple_field_value
 * @var string $current_multiple_value
 */
$field_name = str_replace(array('[',']'), '', $field_name);
$current_field = $field_value;

if ( ! is_array( $current_field ) ) {
    $current_field = [
        'is_variable' => '',
        'foreach'     => '',
    ];
}

?>
<div class="repeater">
	<div class="input" style="margin-bottom: 10px;">
		<div class="input">
			<input type="radio"
				id="is_variable_<?= $field_name ?>_<?= $field['id'] ?>_no"
				class="switcher variable_repeater_mode"
				name="<?= $field_name ?>[is_variable]" value="no" <?php echo 'yes' != $current_field['is_variable'] ? 'checked="checked"' : '' ?> />
			<label
				for="is_variable_<?= $field_name ?>_<?= $field['id'] ?>_no"
				class="chooser_label">
				<?php _e( 'Fixed Repeater Mode', 'mb-wpai' ) ?>
			</label>
		</div>
		<div class="input">
			<input type="radio"
				id="is_variable_<?= $field_name ?>_<?= $field['id'] ?>_yes"
				class="switcher variable_repeater_mode"
				name="<?= $field_name ?>[is_variable]" value="yes" <?php echo 'yes' == $current_field['is_variable'] ? 'checked="checked"' : '' ?> />
			<label
				for="is_variable_<?= $field_name ?>_<?= $field['id'] ?>_yes"
				class="chooser_label">
				<?php _e( 'Variable Repeater Mode (XML)', 'mb-wpai' ) ?>
			</label>
		</div>
		<div class="input">
			<input type="radio"
				id="is_variable_<?= $field_name ?>_<?= $field['id'] ?>_yes_csv"
				class="switcher variable_repeater_mode"
				name="<?= $field_name ?>[is_variable]" value="csv" <?php echo 'csv' == $current_field['is_variable'] ? 'checked="checked"' : '' ?> />
			<label
				for="is_variable_<?= $field_name ?>_<?= $field['id'] ?>_yes_csv"
				class="chooser_label">
				<?php _e( 'Variable Repeater Mode (CSV)', 'mb-wpai' ) ?>
			</label>
		</div>
		<div class="input sub_input">
			<input type="hidden"
				name="<?= $field['_name'] ?>[is_ignore_empties]" value="0" />
			<input type="checkbox" value="1"
				id="is_ignore_empties<?= $field_name ?>_<?= $field['id'] ?>"
				name="<?= $field['_name'] ?>[is_ignore_empties]" <?php if ( ! empty( $current_field['is_ignore_empties'] ) )
						  echo 'checked="checked'; ?> />
			<label
				for="is_ignore_empties<?= $field_name ?>_<?= $field['id'] ?>">
				<?php _e( 'Ignore blank fields', 'mb-wpai' ); ?>
			</label>
			<a href="#help" class="wpallimport-help" style="top:0;"
				title="<?php _e( 'If the value of the element or column in your file is blank, it will be ignored. Use this option when some records in your file have a different number of repeating elements than others.', 'mb-wpai' ) ?>">?</a>
		</div>
		<div class="wpallimport-clear"></div>
		<div
			class="switcher-target-is_variable_<?= $field_name ?>_<?= $field['id'] ?>_yes">
			<div class="input sub_input">
				<div class="input">
					<p>
						<?php printf( __( "For each %s do ..." ), '<input type="text" name="' . $field['_name'] . '[foreach]" value="' . esc_html( $current_field["foreach"] ) . '" class="pmai_foreach widefat rad4"/>' ); ?>
						<a href="http://www.wpallimport.com/documentation/advanced-custom-fields/repeater-fields/"
							target="_blank">
							<?php _e( '(documentation)', 'mb-wpai' ); ?>
						</a>
					</p>
				</div>
			</div>
		</div>
		<div
			class="switcher-target-is_variable_<?= $field_name ?>_<?= $field['id'] ?>_yes_csv">
			<div class="input sub_input">
				<div class="input">
					<p>
						<?php printf( __( "Separator Character %s" ), '<input type="text" name="' . $field['_name'] . '[separator]" value="' . ( ( empty( $current_field["separator"] ) ) ? '|' : $current_field["separator"] ) . '" class="pmai_variable_separator widefat rad4"/>' ); ?>
						<a href="#help" class="wpallimport-help" style="top:0;"
							title="<?php _e( 'Use this option when importing a CSV file with a column or columns that contains the repeating data, separated by separators. For example, if you had a repeater with two fields - image URL and caption, and your CSV file had two columns, image URL and caption, with values like \'url1,url2,url3\' and \'caption1,caption2,caption3\', use this option and specify a comma as the separator.', 'mb-wpai' ) ?>">?</a>
					</p>
				</div>
			</div>
		</div>
	</div>

	<?php
	// -- Body
	?>


	<?php
	// -- Footer
	?>
	<div class="wpallimport-clear"></div>
	<div
		class="switcher-target-is_variable_<?= $field_name ?>_<?= $field['id'] ?>_no">
		<div class="input sub_input">
			<ul class="hl clearfix repeater-footer">
				<li class="right">
					<a href="javascript:void(0);" class="acf-button delete_row" style="margin-left:10px;color:#fff;">
						<?php _e( 'Delete Row', 'mb-wpai' ); ?>
					</a>
				</li>
				<li class="right">
					<a class="add-row-end acf-button" href="javascript:void(0);" style="color:#fff;">
						<?php _e( "Add Row", 'mb-wpai' ); ?>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>