/**
 * All of the code for your admin-facing JavaScript source
 * should reside in this file.
 */
(function( $ ) {
	'use strict';

	/**
	 * Initialize lead management features
	 */
	function initLeadManagement() {
		// Handle lead status changes
		$('.mifeco-admin-container').on('change', 'select[name="lead_status"]', function() {
			const $form = $(this).closest('form');
			if ($form.length) {
				$form.submit();
			}
		});

		// Handle CRM sync form submissions
		$('#mifeco-crm-sync-form').on('submit', function(e) {
			e.preventDefault();
			
			const $form = $(this);
			const leadId = $form.find('input[name="lead_id"]').val();
			const crmSystem = $form.find('#crm-system').val();
			const crmStage = $form.find('#crm-stage').val();
			
			$.ajax({
				type: 'POST',
				url: mifeco_admin_ajax.ajax_url,
				data: {
					action: 'mifeco_sync_to_crm',
					nonce: mifeco_admin_ajax.nonce,
					lead_id: leadId,
					crm_system: crmSystem,
					crm_stage: crmStage
				},
				beforeSend: function() {
					$form.find('button[type="submit"]').prop('disabled', true).text('Syncing...');
				},
				success: function(response) {
					if (response.success) {
						alert(response.data.message);
						window.location.reload();
					} else {
						alert(response.data.message || 'Error syncing to CRM.');
						$form.find('button[type="submit"]').prop('disabled', false).text('Sync to CRM');
					}
				},
				error: function() {
					alert('Error connecting to the server. Please try again.');
					$form.find('button[type="submit"]').prop('disabled', false).text('Sync to CRM');
				}
			});
		});

		// Handle email form submissions
		$('#mifeco-email-form').on('submit', function(e) {
			e.preventDefault();
			
			const $form = $(this);
			const leadId = $form.find('input[name="lead_id"]').val();
			const subject = $form.find('#email-subject').val();
			const message = $form.find('#email-body').val();
			
			if (!subject || !message) {
				alert('Please enter both subject and message.');
				return;
			}
			
			$.ajax({
				type: 'POST',
				url: mifeco_admin_ajax.ajax_url,
				data: {
					action: 'mifeco_send_lead_email',
					nonce: mifeco_admin_ajax.nonce,
					lead_id: leadId,
					subject: subject,
					message: message
				},
				beforeSend: function() {
					$form.find('button[type="submit"]').prop('disabled', true).text('Sending...');
				},
				success: function(response) {
					if (response.success) {
						document.getElementById('mifeco-send-email').style.display = 'none';
						alert(response.data.message);
						window.location.reload();
					} else {
						alert(response.data.message || 'Error sending email.');
						$form.find('button[type="submit"]').prop('disabled', false).text('Send Email');
					}
				},
				error: function() {
					alert('Error connecting to the server. Please try again.');
					$form.find('button[type="submit"]').prop('disabled', false).text('Send Email');
				}
			});
		});

		// Handle SMS form submissions
		$('#mifeco-sms-form').on('submit', function(e) {
			e.preventDefault();
			
			const $form = $(this);
			const leadId = $form.find('input[name="lead_id"]').val();
			const message = $form.find('#sms-message').val();
			
			if (!message) {
				alert('Please enter a message.');
				return;
			}
			
			if (message.length > 160) {
				if (!confirm('Your message exceeds the standard SMS length of 160 characters and may be split into multiple messages. Continue?')) {
					return;
				}
			}
			
			$.ajax({
				type: 'POST',
				url: mifeco_admin_ajax.ajax_url,
				data: {
					action: 'mifeco_send_lead_sms',
					nonce: mifeco_admin_ajax.nonce,
					lead_id: leadId,
					message: message
				},
				beforeSend: function() {
					$form.find('button[type="submit"]').prop('disabled', true).text('Sending...');
				},
				success: function(response) {
					if (response.success) {
						document.getElementById('mifeco-send-sms').style.display = 'none';
						alert(response.data.message);
						window.location.reload();
					} else {
						alert(response.data.message || 'Error sending SMS.');
						$form.find('button[type="submit"]').prop('disabled', false).text('Send SMS');
					}
				},
				error: function() {
					alert('Error connecting to the server. Please try again.');
					$form.find('button[type="submit"]').prop('disabled', false).text('Send SMS');
				}
			});
		});

		// Initialize template selectors
		$('#email-template').on('change', function() {
			const template = $(this).val();
			if (!template) return;
			
			// Get template content via AJAX
			$.ajax({
				type: 'POST',
				url: mifeco_admin_ajax.ajax_url,
				data: {
					action: 'mifeco_get_email_template',
					nonce: mifeco_admin_ajax.nonce,
					template: template,
					lead_id: $('#mifeco-email-form input[name="lead_id"]').val()
				},
				success: function(response) {
					if (response.success) {
						$('#email-subject').val(response.data.subject);
						$('#email-body').val(response.data.body);
					}
				}
			});
		});

		$('#sms-template').on('change', function() {
			const template = $(this).val();
			if (!template) return;
			
			// Get template content via AJAX
			$.ajax({
				type: 'POST',
				url: mifeco_admin_ajax.ajax_url,
				data: {
					action: 'mifeco_get_sms_template',
					nonce: mifeco_admin_ajax.nonce,
					template: template,
					lead_id: $('#mifeco-sms-form input[name="lead_id"]').val()
				},
				success: function(response) {
					if (response.success) {
						$('#sms-message').val(response.data.message);
						updateSmsCharCount();
					}
				}
			});
		});

		// SMS character counter
		$('#sms-message').on('input', updateSmsCharCount);
	}

	/**
	 * Update SMS character count
	 */
	function updateSmsCharCount() {
		const count = $('#sms-message').val().length;
		$('#sms-char-count').text(count);
		
		if (count > 160) {
			$('#sms-char-count').css('color', 'red');
		} else {
			$('#sms-char-count').css('color', '');
		}
	}

	/**
	 * Initialize consultation calendar features
	 */
	function initConsultationCalendar() {
		// Handle consultation booking form
		$('#mifeco-consultation-form').on('submit', function(e) {
			e.preventDefault();
			
			const $form = $(this);
			const leadId = $form.find('input[name="lead_id"]').val();
			const serviceType = $form.find('#consultation-type').val();
			const date = $form.find('#consultation-date').val();
			const time = $form.find('#consultation-time').val();
			const notes = $form.find('#consultation-notes').val();
			
			if (!serviceType || !date || !time) {
				alert('Please fill out all required fields.');
				return;
			}
			
			$.ajax({
				type: 'POST',
				url: mifeco_admin_ajax.ajax_url,
				data: {
					action: 'mifeco_schedule_consultation',
					nonce: mifeco_admin_ajax.nonce,
					lead_id: leadId,
					service_type: serviceType,
					date: date,
					time: time,
					notes: notes
				},
				beforeSend: function() {
					$form.find('button[type="submit"]').prop('disabled', true).text('Scheduling...');
				},
				success: function(response) {
					if (response.success) {
						document.getElementById('mifeco-schedule-consultation').style.display = 'none';
						alert(response.data.message);
						
						// Redirect to the new consultation
						if (response.data.consultation_id) {
							window.location.href = `admin.php?page=mifeco-consultation-calendar&consultation_id=${response.data.consultation_id}`;
						} else {
							window.location.reload();
						}
					} else {
						alert(response.data.message || 'Error scheduling consultation.');
						$form.find('button[type="submit"]').prop('disabled', false).text('Schedule');
					}
				},
				error: function() {
					alert('Error connecting to the server. Please try again.');
					$form.find('button[type="submit"]').prop('disabled', false).text('Schedule');
				}
			});
		});

		// Handle reschedule form
		$('#mifeco-reschedule-form').on('submit', function(e) {
			e.preventDefault();
			
			const $form = $(this);
			const consultationId = $form.find('input[name="consultation_id"]').val();
			const date = $form.find('#reschedule-date').val();
			const time = $form.find('#reschedule-time').val();
			const reason = $form.find('#reschedule-reason').val();
			const notifyClient = $form.find('input[name="notify_client"]').is(':checked');
			
			if (!date || !time) {
				alert('Please select both date and time.');
				return;
			}
			
			$.ajax({
				type: 'POST',
				url: mifeco_admin_ajax.ajax_url,
				data: {
					action: 'mifeco_reschedule_consultation',
					nonce: mifeco_admin_ajax.nonce,
					consultation_id: consultationId,
					date: date,
					time: time,
					reason: reason,
					notify_client: notifyClient
				},
				beforeSend: function() {
					$form.find('button[type="submit"]').prop('disabled', true).text('Rescheduling...');
				},
				success: function(response) {
					if (response.success) {
						document.getElementById('mifeco-reschedule').style.display = 'none';
						alert(response.data.message);
						window.location.reload();
					} else {
						alert(response.data.message || 'Error rescheduling consultation.');
						$form.find('button[type="submit"]').prop('disabled', false).text('Reschedule');
					}
				},
				error: function() {
					alert('Error connecting to the server. Please try again.');
					$form.find('button[type="submit"]').prop('disabled', false).text('Reschedule');
				}
			});
		});

		// Handle follow-up consultation booking
		$('#mifeco-followup-form').on('submit', function(e) {
			e.preventDefault();
			
			const $form = $(this);
			const leadId = $form.find('input[name="lead_id"]').val();
			const serviceType = $form.find('#followup-type').val();
			const date = $form.find('#followup-date').val();
			const time = $form.find('#followup-time').val();
			const notes = $form.find('#followup-notes').val();
			const notifyClient = $form.find('input[name="notify_client"]').is(':checked');
			
			if (!serviceType || !date || !time) {
				alert('Please fill out all required fields.');
				return;
			}
			
			$.ajax({
				type: 'POST',
				url: mifeco_admin_ajax.ajax_url,
				data: {
					action: 'mifeco_schedule_followup_consultation',
					nonce: mifeco_admin_ajax.nonce,
					lead_id: leadId,
					service_type: serviceType,
					date: date,
					time: time,
					notes: notes,
					notify_client: notifyClient
				},
				beforeSend: function() {
					$form.find('button[type="submit"]').prop('disabled', true).text('Scheduling...');
				},
				success: function(response) {
					if (response.success) {
						document.getElementById('mifeco-schedule-followup').style.display = 'none';
						alert(response.data.message);
						
						// Redirect to the new consultation
						if (response.data.consultation_id) {
							window.location.href = `admin.php?page=mifeco-consultation-calendar&consultation_id=${response.data.consultation_id}`;
						} else {
							window.location.reload();
						}
					} else {
						alert(response.data.message || 'Error scheduling follow-up consultation.');
						$form.find('button[type="submit"]').prop('disabled', false).text('Schedule Follow-up');
					}
				},
				error: function() {
					alert('Error connecting to the server. Please try again.');
					$form.find('button[type="submit"]').prop('disabled', false).text('Schedule Follow-up');
				}
			});
		});
	}

	/**
	 * Initialize dashboard widgets
	 */
	function initDashboard() {
		// Statistics refresh
		$('.mifeco-dashboard-widget-refresh').on('click', function(e) {
			e.preventDefault();
			
			const $widget = $(this).closest('.mifeco-dashboard-widget');
			const widgetType = $widget.data('widget-type');
			
			if (!widgetType) return;
			
			$widget.addClass('loading');
			
			$.ajax({
				type: 'POST',
				url: mifeco_admin_ajax.ajax_url,
				data: {
					action: 'mifeco_refresh_dashboard_widget',
					nonce: mifeco_admin_ajax.nonce,
					widget_type: widgetType
				},
				success: function(response) {
					$widget.removeClass('loading');
					
					if (response.success) {
						$widget.find('.mifeco-widget-number').text(response.data.value);
						$widget.find('.mifeco-widget-trend').removeClass('positive negative neutral');
						
						if (response.data.trend > 0) {
							$widget.find('.mifeco-widget-trend')
								.addClass('positive')
								.html(`↑ ${Math.abs(response.data.trend)}% from last month`);
						} else if (response.data.trend < 0) {
							$widget.find('.mifeco-widget-trend')
								.addClass('negative')
								.html(`↓ ${Math.abs(response.data.trend)}% from last month`);
						} else {
							$widget.find('.mifeco-widget-trend')
								.addClass('neutral')
								.html(`No change from last month`);
						}
					}
				},
				error: function() {
					$widget.removeClass('loading');
				}
			});
		});
	}

	/**
	 * Document ready
	 */
	$(function() {
		// Initialize features based on current admin page
		const currentPage = window.location.search.match(/page=([^&]*)/);
		
		if (currentPage) {
			const page = currentPage[1];
			
			if (page === 'mifeco-lead-management') {
				initLeadManagement();
			} else if (page === 'mifeco-consultation-calendar') {
				initConsultationCalendar();
			} else if (page === 'mifeco-suite') {
				initDashboard();
			}
		}
		
		// Generic modal handling
		$('.mifeco-modal-close, .mifeco-modal-cancel').on('click', function() {
			$(this).closest('.mifeco-modal').hide();
		});
		
		// Global handling for modals
		$(window).on('click', function(e) {
			if ($(e.target).hasClass('mifeco-modal')) {
				$(e.target).hide();
			}
		});
	});

})( jQuery );