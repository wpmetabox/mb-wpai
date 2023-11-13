<div class="input">
    <p class="label"><?php _e("Address"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['address'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][address]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("Lat"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['lat'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][lat]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("Lng"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['lng'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][lng]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("Zoom"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['zoom'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][zoom]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("Street Number"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['street_number'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][street_number]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("Street name"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['street_name'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][street_name]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("Street short name"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['street_short_name'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][street_short_name]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("City"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['city'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][city]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("State"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['state'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][state]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("State short"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['state_short'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][state_short]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("Post code"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['post_code'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][post_code]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("Country"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['country'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][country]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("Country short"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['country_short'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][country_short]" class="text widefat rad4"/>
    </div>
</div>
<div class="input">
    <p class="label"><?php _e("Place ID"); ?></p>
    <div class="mb-input-wrap">
        <input type="text" placeholder="" value="<?= esc_attr( $current_field['place_id'] );?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][place_id]" class="text widefat rad4"/>
    </div>
</div>
<div class="wpallimport-collapsed wpallimport-section wpallimport-sub-options wpallimport-dependent-options">
    <div class="wpallimport-content-section wpallimport-bottom-radius">
        <div style="padding: 0px; display: block;" class="wpallimport-collapsed-content">
            <div class="wpallimport-collapsed-content-inner">
                <label for="realhomes_addonaddress_geocode">Google Geocode API Settings</label>
                <div class="input">
                    <div class="form-field wpallimport-radio-field wpallimport-realhomes_addonaddress_geocode_address_no_key">
                        <input type="radio" <?php if (empty($current_field['address_geocode']) or esc_attr( $current_field['address_geocode'] ) == 'address_no_key'):?>checked="checked"<?php endif;?> value="address_no_key" name="fields<?= $field_name; ?>[<?= $field['id'];?>][address_geocode]" class="switcher" id="<?= sanitize_key($field_name); ?>_geocode_address_no_key">
                        <label for="<?= sanitize_key($field_name); ?>_geocode_address_no_key" class="chooser_label">No API Key</label>
                        <a style="position: relative; top: -2px;" class="wpallimport-help" href="#help" title="Limited number of requests.">?</a>
                    </div>
                    <div class="form-field wpallimport-radio-field wpallimport-<?= $field_name; ?>_<?= $field_name; ?>_geocode_address_google_developers">
                        <input type="radio" value="address_google_developers" name="fields<?= $field_name; ?>[<?= $field['id'];?>][address_geocode]" class="switcher" id="<?= sanitize_key($field_name); ?>_geocode_address_google_developers" <?php if (esc_attr( $current_field['address_geocode'] ) == 'address_google_developers'):?>checked="checked"<?php endif;?> >
                        <label for="<?= sanitize_key($field_name); ?>_geocode_address_google_developers" class="chooser_label">Google Developers API Key - <a href="https://developers.google.com/maps/documentation/geocoding/#api_key">Get free API key</a></label>
                        <a style="position: relative; top: -2px;" class="wpallimport-help" href="#help" title="Up to 2500 requests per day and 5 requests per second.">?</a>
                        <div class="switcher-target-<?= sanitize_key($field_name); ?>_geocode_address_google_developers" style="display: none;">
                            <div class="input sub_input">
                                <label for="<?= sanitize_key($field_name); ?>_google_developers_api_key">API Key</label>
                                <div class="input">
                                    <input type="text" style="width:100%;" value="<?= esc_attr( $current_field['address_google_developers_api_key'] );?>" id="<?= sanitize_key($field_name); ?>_google_developers_api_key" name="fields<?= $field_name; ?>[<?= $field['id'];?>][address_google_developers_api_key]">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-field wpallimport-radio-field wpallimport-<?= $field_name; ?>_<?= $field_name; ?>_geocode_address_google_for_work">
                        <input type="radio" value="address_google_for_work" name="fields<?= $field_name; ?>[<?= $field['id'];?>][address_geocode]" class="switcher" <?php if (esc_attr( $current_field['address_geocode'] ) == 'address_google_for_work'):?>checked="checked"<?php endif;?> id="<?= sanitize_key($field_name); ?>_geocode_address_google_for_work">
                        <label for="<?= sanitize_key($field_name); ?>_geocode_address_google_for_work" class="chooser_label">Google for Work Client ID &amp; Digital Signature - <a href="https://developers.google.com/maps/documentation/business">Sign up for Google for Work</a></label>
                        <a style="position: relative; top: -2px;" class="wpallimport-help" href="#help" title="Up to 100,000 requests per day and 10 requests per second">?</a>
                        <div class="switcher-target-<?= sanitize_key($field_name); ?>_geocode_address_google_for_work" style="display: none;">
                            <div class="input sub_input">
                                <label for="<?= sanitize_key($field_name); ?>_google_for_work_client_id">Google for Work Client ID</label>
                                <div class="input">
                                    <input type="text" style="width:100%;" value="<?= esc_attr( $current_field['address_google_for_work_client_id'] );?>" id="<?= sanitize_key($field_name); ?>_google_for_work_client_id" name="fields<?= $field_name; ?>[<?= $field['id'];?>][address_google_for_work_client_id]">
                                </div>
                                <label for="<?= $field_name; ?>_<?= $field_name; ?>_google_for_work_digital_signature">Google for Work Digital Signature</label>
                                <div class="input">
                                    <input type="text" style="width:100%;" value="<?= esc_attr( $current_field['address_google_for_work_digital_signature'] );?>" id="<?= sanitize_key($field_name); ?>_google_for_work_digital_signature" name="fields<?= $field_name; ?>[<?= $field['id'];?>][address_google_for_work_digital_signature]">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>