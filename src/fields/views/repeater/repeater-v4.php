<table class="widefat acf-input-table row_layout">
    <tbody>
    <?php
    if (!empty($current_field['rows'])) : foreach ($current_field['rows'] as $key => $row): if ("ROWNUMBER" == $key) continue; ?>
        <tr class="row">
            <td class="order" style="padding:8px;"><?= $key; ?></td>
            <td class="acf_input-wrap" style="padding:0 !important;">
                <table class="widefat acf_input" style="border:none;">
                    <tbody>
                    <?php
                    if (!empty($fields)){
                        /** @var \wpai_meta_box_add_on\fields\Field $subField */
                        foreach ($fields as $subField){
                            ?>
                            <tr class="field sub_field field_type-<?= $subField->getType();?> field_key-<?= $subField->getFieldKey();?>">
                                <td class="label">
                                    <?= $subField->getFieldLabel();?>
                                </td>
                                <td>
                                    <div class="inner input">
                                        <?php
                                        $subField->setFieldInputName($field_name . "[" . $field['id'] . "][rows][" . $key . "]");
                                        $subField->view();
                                        ?>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
    <?php endforeach; endif; ?>
    <tr class="row-clone">
        <td class="order" style="padding:8px;"></td>
        <td class="acf_input-wrap" style="padding:0 !important;">
            <table class="widefat acf_input" style="border:none;">
                <tbody>
                <?php
                if (!empty($fields)){
                    /** @var \wpai_meta_box_add_on\fields\Field $subField */
                    foreach ($fields as $subField){
                        ?>
                        <tr class="field sub_field field_type-<?= $subField->getType();?> field_key-<?= $subField->getFieldKey();?>">
                            <td class="label">
                                <?= $subField->getFieldLabel();?>
                            </td>
                            <td>
                                <div class="inner input">
                                    <?php
                                    $subField->setFieldInputName($field_name . "[" . $field['id'] . "][rows][ROWNUMBER]");
                                    $subField->view();
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
