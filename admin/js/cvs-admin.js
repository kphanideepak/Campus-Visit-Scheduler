/**
 * Admin JavaScript for Campus Visit Scheduler
 *
 * @package CampusVisitScheduler
 */

(function($) {
    'use strict';

    // Add time slot (recurring)
    $('#add-recurring-slot-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $button = $form.find('button[type="submit"]');

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_add_time_slot',
                nonce: cvs_admin.nonce,
                tour_type: $form.find('[name="tour_type"]').val(),
                day_of_week: $form.find('[name="day_of_week"]').val(),
                time_slot: $form.find('[name="time_slot"]').val(),
                max_groups: $form.find('[name="max_groups"]').val()
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Add time slot (one-off)
    $('#add-oneoff-slot-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $button = $form.find('button[type="submit"]');

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_add_time_slot',
                nonce: cvs_admin.nonce,
                tour_type: $form.find('[name="tour_type"]').val(),
                specific_date: $form.find('[name="specific_date"]').val(),
                time_slot: $form.find('[name="time_slot"]').val(),
                max_groups: $form.find('[name="max_groups"]').val()
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Delete time slot
    $(document).on('click', '.cvs-delete-slot', function() {
        if (!confirm(cvs_admin.strings.confirm_delete)) {
            return;
        }

        var $button = $(this);
        var id = $button.data('id');

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_delete_time_slot',
                nonce: cvs_admin.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    $button.closest('tr').fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Add blackout date
    $('#add-blackout-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $button = $form.find('button[type="submit"]');

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_add_blackout_date',
                nonce: cvs_admin.nonce,
                blackout_date: $form.find('[name="blackout_date"]').val(),
                reason: $form.find('[name="reason"]').val()
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Delete blackout date
    $(document).on('click', '.cvs-delete-blackout', function() {
        if (!confirm(cvs_admin.strings.confirm_delete)) {
            return;
        }

        var $button = $(this);
        var id = $button.data('id');

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_delete_blackout_date',
                nonce: cvs_admin.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    $button.closest('tr').fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Exclusion period form variables
    var $exclusionForm = $('#cvs-exclusion-period-form');
    var $exclusionIdField = $('#exclusion_id');
    var $exclusionFormTitle = $('#cvs-exclusion-form-title');
    var $exclusionSubmitBtn = $('#cvs-exclusion-submit-btn');
    var $exclusionCancelBtn = $('#cvs-exclusion-cancel-btn');
    var isEditingExclusion = false;

    // Reset exclusion form to add mode
    function resetExclusionForm() {
        $exclusionForm[0].reset();
        $exclusionIdField.val('');
        $exclusionFormTitle.text(cvs_admin.strings.add_period || 'Add Holiday Period');
        $exclusionSubmitBtn.text(cvs_admin.strings.add_period_btn || 'Add Period');
        $exclusionCancelBtn.hide();
        isEditingExclusion = false;
    }

    // Add/Update exclusion period
    $exclusionForm.on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $button = $form.find('button[type="submit"]');
        var exclusionId = $exclusionIdField.val();
        var action = exclusionId ? 'cvs_update_exclusion_period' : 'cvs_add_exclusion_period';

        // Validate dates
        var startDate = $form.find('[name="start_date"]').val();
        var endDate = $form.find('[name="end_date"]').val();

        if (startDate && endDate) {
            var start = new Date(startDate);
            var end = new Date(endDate);

            // For non-recurring periods, end must be after start
            var isRecurring = $form.find('[name="recurring_yearly"]').is(':checked');
            if (!isRecurring && end < start) {
                alert(cvs_admin.strings.invalid_dates || 'End date must be after start date for non-recurring periods.');
                return;
            }
        }

        $button.prop('disabled', true);

        var data = {
            action: action,
            nonce: cvs_admin.nonce,
            period_name: $form.find('[name="period_name"]').val(),
            start_date: startDate,
            end_date: endDate,
            recurring_yearly: $form.find('[name="recurring_yearly"]').is(':checked') ? '1' : '0'
        };

        if (exclusionId) {
            data.id = exclusionId;
        }

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Edit exclusion period - populate form
    $(document).on('click', '.cvs-edit-exclusion', function() {
        var $button = $(this);

        // Populate form with existing data
        $exclusionIdField.val($button.data('id'));
        $('#period_name').val($button.data('name'));
        $('#start_date').val($button.data('start'));
        $('#end_date').val($button.data('end'));
        $('#recurring_yearly').prop('checked', $button.data('recurring') == '1');

        // Update form UI for edit mode
        $exclusionFormTitle.text(cvs_admin.strings.edit_period || 'Edit Holiday Period');
        $exclusionSubmitBtn.text(cvs_admin.strings.update_period_btn || 'Update Period');
        $exclusionCancelBtn.show();
        isEditingExclusion = true;

        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#cvs-exclusion-form-container').offset().top - 50
        }, 300);
    });

    // Cancel editing exclusion period
    $exclusionCancelBtn.on('click', function() {
        resetExclusionForm();
    });

    // Delete exclusion period
    $(document).on('click', '.cvs-delete-exclusion', function() {
        if (!confirm(cvs_admin.strings.confirm_delete_period || 'Are you sure you want to delete this holiday period?')) {
            return;
        }

        var $button = $(this);
        var id = $button.data('id');

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_delete_exclusion_period',
                nonce: cvs_admin.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    $button.closest('tr').fadeOut(function() {
                        $(this).remove();
                        // Check if table is empty
                        if ($('#exclusion-periods-table tbody tr').length === 0) {
                            $('#exclusion-periods-table tbody').html(
                                '<tr class="no-items"><td colspan="6">' +
                                (cvs_admin.strings.no_periods || 'No holiday exclusion periods configured.') +
                                '</td></tr>'
                            );
                        }
                    });
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Add notification recipient
    $('#add-recipient-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $button = $form.find('button[type="submit"]');

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_add_recipient',
                nonce: cvs_admin.nonce,
                email: $form.find('[name="email"]').val(),
                notify_new_booking: $form.find('[name="notify_new_booking"]').is(':checked') ? '1' : '0',
                notify_cancellation: $form.find('[name="notify_cancellation"]').is(':checked') ? '1' : '0'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Delete notification recipient
    $(document).on('click', '.cvs-delete-recipient', function() {
        if (!confirm(cvs_admin.strings.confirm_delete)) {
            return;
        }

        var $button = $(this);
        var id = $button.data('id');

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_delete_recipient',
                nonce: cvs_admin.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    $button.closest('tr').fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Cancel booking
    $(document).on('click', '.cvs-cancel-booking', function() {
        if (!confirm(cvs_admin.strings.confirm_cancel)) {
            return;
        }

        var $button = $(this);
        var id = $button.data('id');
        var notify = confirm('Send cancellation email to parent?');

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_cancel_booking',
                nonce: cvs_admin.nonce,
                id: id,
                notify: notify ? '1' : '0'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Resend confirmation email
    $(document).on('click', '.cvs-resend-confirmation', function() {
        var $button = $(this);
        var id = $button.data('id');

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_resend_confirmation',
                nonce: cvs_admin.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    alert(cvs_admin.strings.email_sent);
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Save admin notes
    $('#save-admin-notes').on('click', function() {
        var $button = $(this);
        var id = $button.data('id');
        var notes = $('#admin-notes').val();

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_save_admin_notes',
                nonce: cvs_admin.nonce,
                id: id,
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    $('#notes-saved-message').fadeIn().delay(2000).fadeOut();
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // ==============================================
    // Form Fields Settings Tab
    // ==============================================

    // Toggle built-in field enabled/disabled
    $(document).on('change', '.cvs-toggle-builtin-field', function() {
        var $checkbox = $(this);
        var fieldId = $checkbox.data('field-id');
        var enabled = $checkbox.is(':checked') ? '1' : '0';
        var $label = $checkbox.siblings('.cvs-toggle-label');

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_toggle_builtin_field',
                nonce: cvs_admin.nonce,
                field_id: fieldId,
                enabled: enabled
            },
            success: function(response) {
                if (response.success) {
                    $label.text(enabled === '1' ? 'On' : 'Off');
                } else {
                    alert(response.data || cvs_admin.strings.error);
                    $checkbox.prop('checked', !$checkbox.is(':checked'));
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
                $checkbox.prop('checked', !$checkbox.is(':checked'));
            }
        });
    });

    // Show/hide options textarea based on field type
    $('#cvs-cf-field-type').on('change', function() {
        if ($(this).val() === 'select') {
            $('#cvs-cf-options-row').show();
        } else {
            $('#cvs-cf-options-row').hide();
        }
    });

    // Custom field form variables
    var $cfForm = $('#cvs-custom-field-form');
    var $cfEditId = $('#cvs-edit-field-id');
    var $cfFormTitle = $('#cvs-custom-field-form-title');
    var $cfSubmitBtn = $('#cvs-cf-submit-btn');
    var $cfCancelBtn = $('#cvs-cf-cancel-btn');

    // Reset custom field form to add mode
    function resetCustomFieldForm() {
        $cfForm[0].reset();
        $cfEditId.val('');
        $cfFormTitle.text('Add Custom Field');
        $cfSubmitBtn.text('Add Field');
        $cfCancelBtn.hide();
        $('#cvs-cf-field-type').trigger('change');
    }

    // Add / Update custom field form submit
    $cfForm.on('submit', function(e) {
        e.preventDefault();

        var editId = $cfEditId.val();
        var action = editId ? 'cvs_update_custom_field' : 'cvs_add_custom_field';
        var $button = $cfSubmitBtn;

        var label = $('#cvs-cf-label').val();
        if (!label || !label.trim()) {
            alert('Please enter a field label.');
            return;
        }

        $button.prop('disabled', true);

        var data = {
            action: action,
            nonce: cvs_admin.nonce,
            label: label,
            field_type: $('#cvs-cf-field-type').val(),
            required: $('#cvs-cf-required').is(':checked') ? '1' : '0',
            placeholder: $('#cvs-cf-placeholder').val(),
            max_length: $('#cvs-cf-max-length').val(),
            options: $('#cvs-cf-options').val()
        };

        if (editId) {
            data.field_id = editId;
        }

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Edit custom field - populate form
    $(document).on('click', '.cvs-edit-custom-field', function() {
        var $button = $(this);

        $cfEditId.val($button.data('field-id'));
        $('#cvs-cf-label').val($button.data('label'));
        $('#cvs-cf-field-type').val($button.data('field-type')).trigger('change');
        $('#cvs-cf-required').prop('checked', $button.data('required') === 1 || $button.data('required') === '1');
        $('#cvs-cf-placeholder').val($button.data('placeholder'));
        $('#cvs-cf-max-length').val($button.data('max-length'));
        $('#cvs-cf-options').val($button.data('options'));

        $cfFormTitle.text('Edit Custom Field');
        $cfSubmitBtn.text('Update Field');
        $cfCancelBtn.show();

        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#cvs-custom-field-form-container').offset().top - 50
        }, 300);
    });

    // Cancel editing custom field
    $cfCancelBtn.on('click', function() {
        resetCustomFieldForm();
    });

    // Delete custom field
    $(document).on('click', '.cvs-delete-custom-field', function() {
        if (!confirm(cvs_admin.strings.confirm_delete)) {
            return;
        }

        var $button = $(this);
        var fieldId = $button.data('field-id');

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_delete_custom_field',
                nonce: cvs_admin.nonce,
                field_id: fieldId
            },
            success: function(response) {
                if (response.success) {
                    $button.closest('tr').fadeOut(function() {
                        $(this).remove();
                        if ($('#cvs-custom-fields-table tbody tr').length === 0) {
                            $('#cvs-custom-fields-table tbody').html(
                                '<tr class="no-items"><td colspan="5">No custom fields yet. Add one below.</td></tr>'
                            );
                        }
                    });
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Reorder custom field
    $(document).on('click', '.cvs-reorder-field', function() {
        var $button = $(this);
        var fieldId = $button.data('field-id');
        var direction = $button.data('direction');

        $button.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_reorder_field',
                nonce: cvs_admin.nonce,
                field_id: fieldId,
                direction: direction
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

})(jQuery);
