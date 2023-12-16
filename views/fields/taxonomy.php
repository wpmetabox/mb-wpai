<?php
/**
 * @var array $field
 * @var string $field_name
 * @var string $field_value
 * @var string $current_is_multiple_field_value
 * @var string $current_multiple_value
 */
$field_name = str_replace(['[',']'], '', $field_name);
$current_field = $field_value;
?>
<div class="input">
    <div class="main_choise">
        <input type="radio" id="is_multiple_field_value_<?= $field_name ?>_<?= $field['id'];?>_yes" class="switcher" name="is_multiple_field_value<?php echo $field_name; ?>[<?= $field['id'];?>]" value="yes" <?php echo 'no' != $current_is_multiple_field_value ? 'checked="checked"': '' ?>/>
        <label for="is_multiple_field_value_<?= $field_name ?>_<?= $field['id'];?>_yes" class="chooser_label"><?php _e("Select value for all records"); ?></label>
    </div>
    <div class="wpallimport-clear"></div>
    <div class="switcher-target-is_multiple_field_value_<?= $field_name ?>_<?= $field['id'];?>_yes">
        <div class="input sub_input">
            <div class="input">
                <?php
                    $tax_field = [
                        'id' => $field['_name'],
                        'name' => $field['_name'],
                        'type' => 'taxonomy',
                        'taxonomy' => $field['taxonomy'],
                        'std' => $field_value,
                    ];

                    $tax_fields = \RW_Meta_Box::normalize_fields( [$tax_field] );
                    RWMB_Field::call('show', $tax_fields[0] , false );
                ?>
            </div>
        </div>
    </div>
</div>
<div class="clear"></div>
<div class="input" style="overflow:hidden;">
    <div class="main_choise">
        <input type="radio" id="is_multiple_field_value_<?= $field_name ?>_<?= $field['id'];?>_no" class="switcher" name="is_multiple_field_value<?php echo $field_name; ?>[<?= $field['id'];?>]" value="no" <?php echo 'no' == $current_is_multiple_field_value ? 'checked="checked"': '' ?>/>
        <label for="is_multiple_field_value_<?= $field_name ?>_<?= $field['id'];?>_no" class="chooser_label"><?php _e('Set with XPath', 'mb-wpai' )?></label>
    </div>
    <div class="wpallimport-clear"></div>
    <div class="switcher-target-is_multiple_field_value_<?= $field_name ?>_<?= $field['id'];?>_no">
        <div class="input sub_input">
            <div class="input">
                <table class="pmai_taxonomy post_taxonomy">
                    <tr>
                        <td>
                            <div class="col2" style="clear: both;">
                                <ol class="sortable no-margin">
                                    <?php
                                    if (!is_array($current_field)){
                                        $current_field = [
                                            'value' => $current_field,
                                            'delim' => ','
                                        ];
                                    }
                                    if ( ! empty($field_value) ):
                                        $taxonomies_hierarchy = json_decode($field_value);

                                        if ( ! empty($taxonomies_hierarchy) and is_array($taxonomies_hierarchy)): $i = 0; foreach ($taxonomies_hierarchy as $cat) { $i++;
                                            if ( is_null($cat->parent_id) or empty($cat->parent_id) )
                                            {
                                                ?>
                                                <li id="item_<?php echo $i; ?>" class="dragging">
                                                    <div class="drag-element">
                                                        <input type="text" class="widefat xpath_field rad4" value="<?php echo esc_attr($cat->xpath); ?>"/>
                                                    </div>
                                                    <?php if ( $i > 1 ): ?><a href="javascript:void(0);" class="icon-item remove-ico"></a><?php endif; ?>

                                                    <?php echo reverse_taxonomies_html($taxonomies_hierarchy, $cat->item_id, $i); ?>
                                                </li>
                                                <?php
                                            }
                                        }; else:?>
                                            <li id="item_1" class="dragging">
                                                <div class="drag-element" >
                                                    <input type="text" class="widefat xpath_field rad4" value=""/>
                                                    <a href="javascript:void(0);" class="icon-item remove-ico"></a>
                                                </div>
                                            </li>
                                        <?php endif;
                                    else: ?>
                                        <li id="item_1" class="dragging">
                                            <div class="drag-element">
                                                <input type="text" class="widefat xpath_field rad4" value=""/>
                                                <a href="javascript:void(0);" class="icon-item remove-ico"></a>
                                            </div>
                                        </li>
                                    <?php endif;?>
                                    <li id="item" class="template">
                                        <div class="drag-element">
                                            <input type="text" class="widefat xpath_field rad4" value=""/>
                                            <a href="javascript:void(0);" class="icon-item remove-ico"></a>
                                        </div>
                                    </li>
                                </ol>
                                <input type="hidden" class="hierarhy-output" name="<?= $field['_name'] ?>" value="<?= esc_attr($field_value) ?>"/>

                                <div class="input">
                                    <label for=""><?php _e('Separated by'); ?></label>
                                    <input
                                    type="text"
                                        style="width:5%; text-align:center; padding-left: 25px;"
                                        value="<?php echo (!empty($current_field['delim'])) ? esc_attr( $current_field['delim'] ) : ',';?>"
                                            name="<?= $field_name . '[delim]' ?>"
                                        class="small rad4">
                                </div>
                                <div class="delim">
                                    <a href="javascript:void(0);" class="icon-item add-new-ico"><?php _e('Add more','mb-wpai');?></a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>