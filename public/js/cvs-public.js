/**
 * Public JavaScript for Campus Visit Scheduler
 *
 * @package CampusVisitScheduler
 */

(function($) {
    'use strict';

    var CVS = {
        init: function() {
            this.bindEvents();
            this.updateTotalGroup();
        },

        bindEvents: function() {
            // Date selection
            $('#cvs-tour-date').on('change', this.handleDateChange.bind(this));

            // Group size changes
            $('#cvs-adults, #cvs-children').on('change', this.updateTotalGroup.bind(this));

            // Time slot selection
            $(document).on('click', '.cvs-time-slot:not(.cvs-time-slot-unavailable)', this.handleTimeSlotClick.bind(this));

            // Form submission
            $('#cvs-booking-form').on('submit', this.handleFormSubmit.bind(this));
        },

        handleDateChange: function(e) {
            var date = $(e.target).val();
            var $timeSlots = $('#cvs-time-slots');
            var $timeInput = $('#cvs-tour-time');

            if (!date) {
                $timeSlots.html('<p class="cvs-select-date-prompt">' + cvs_public.strings.select_date + '</p>');
                $timeInput.val('');
                return;
            }

            $timeSlots.html('<p class="cvs-loading-slots">' + cvs_public.strings.loading + '</p>');

            $.ajax({
                url: cvs_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'cvs_get_available_slots',
                    nonce: cvs_public.nonce,
                    date: date
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        var html = '';
                        $.each(response.data, function(index, slot) {
                            var availabilityClass = slot.available ? 'cvs-time-slot-available' : '';
                            var unavailableClass = !slot.available ? 'cvs-time-slot-unavailable' : '';
                            var spotsText = '';

                            if (slot.available) {
                                if (slot.remaining === 1) {
                                    spotsText = slot.remaining + ' ' + cvs_public.strings.spot_remaining;
                                } else {
                                    spotsText = slot.remaining + ' ' + cvs_public.strings.spots_remaining;
                                }
                            } else {
                                spotsText = cvs_public.strings.fully_booked;
                            }

                            html += '<div class="cvs-time-slot ' + unavailableClass + '" data-time="' + slot.time + '">';
                            html += '<span class="cvs-time-slot-time">' + slot.time_display + '</span>';
                            html += '<span class="cvs-time-slot-availability ' + availabilityClass + '">' + spotsText + '</span>';
                            html += '</div>';
                        });
                        $timeSlots.html(html);
                    } else {
                        $timeSlots.html('<p class="cvs-no-slots">' + (response.data || cvs_public.strings.no_slots) + '</p>');
                    }
                    $timeInput.val('');
                },
                error: function() {
                    $timeSlots.html('<p class="cvs-error">' + cvs_public.strings.error + '</p>');
                }
            });
        },

        handleTimeSlotClick: function(e) {
            var $slot = $(e.currentTarget);
            var time = $slot.data('time');

            $('.cvs-time-slot').removeClass('cvs-time-slot-selected');
            $slot.addClass('cvs-time-slot-selected');
            $('#cvs-tour-time').val(time);
        },

        updateTotalGroup: function() {
            var adults = parseInt($('#cvs-adults').val()) || 0;
            var children = parseInt($('#cvs-children').val()) || 0;
            var total = adults + children;
            var $total = $('#cvs-total-group');
            var maxSize = cvs_public.max_group_size;

            $total.text(total);

            if (total > maxSize) {
                $total.addClass('cvs-error');
            } else {
                $total.removeClass('cvs-error');
            }
        },

        handleFormSubmit: function(e) {
            e.preventDefault();

            var $form = $(e.target);
            var $submit = $('#cvs-submit-booking');
            var $messages = $('#cvs-form-messages');

            // Clear previous messages
            $messages.empty();

            // Validate required fields
            var errors = this.validateForm($form);
            if (errors.length > 0) {
                this.showErrors(errors);
                return;
            }

            // Disable submit button
            $submit.prop('disabled', true).text(cvs_public.strings.loading);

            $.ajax({
                url: cvs_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'cvs_submit_booking',
                    nonce: cvs_public.nonce,
                    tour_date: $('#cvs-tour-date').val(),
                    tour_time: $('#cvs-tour-time').val(),
                    parent_name: $('#cvs-parent-name').val(),
                    email: $('#cvs-email').val(),
                    phone: $('#cvs-phone').val(),
                    adults: $('#cvs-adults').val(),
                    children: $('#cvs-children').val(),
                    child_name: $('#cvs-child-name').val(),
                    year_level: $('#cvs-year-level').val(),
                    special_requirements: $('#cvs-special-requirements').val()
                },
                success: function(response) {
                    if (response.success) {
                        CVS.showConfirmation(response.data);
                    } else {
                        CVS.showErrors([response.data || cvs_public.strings.error]);
                        $submit.prop('disabled', false).text('Book Tour');
                    }
                },
                error: function() {
                    CVS.showErrors([cvs_public.strings.error]);
                    $submit.prop('disabled', false).text('Book Tour');
                }
            });
        },

        validateForm: function($form) {
            var errors = [];
            var maxSize = cvs_public.max_group_size;
            var minSize = cvs_public.min_group_size;

            // Check required fields
            if (!$('#cvs-tour-date').val()) {
                errors.push(cvs_public.strings.select_date);
            }

            if (!$('#cvs-tour-time').val()) {
                errors.push(cvs_public.strings.select_time);
            }

            if (!$('#cvs-parent-name').val().trim()) {
                errors.push('Please enter your name.');
            }

            if (!$('#cvs-email').val().trim()) {
                errors.push('Please enter your email address.');
            } else if (!this.isValidEmail($('#cvs-email').val())) {
                errors.push('Please enter a valid email address.');
            }

            if (!$('#cvs-phone').val().trim()) {
                errors.push('Please enter your phone number.');
            }

            // Check group size
            var adults = parseInt($('#cvs-adults').val()) || 0;
            var children = parseInt($('#cvs-children').val()) || 0;
            var total = adults + children;

            if (total < minSize) {
                errors.push('Group size must be at least ' + minSize + '.');
            }

            if (total > maxSize) {
                errors.push('Group size cannot exceed ' + maxSize + ' people.');
            }

            return errors;
        },

        isValidEmail: function(email) {
            var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        },

        showErrors: function(errors) {
            var $messages = $('#cvs-form-messages');
            var html = '';

            $.each(errors, function(index, error) {
                html += '<div class="cvs-message cvs-message-error">' + error + '</div>';
            });

            $messages.html(html);

            // Scroll to messages
            $('html, body').animate({
                scrollTop: $messages.offset().top - 100
            }, 500);
        },

        showConfirmation: function(data) {
            var $form = $('#cvs-booking-form');
            var $confirmation = $('#cvs-booking-confirmation');

            var html = '<div class="cvs-confirmation-wrapper">';
            html += '<div class="cvs-confirmation-icon">';
            html += '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
            html += '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>';
            html += '<polyline points="22 4 12 14.01 9 11.01"></polyline>';
            html += '</svg>';
            html += '</div>';
            html += '<h2>Booking Confirmed!</h2>';
            html += '<p class="cvs-confirmation-message">Thank you for booking a campus tour. A confirmation email has been sent to your email address.</p>';
            html += '<div class="cvs-booking-details">';
            html += '<h3>Booking Details</h3>';
            html += '<table class="cvs-details-table">';
            html += '<tr><th>Reference Number</th><td><strong class="cvs-reference">' + data.booking_reference + '</strong></td></tr>';
            html += '<tr><th>Date</th><td>' + data.tour_date + '</td></tr>';
            html += '<tr><th>Time</th><td>' + data.tour_time + '</td></tr>';
            html += '<tr><th>Name</th><td>' + data.parent_name + '</td></tr>';
            html += '<tr><th>Group Size</th><td>' + data.group_size + ' people</td></tr>';
            html += '</table>';
            html += '</div>';
            html += '<div class="cvs-confirmation-actions">';
            html += '<a href="' + cvs_public.ajax_url + '?action=cvs_download_ics&reference=' + data.booking_reference + '" class="cvs-btn cvs-btn-secondary">Add to Calendar</a>';
            html += '</div>';
            html += '<div class="cvs-confirmation-note">';
            html += '<p>Please arrive 10 minutes before your scheduled tour time and report to the main reception area.</p>';
            html += '<p>If you need to cancel or modify your booking, please contact us as soon as possible.</p>';
            html += '</div>';
            html += '</div>';

            $form.hide();
            $confirmation.html(html).show();

            // Scroll to top of confirmation
            $('html, body').animate({
                scrollTop: $confirmation.offset().top - 50
            }, 500);
        }
    };

    $(document).ready(function() {
        CVS.init();
    });

})(jQuery);
