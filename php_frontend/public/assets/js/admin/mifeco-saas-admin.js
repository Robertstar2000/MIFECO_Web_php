/**
 * All of the JavaScript code for the SaaS admin functionality.
 *
 * @package    Mifeco_Suite
 * @subpackage Mifeco_Suite/admin/js
 */

(function($) {
    'use strict';

    // Product management functionality
    function initProductManagement() {
        // Check if we're on the product management page
        const productForm = document.querySelector('.mifeco-product-form');
        if (!productForm) return;
        
        // Add feature button functionality
        const addFeatureBtn = document.querySelector('.mifeco-add-feature-btn');
        if (addFeatureBtn) {
            addFeatureBtn.addEventListener('click', function() {
                const featuresList = document.querySelector('.mifeco-plan-features-list');
                
                const featureItem = document.createElement('div');
                featureItem.className = 'mifeco-plan-feature-item';
                
                const featureInput = document.createElement('input');
                featureInput.type = 'text';
                featureInput.name = 'product_features[]';
                featureInput.placeholder = 'Enter feature';
                
                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'button mifeco-remove-feature';
                removeButton.textContent = 'Remove';
                
                featureItem.appendChild(featureInput);
                featureItem.appendChild(removeButton);
                
                featuresList.appendChild(featureItem);
            });
        }
        
        // Remove feature button functionality
        $(document).on('click', '.mifeco-remove-feature', function() {
            $(this).parent('.mifeco-plan-feature-item').remove();
        });
    }
    
    // Subscription management functionality
    function initSubscriptionManagement() {
        // Check if we're on the subscription management page
        const subscriptionForm = document.querySelector('.mifeco-user-subscription-edit form');
        if (!subscriptionForm) return;
        
        // Handle form submission via AJAX
        subscriptionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(subscriptionForm);
            
            // Add loading state
            const submitButton = subscriptionForm.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Updating...';
            submitButton.disabled = true;
            
            // Make AJAX call
            $.ajax({
                url: subscriptionForm.action,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        const successMessage = document.createElement('div');
                        successMessage.className = 'notice notice-success is-dismissible';
                        successMessage.innerHTML = '<p>Subscription updated successfully.</p>';
                        
                        subscriptionForm.parentNode.insertBefore(successMessage, subscriptionForm);
                        
                        // Redirect after delay
                        setTimeout(function() {
                            window.location.href = ajaxurl.replace('admin-ajax.php', 'admin.php?page=mifeco_subscriptions');
                        }, 1500);
                    } else {
                        // Show error message
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'notice notice-error is-dismissible';
                        errorMessage.innerHTML = '<p>Error updating subscription: ' + (response.data || 'Unknown error') + '</p>';
                        
                        subscriptionForm.parentNode.insertBefore(errorMessage, subscriptionForm);
                        
                        // Reset button
                        submitButton.textContent = originalText;
                        submitButton.disabled = false;
                    }
                },
                error: function(xhr, status, error) {
                    // Show error message
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'notice notice-error is-dismissible';
                    errorMessage.innerHTML = '<p>Error updating subscription: ' + error + '</p>';
                    
                    subscriptionForm.parentNode.insertBefore(errorMessage, subscriptionForm);
                    
                    // Reset button
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                }
            });
        });
    }
    
    // Direct subscription updates from subscription list
    function initQuickSubscriptionUpdates() {
        // Check if we're on the subscription list page
        const subscriptionTable = document.querySelector('.mifeco-subscriptions-table');
        if (!subscriptionTable) return;
        
        // Add quick status update functionality
        $('.mifeco-update-status-btn').on('click', function() {
            const userId = $(this).data('user-id');
            const currentStatus = $(this).data('current-status');
            
            // Create status dropdown
            const statusOptions = [
                {value: 'active', label: 'Active'},
                {value: 'inactive', label: 'Inactive'},
                {value: 'pending', label: 'Pending'},
                {value: 'cancelled', label: 'Cancelled'}
            ];
            
            let optionsHtml = '';
            statusOptions.forEach(function(option) {
                const selected = option.value === currentStatus ? 'selected' : '';
                optionsHtml += `<option value="${option.value}" ${selected}>${option.label}</option>`;
            });
            
            const dropdown = `
                <select class="mifeco-status-select" data-user-id="${userId}">
                    ${optionsHtml}
                </select>
                <button class="button button-small mifeco-save-status" data-user-id="${userId}">Save</button>
                <button class="button button-small mifeco-cancel-status">Cancel</button>
            `;
            
            // Replace button with dropdown
            $(this).replaceWith(dropdown);
        });
        
        // Handle status save
        $(document).on('click', '.mifeco-save-status', function() {
            const userId = $(this).data('user-id');
            const statusSelect = $(this).siblings('.mifeco-status-select');
            const newStatus = statusSelect.val();
            
            // Make AJAX call to update status
            $.ajax({
                url: mifeco_saas_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'mifeco_update_subscription_status',
                    user_id: userId,
                    status: newStatus,
                    nonce: mifeco_saas_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page to show updated status
                        location.reload();
                    } else {
                        alert('Error updating status: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('Error updating status. Please try again.');
                }
            });
        });
        
        // Handle cancel button
        $(document).on('click', '.mifeco-cancel-status', function() {
            const userId = $(this).siblings('.mifeco-save-status').data('user-id');
            const currentStatus = $(this).siblings('.mifeco-status-select').find('option:selected').val();
            
            // Replace dropdown with original button
            const statusButton = `
                <button class="button button-small mifeco-update-status-btn" data-user-id="${userId}" data-current-status="${currentStatus}">
                    Update Status
                </button>
            `;
            
            $(this).parent().html(statusButton);
        });
    }
    
    // Subscription settings page functionality
    function initSubscriptionSettings() {
        // Check if we're on the subscription settings page
        const settingsForm = document.querySelector('form .mifeco-plan-settings-card');
        if (!settingsForm) return;
        
        // Toggle secret key visibility
        const secretKeyInput = document.getElementById('mifeco_stripe_secret_key');
        if (secretKeyInput) {
            const toggleButton = document.createElement('button');
            toggleButton.type = 'button';
            toggleButton.className = 'button';
            toggleButton.textContent = 'Show';
            toggleButton.style.marginLeft = '10px';
            
            toggleButton.addEventListener('click', function() {
                if (secretKeyInput.type === 'password') {
                    secretKeyInput.type = 'text';
                    toggleButton.textContent = 'Hide';
                } else {
                    secretKeyInput.type = 'password';
                    toggleButton.textContent = 'Show';
                }
            });
            
            secretKeyInput.parentNode.insertBefore(toggleButton, secretKeyInput.nextSibling);
        }
    }
    
    // Dashboard statistics functionality
    function initDashboardStats() {
        // Check if we're on the dashboard
        const statsCards = document.querySelector('.mifeco-saas-stats-cards');
        if (!statsCards) return;
        
        // In a real implementation, this would fetch data via AJAX and update charts
        console.log('Dashboard stats would be initialized here in a real implementation');
    }
    
    // Initialize everything on document ready
    $(document).ready(function() {
        // Initialize product management
        initProductManagement();
        
        // Initialize subscription management
        initSubscriptionManagement();
        
        // Initialize quick subscription updates
        initQuickSubscriptionUpdates();
        
        // Initialize subscription settings
        initSubscriptionSettings();
        
        // Initialize dashboard stats
        initDashboardStats();
    });

})(jQuery);