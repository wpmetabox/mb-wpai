<?php

if (!empty($fields)){
    /** @var \wpai_meta_box_add_on\fields\Field $subField */
    foreach ($fields as $subField){
        ?>
        <tr class="field sub_field field_type-<?= $subField->getType();?> field_key-<?= $subField->getFieldKey();?>">
            <td>
                <div class="inner">
                    <?php
                    $subField->setFieldInputName($field_name . '[' . $field['id'] . ']');
                    $subField->view();
                    ?>
                </div>
            </td>
        </tr>
        <?php
    }
}