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

})(jQuery);
