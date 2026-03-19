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
    // Visual Form Builder
    // ==============================================

    var $sectionsList = document.getElementById('cvs-sections-list');
    var saveTimeout = null;

    // Initialize SortableJS if builder exists on page
    if ($sectionsList && typeof Sortable !== 'undefined') {
        // Section-level sortable — only non-core sections can be dragged
        new Sortable($sectionsList, {
            handle: '.cvs-section-drag',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            filter: '.cvs-section-core',
            onEnd: function() {
                saveFormLayout();
            }
        });

        // Field-level sortable for each section (including core — custom fields can be added)
        $sectionsList.querySelectorAll('.cvs-fields-list').forEach(function(list) {
            new Sortable(list, {
                group: 'fields',
                handle: '.cvs-field-drag',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function() {
                    updateEmptyStates();
                    updateFieldCounts();
                    saveFormLayout();
                }
            });
        });
    }

    // Show status message
    function showBuilderStatus(text, type) {
        var $status = $('#cvs-builder-status');
        $status.find('.cvs-status-text').text(text);
        $status.removeClass('saving saved error').addClass(type).show();

        if (type === 'saved') {
            setTimeout(function() { $status.fadeOut(); }, 2000);
        }
    }

    // Update empty state visibility for all sections
    function updateEmptyStates() {
        $('.cvs-fields-list').each(function() {
            var $list = $(this);
            var hasFields = $list.find('.cvs-field-item').length > 0;
            $list.find('.cvs-fields-empty-state').toggle(!hasFields);
        });
    }

    // Update field counts in section headers
    function updateFieldCounts() {
        $('.cvs-section-card').each(function() {
            var count = $(this).find('.cvs-field-item').length;
            var text = count === 1 ? '1 field' : count + ' fields';
            $(this).find('.cvs-section-field-count').text(text);
        });
    }

    // Save entire layout via AJAX (debounced)
    function saveFormLayout() {
        if (saveTimeout) clearTimeout(saveTimeout);

        saveTimeout = setTimeout(function() {
            var layout = { sections: [], fields: {} };

            document.querySelectorAll('.cvs-section-card').forEach(function(card, idx) {
                var sectionId = card.dataset.sectionId;
                layout.sections.push({ id: sectionId, sort_order: (idx + 1) * 10 });
                layout.fields[sectionId] = [];

                card.querySelectorAll('.cvs-field-item').forEach(function(item, fIdx) {
                    layout.fields[sectionId].push({
                        id: item.dataset.fieldId,
                        sort_order: (fIdx + 1) * 10
                    });
                });
            });

            showBuilderStatus('Saving layout...', 'saving');

            $.ajax({
                url: cvs_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'cvs_save_form_layout',
                    nonce: cvs_admin.nonce,
                    layout: JSON.stringify(layout)
                },
                success: function(response) {
                    if (response.success) {
                        showBuilderStatus('Layout saved', 'saved');
                    } else {
                        showBuilderStatus('Error saving layout', 'error');
                    }
                },
                error: function() {
                    showBuilderStatus('Error saving layout', 'error');
                }
            });
        }, 500);
    }

    // Initialize sortable on a new field list (after adding section)
    function initFieldSortable(listEl) {
        if (typeof Sortable !== 'undefined') {
            new Sortable(listEl, {
                group: 'fields',
                handle: '.cvs-field-drag',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function() {
                    updateEmptyStates();
                    updateFieldCounts();
                    saveFormLayout();
                }
            });
        }
    }

    // ---- Modal Helpers ----
    function openModal(modalId) {
        $('#' + modalId).show();
    }

    function closeModal(modalId) {
        $('#' + modalId).hide();
    }

    // Close modals via overlay click, X button, or Cancel
    $(document).on('click', '.cvs-modal-overlay, .cvs-modal-close, .cvs-modal-cancel', function() {
        $(this).closest('.cvs-modal').hide();
    });

    // ---- Section CRUD ----

    // Add Section button
    $('#cvs-add-section-btn').on('click', function() {
        $('#cvs-section-edit-id').val('');
        $('#cvs-section-label').val('');
        $('#cvs-section-description').val('');
        $('#cvs-section-modal-title').text('Add Section');
        $('#cvs-section-submit-btn').text('Add Section');
        openModal('cvs-section-modal');
    });

    // Edit Section button
    $(document).on('click', '.cvs-edit-section', function() {
        var $btn = $(this);
        $('#cvs-section-edit-id').val($btn.data('section-id'));
        $('#cvs-section-label').val($btn.data('label'));
        $('#cvs-section-description').val($btn.data('description') || '');
        $('#cvs-section-modal-title').text('Edit Section');
        $('#cvs-section-submit-btn').text('Update Section');
        openModal('cvs-section-modal');
    });

    // Section form submit
    $('#cvs-section-form').on('submit', function(e) {
        e.preventDefault();

        var editId = $('#cvs-section-edit-id').val();
        var label = $('#cvs-section-label').val().trim();
        if (!label) { alert('Please enter a section name.'); return; }

        var $btn = $('#cvs-section-submit-btn');
        $btn.prop('disabled', true);

        var data = {
            action: editId ? 'cvs_update_section' : 'cvs_add_section',
            nonce: cvs_admin.nonce,
            label: label,
            description: $('#cvs-section-description').val()
        };

        if (editId) {
            data.section_id = editId;
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
                $btn.prop('disabled', false);
            }
        });
    });

    // Delete Section
    $(document).on('click', '.cvs-delete-section', function() {
        if (!confirm('Are you sure you want to delete this section? It must be empty (no fields).')) {
            return;
        }

        var $btn = $(this);
        var sectionId = $btn.data('section-id');
        $btn.prop('disabled', true);

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_delete_section',
                nonce: cvs_admin.nonce,
                section_id: sectionId
            },
            success: function(response) {
                if (response.success) {
                    $btn.closest('.cvs-section-card').fadeOut(function() {
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
                $btn.prop('disabled', false);
            }
        });
    });

    // ---- Field CRUD ----

    // Sync type grid radio buttons with hidden select
    function setFieldType(type) {
        $('#cvs-field-type').val(type);
        $('input[name="cvs-field-type-radio"][value="' + type + '"]').prop('checked', true);
        // Show/hide options group
        $('#cvs-field-options-group').toggle(type === 'select');
        // Update preview
        updateFieldPreview();
    }

    // Type grid click handler
    $(document).on('change', 'input[name="cvs-field-type-radio"]', function() {
        setFieldType($(this).val());
    });

    // Legacy hidden select change (backward compat)
    $('#cvs-field-type').on('change', function() {
        var type = $(this).val();
        $('input[name="cvs-field-type-radio"][value="' + type + '"]').prop('checked', true);
        $('#cvs-field-options-group').toggle(type === 'select');
        updateFieldPreview();
    });

    // ---- Live Preview ----
    function updateFieldPreview() {
        var label = $('#cvs-field-label').val() || 'Field Label';
        var type = $('#cvs-field-type').val() || 'text';
        var placeholder = $('#cvs-field-placeholder').val() || '';
        var required = $('#cvs-field-required').is(':checked');
        var options = $('#cvs-field-options').val() || '';

        // Update label
        $('#cvs-preview-label-text').text(label);
        $('#cvs-preview-required').toggle(required);

        // Update input type
        var $wrap = $('#cvs-preview-input-wrap');
        var html = '';

        switch (type) {
            case 'textarea':
                html = '<textarea class="cvs-preview-textarea" disabled placeholder="' + $('<span>').text(placeholder).html() + '"></textarea>';
                break;
            case 'select':
                html = '<select class="cvs-preview-select" disabled>';
                html += '<option>' + (placeholder || 'Select...') + '</option>';
                if (options) {
                    options.split('\n').forEach(function(opt) {
                        opt = opt.trim();
                        if (opt) {
                            var parts = opt.split('|');
                            var optLabel = parts.length > 1 ? parts[1].trim() : opt;
                            html += '<option>' + $('<span>').text(optLabel).html() + '</option>';
                        }
                    });
                }
                html += '</select>';
                break;
            case 'checkbox':
                html = '<div class="cvs-preview-checkbox-wrap"><input type="checkbox" disabled> <span>' + $('<span>').text(label).html() + '</span></div>';
                break;
            case 'number':
                html = '<input type="number" class="cvs-preview-input" disabled placeholder="' + $('<span>').text(placeholder || '0').html() + '">';
                break;
            case 'text':
            default:
                html = '<input type="text" class="cvs-preview-input" disabled placeholder="' + $('<span>').text(placeholder).html() + '">';
                break;
        }

        $wrap.html(html);
    }

    // Bind preview updates to field inputs
    $('#cvs-field-label, #cvs-field-placeholder, #cvs-field-options').on('input', updateFieldPreview);
    $('#cvs-field-required').on('change', updateFieldPreview);

    // Add Field button (in section footer)
    $(document).on('click', '.cvs-add-field-btn', function() {
        var sectionId = $(this).data('section');
        $('#cvs-field-edit-id').val('');
        $('#cvs-field-section').val(sectionId);
        $('#cvs-field-type-key').val('');
        $('#cvs-field-label').val('');
        setFieldType('text');
        $('#cvs-field-required').prop('checked', false);
        $('#cvs-field-placeholder').val('');
        $('#cvs-field-max-length').val('255');
        $('#cvs-field-options').val('');
        $('#cvs-field-section-select').val(sectionId);
        $('#cvs-field-type-row').show();
        $('#cvs-field-modal-title').text('Add Field');
        $('#cvs-field-submit-btn').text('Add Field');
        updateFieldPreview();
        openModal('cvs-field-modal');
    });

    // Edit Field button
    $(document).on('click', '.cvs-edit-field', function() {
        var $btn = $(this);
        var isBuiltin = $btn.data('field-type-key') === 'builtin_optional';

        $('#cvs-field-edit-id').val($btn.data('field-id'));
        $('#cvs-field-section').val($btn.data('section'));
        $('#cvs-field-type-key').val($btn.data('field-type-key'));
        $('#cvs-field-label').val($btn.data('label'));
        setFieldType($btn.data('field-type'));
        $('#cvs-field-required').prop('checked', $btn.data('required') == 1);
        $('#cvs-field-placeholder').val($btn.data('placeholder'));
        $('#cvs-field-max-length').val($btn.data('max-length'));
        $('#cvs-field-options').val($btn.data('options') || '');
        $('#cvs-field-section-select').val($btn.data('section'));

        // Builtin fields cannot change type
        if (isBuiltin) {
            $('#cvs-field-type-row').hide();
        } else {
            $('#cvs-field-type-row').show();
        }

        $('#cvs-field-modal-title').text('Edit Field');
        $('#cvs-field-submit-btn').text('Update Field');
        updateFieldPreview();
        openModal('cvs-field-modal');
    });

    // Field form submit
    $('#cvs-field-form').on('submit', function(e) {
        e.preventDefault();

        var editId = $('#cvs-field-edit-id').val();
        var fieldTypeKey = $('#cvs-field-type-key').val();
        var label = $('#cvs-field-label').val().trim();
        if (!label) { alert('Please enter a field label.'); return; }

        var $btn = $('#cvs-field-submit-btn');
        $btn.prop('disabled', true);

        // For builtin fields being edited, use toggle/update handler
        var isBuiltin = fieldTypeKey === 'builtin_optional';
        var action = editId ? 'cvs_update_custom_field' : 'cvs_add_custom_field';

        // Builtin fields use the same update handler
        if (isBuiltin && editId) {
            action = 'cvs_update_custom_field';
        }

        var data = {
            action: action,
            nonce: cvs_admin.nonce,
            label: label,
            field_type: $('#cvs-field-type').val(),
            required: $('#cvs-field-required').is(':checked') ? '1' : '0',
            placeholder: $('#cvs-field-placeholder').val(),
            max_length: $('#cvs-field-max-length').val(),
            options: $('#cvs-field-options').val(),
            section: $('#cvs-field-section-select').val()
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
                $btn.prop('disabled', false);
            }
        });
    });

    // Delete Field
    $(document).on('click', '.cvs-delete-field', function() {
        if (!confirm(cvs_admin.strings.confirm_delete)) {
            return;
        }

        var $btn = $(this);
        var fieldId = $btn.data('field-id');
        $btn.prop('disabled', true);

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
                    $btn.closest('.cvs-field-item').fadeOut(function() {
                        $(this).remove();
                        updateEmptyStates();
                        updateFieldCounts();
                    });
                } else {
                    alert(response.data || cvs_admin.strings.error);
                }
            },
            error: function() {
                alert(cvs_admin.strings.error);
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    // Toggle field enabled/disabled
    $(document).on('change', '.cvs-toggle-field-enabled', function() {
        var $checkbox = $(this);
        var fieldId = $checkbox.data('field-id');
        var fieldTypeKey = $checkbox.data('field-type-key');
        var enabled = $checkbox.is(':checked') ? '1' : '0';
        var $fieldItem = $checkbox.closest('.cvs-field-item');

        // Use the right handler based on field type
        var action = fieldTypeKey === 'builtin_optional' ? 'cvs_toggle_builtin_field' : 'cvs_update_custom_field';

        var data = {
            action: action,
            nonce: cvs_admin.nonce,
            field_id: fieldId,
            enabled: enabled
        };

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    $fieldItem.toggleClass('cvs-field-disabled', enabled === '0');
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

})(jQuery);
