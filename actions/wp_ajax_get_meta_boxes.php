<?php
use wpai_meta_box_add_on\meta_boxes\MetaboxFactory;

/**
 *  Render ACF group
 */
function mbai_wp_ajax_get_meta_boxes() {

    if (!check_ajax_referer('wp_all_import_secure', 'security', FALSE)) {
        wp_send_json(['message' => __('Security check', 'mbai')]);
    }

    if (!current_user_can(PMXI_Plugin::$capabilities)) {
        wp_send_json(['html' => __('Security check', 'mbai')]);
    }

    ob_start();

    $meta_boxes = PMXI_Plugin::$session->meta_boxes;
    
    if (!isset($_GET['meta_box']) || !isset($meta_boxes[$_GET['meta_box']])) {
        wp_send_json(['html' => __('Meta Box does not exist', 'mbai')]);
    }

    $selected_meta_box = $meta_boxes[$_GET['meta_box']];
    
    $import = new PMXI_Import_Record();

    if (!empty($_GET['id'])) {
        $import->getById($_GET['id']);
    }

    $is_loaded_template = (!empty(PMXI_Plugin::$session->is_loaded_template)) ? PMXI_Plugin::$session->is_loaded_template : FALSE;

    if ($is_loaded_template) {
        $default = MBAI_Plugin::get_default_import_options();
        $template = new PMXI_Template_Record();
        if (!$template->getById($is_loaded_template)->isEmpty()) {
            $options = (!empty($template->options) ? $template->options : array()) + $default;
        }
    }
    elseif (!$import->isEmpty()) {
        $options = $import->options;
    }
    else {
        $options = PMXI_Plugin::$session->options;
    }

    $meta_box = MetaboxFactory::create($selected_meta_box->meta_box, $options);
    $meta_box->view();

    wp_send_json(['html' => ob_get_clean()]);
}