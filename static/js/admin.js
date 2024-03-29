/**
 * plugin admin area javascript
 */
(function ($) {
    $(function () {

        if (!$('body.wpallimport-plugin').length) return; // do not execute any code if we are not on plugin page

        let pmai_repeater_clone = function ($parent) {

            let $clone = $parent.find('tbody:first').children('.row-clone:first').clone();
            let $number = parseInt($parent.find('tbody:first').children().length);

            $clone.removeClass('row-clone').addClass('row').find('td.order').html($number);
            $clone.find('.switcher').each(function () {
                $(this).attr({ 'id': $(this).attr('id').replace('ROWNUMBER', $number) });
            });
            $clone.find('.chooser_label').each(function () {
                $(this).attr({ 'for': $(this).attr('for').replace('ROWNUMBER', $number) });
            });
            $clone.find('div[class^=switcher-target]').each(function () {
                $(this).attr({ 'class': $(this).attr('class').replace('ROWNUMBER', $number) });
            });
            $clone.find('input, select, textarea').each(function () {
                let name = $(this).attr('name');
                if (name != undefined) $(this).attr({ 'name': $(this).attr('name').replace('ROWNUMBER', $number) });
            });

            $parent.find('.acf-input-table:first').find('tbody:first').append($clone);

            $parent.find('tr.row').find('.sortable').each(function () {
                if (!$(this).hasClass('ui-sortable') && !$(this).parents('tr.row-clone').length) {
                    $(this).pmxi_nestedSortable({
                        handle: 'div',
                        items: 'li.dragging',
                        toleranceElement: '> div',
                        update: function () {
                            $(this).parents('td:first').find('.hierarhy-output').val(window.JSON.stringify($(this).pmxi_nestedSortable('toArray', { startDepthCount: 0 })));
                            if ($(this).parents('td:first').find('input:first').val() == '') $(this).parents('td:first').find('.hierarhy-output').val('');
                        }
                    });
                }
            });

            pmai_init($parent);
        };

        $(document).on('click', '.add_layout_button', function () {

            let $parent = $(this).parents('.acf-flexible-content:first');

            let $dropdown = $parent.children('.add_layout').children('select'); //$('.add_layout select');

            if ($dropdown.val() == "" || $dropdown.val() == "Select Layout") return;

            let $clone = $parent.children('.clones:first').children('div.layout[data-layout = ' + $dropdown.val() + ']').clone();

            let $number = parseInt($parent.children('.values:first').children().length) + 1;

            $clone.find('.fc-layout-order:first').html($number);

            $clone.find('.switcher').each(function () {
                $(this).attr({ 'id': $(this).attr('id').replace('ROWNUMBER', $number) });
            });
            $clone.find('.chooser_label').each(function () {
                $(this).attr({ 'for': $(this).attr('for').replace('ROWNUMBER', $number) });
            });
            $clone.find('div[class^=switcher-target]').each(function () {
                $(this).attr({ 'class': $(this).attr('class').replace('ROWNUMBER', $number) });
            });
            $clone.find('input, select, textarea').each(function () {
                let name = $(this).attr('name');
                if (name != undefined) $(this).attr({ 'name': $(this).attr('name').replace('ROWNUMBER', $number) });
            });

            pmai_init($clone);

            $parent.children('div.values:first').append($clone);

        });

        $(document).on('click', '.delete_layout_button', function () {
            let $parent = $(this).parents('.acf-flexible-content:first');
            $parent.children('.values:first').children(':last').remove();
        });

        $(document).on('click', '.delete_row', function () {
            let $parent = $(this).parents('.repeater:first');
            $parent.find('tbody:first').children('.row:last').remove();
        });

        let pmai_get_meta_box = function (ths) {

            let request = {
                action: 'get_meta_boxes',
                security: wp_all_import_security,
                meta_box: ths.attr('rel')
            };

            if (typeof import_id != "undefined") request.id = import_id;

            let $ths = ths.parents('.pmai_options:first');

            $ths.find('.meta_boxes').prepend('<div class="pmai_preloader"></div>');

            $('.pmai_meta_boxes').attr('disabled', 'disabled');

            $.ajax({
                type: 'GET',
                url: ajaxurl,
                data: request,
                success: function (response) {
                    $('.pmai_meta_boxes').removeAttr('disabled');
                    $ths.find('.pmai_preloader').remove();
                    $ths.find('.meta_boxes').prepend(response.html);
                    pmai_init($ths.find('.single_meta_box:first'));
                    // swither show/hide logic
                    $ths.find('.meta_boxes').find('input.switcher').change();
                    $ths.find('.meta_boxes').find('input, textarea').bind('focus', function () {
                        let selected = $('.xml-element.selected');
                        if (selected.length) {
                            $(this).val($(this).val() + selected.attr('title').replace(/\/[^\/]*\//, '{') + '}');
                            selected.removeClass('selected');
                        }
                    });
                },
                error: function (jqXHR, textStatus) {
                    $('.pmai_meta_boxes').removeAttr('disabled');
                    $ths.find('.pmai_preloader').remove();
                    alert('Something went wrong. ' + textStatus);
                },
                dataType: "json"
            });
        }

        let pmai_reset_meta_boxes = function () {
            $('.pmai_options').find('.single_meta_box').remove();
            $('.pmai_options:visible').find('.pmai_meta_boxes:checked').each(function () {
                pmai_get_meta_box($(this));
            });
        }

        pmai_reset_meta_boxes();

        $('.pmxi_plugin').find('.nav-tab').click(function () {
            pmai_reset_meta_boxes();
        });

        $('.pmai_meta_boxes').on('change', function () {
            const acf = $(this).attr('rel');
            if ($(this).is(':checked')) {
                // if requsted ACF group doesn't exists
                if (!$(this).parents('.pmai_options:first').find('.single_meta_box[rel=' + acf + ']').length) {
                    pmai_get_meta_box($(this));
                }
            } else {
                if (confirm("Confirm removal?")) {
                    $(this).parents('.pmai_options:first').find('.single_meta_box[rel=' + acf + ']').remove();
                } else {
                    $(this).attr('checked', 'checked');
                }
            }
        });

        function pmai_init(ths) {

            ths.find('input.datetimepicker').datetimepicker({
                dateFormat: 'dd-mm-yy',
                timeFormat: 'hh:mm TT',
                ampm: true
            });

            ths.find('input.datepicker').datepicker({
                dateFormat: 'dd-mm-yy'
            });

            ths.find('.sortable').each(function () {
                if (!$(this).parents('tr.row-clone').length) {
                    $(this).pmxi_nestedSortable({
                        handle: 'div',
                        items: 'li.dragging',
                        toleranceElement: '> div',
                        update: function () {
                            $(this).parents('td:first').find('.hierarhy-output').val(window.JSON.stringify($(this).pmxi_nestedSortable('toArray', { startDepthCount: 0 })));
                            if ($(this).parents('td:first').find('input:first').val() == '') $(this).parents('td:first').find('.hierarhy-output').val('');
                        }
                    });
                }
            });

            ths.find('.repeater').find('.add-row-end').on('click', function () {
                let $parent = $(this).parents('.repeater:first');
                pmai_repeater_clone($parent);
            });

            ths.find('input.switcher').on("change", function (e) {
                if ($(this).is(':radio:checked')) {
                    $(this).parents('form').find('input.switcher:radio[name="' + $(this).attr('name') + '"]').not(this).change();
                }
                let $targets = $('.switcher-target-' + $(this).attr('id'));
                let is_show = $(this).is(':checked'); if ($(this).is('.switcher-reversed')) is_show = !is_show;
                if (is_show) {
                    $targets.slideDown();
                } else {
                    $targets.slideUp().find('.clear-on-switch').add($targets.filter('.clear-on-switch')).val('');
                }
            }).change();
        }

        $('.single_meta_box').find('.switcher').on('click', function () {
            $(this).parents('div.mb-input-wrap:first').find('.sortable').each(function () {
                if (!$(this).hasClass('ui-sortable') && !$(this).parents('tr.row-clone').length) {
                    $(this).pmxi_nestedSortable({
                        handle: 'div',
                        items: 'li.dragging',
                        toleranceElement: '> div',
                        update: function () {
                            $(this).parents('td:first').find('.hierarhy-output').val(window.JSON.stringify($(this).pmxi_nestedSortable('toArray', { startDepthCount: 0 })));
                            if ($(this).parents('td:first').find('input:first').val() == '') $(this).parents('td:first').find('.hierarhy-output').val('');
                        }
                    });
                }
            });
        });

        $(document).on('change', '.variable_repeater_mode', function () {
            const val = $(this).val();

            if ($(this).is(':checked') && val !== 'no') {
                var $parent = $(this).parents('.repeater:first');
                
                $parent.find('tbody:first').children('.row:not(:first)').remove();
                if ( ! $parent.find('tbody:first').children('.row').length) pmai_repeater_clone($parent);
            }
        });

    });
})(jQuery);
