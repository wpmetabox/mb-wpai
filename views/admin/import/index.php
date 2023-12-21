<div class="wpallimport-collapsed closed pmai_options">
    <div class="wpallimport-content-section">
        <div class="wpallimport-collapsed-header">
            <h3><?php _e( 'Meta Box Add-On', 'mb-wpai' ); ?></h3>
        </div>
        <div class="wpallimport-collapsed-content" style="padding: 0;">
            <div class="wpallimport-collapsed-content-inner">
                <table class="form-table" style="max-width:none;">
                    <tr>
                        <td colspan="3">
							<?php if ( ! empty( $meta_boxes ) ): ?>
                                <p><strong><?php _e( "Please choose your meta box.", 'mb-wpai' ); ?></strong></p>
                                <ul>
									<?php
									foreach ( $meta_boxes as $id => $meta_box ) {
										$meta_box               = $meta_box->meta_box;
										$is_show_meta_box_group = apply_filters( 'wp_all_import_meta_box_is_show_group', true, $meta_box );
										$id                     = $meta_box['id'];
										$name                   = $id;
										?>
                                        <li>
                                            <input type="hidden" name="meta_box[<?= $id; ?>]"
                                                   value="<?= $is_show_meta_box_group ? '0' : '1' ?>"/>
											<?php if ( $is_show_meta_box_group ): ?>
                                                <input id="meta_box_<?= $post_type . '_' . $id; ?>" type="checkbox"
                                                       name="meta_box[<?= $name; ?>]"
												       <?php if ( ! empty( $post['meta_box'][ $id ] ) || isset( $name ) && ! empty( $post['meta_box'][ $name ] ) ): ?>checked="checked"<?php endif; ?>
                                                       value="1" rel="<?= $id; ?>" class="pmai_meta_boxes"/>
                                                <label for="meta_box_<?= $post_type . '_' . $id; ?>"><?= $meta_box['title']; ?></label>
											<?php endif; ?>
                                        </li>
										<?php
									}
									?>
                                </ul>
                                <div class="meta_boxes"></div>
							<?php
							else:
								?>
                                <p><strong><?php _e( "Please create meta boxes.", 'mb-wpai' ); ?></strong></p>
							<?php
							endif;
							?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>