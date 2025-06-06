<?php
/**
 * Helper functions for interacting with the Stripe API
 *
 * This file contains standalone helper functions for interacting with the Stripe API.
 * These functions are used by the Mifeco_Stripe class and other parts of the plugin.
 *
 * @link       https://mifeco.com
 * @since      1.0.0
 *
 * @package    Mifeco_Suite
 * @subpackage Mifeco_Suite/includes/stripe
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Initialize Stripe API with the configured keys
 *
 * @return bool Whether initialization was successful
 */
function mifeco_init_stripe_api() {
    // Check if Stripe library exists
    if (!class_exists('\Stripe\Stripe') && file_exists(plugin_dir_path(dirname(dirname(__FILE__))) . 'vendor/autoload.php')) {
        require_once plugin_dir_path(dirname(dirname(__FILE__))) . 'vendor/autoload.php';
    }
    
    if (!class_exists('\Stripe\Stripe')) {
        error_log('Stripe PHP library not found');
        return false;
    }
    
    // Get API key based on test mode setting
    $test_mode = (bool) get_option('mifeco_stripe_test_mode', true);
    $secret_key = get_option('mifeco_stripe_secret_key', '');
    
    if (empty($secret_key)) {
        error_log('Stripe secret key not configured');
        return false;
    }
    
    try {
        // Set API key
        \Stripe\Stripe::setApiKey($secret_key);
        
        // Set app info
        \Stripe\Stripe::setAppInfo(
            'MIFECO Suite',
            MIFECO_SUITE_VERSION,
            'https://mifeco.com',
            'pp_partner_JQWbOZgIRpU6ZN'
        );
        
        return true;
    } catch (\Exception $e) {
        error_log('Error initializing Stripe API: ' . $e->getMessage());
        return false;
    }
}

/**
 * Create a Stripe customer for a WordPress user
 *
 * @param int $user_id WordPress user ID
 * @return string|bool Stripe customer ID or false on failure
 */
function mifeco_create_stripe_customer($user_id) {
    // Initialize Stripe API
    if (!mifeco_init_stripe_api()) {
        return false;
    }
    
    // Get user data
    $user = get_userdata($user_id);
    if (!$user) {
        error_log('User not found: ' . $user_id);
        return false;
    }
    
    // Check if customer already exists
    $customer_id = get_user_meta($user_id, 'mifeco_stripe_customer_id', true);
    if (!empty($customer_id)) {
        return $customer_id;
    }
    
    try {
        // Create customer
        $customer = \Stripe\Customer::create([
            'email' => $user->user_email,
            'name' => $user->display_name,
            'metadata' => [
                'wordpress_user_id' => $user_id,
            ],
        ]);
        
        // Save customer ID to user meta
        update_user_meta($user_id, 'mifeco_stripe_customer_id', $customer->id);
        
        return $customer->id;
    } catch (\Exception $e) {
        error_log('Error creating Stripe customer: ' . $e->getMessage());
        return false;
    }
}

/**
 * Create a Stripe checkout session for subscription
 *
 * @param int $user_id WordPress user ID
 * @param string $plan_id Subscription plan ID
 * @return array|bool Checkout session data or false on failure
 */
function mifeco_create_checkout_session($user_id, $plan_id) {
    // Initialize Stripe API
    if (!mifeco_init_stripe_api()) {
        return false;
    }
    
    // Get price ID based on plan
    $price_id = '';
    switch ($plan_id) {
        case 'basic':
            $price_id = get_option('mifeco_stripe_basic_price_id', '');
            break;
        case 'premium':
            $price_id = get_option('mifeco_stripe_premium_price_id', '');
            break;
        case 'enterprise':
            $price_id = get_option('mifeco_stripe_enterprise_price_id', '');
            break;
    }
    
    if (empty($price_id)) {
        error_log('No price ID configured for plan: ' . $plan_id);
        return false;
    }
    
    // Get or create Stripe customer
    $customer_id = mifeco_create_stripe_customer($user_id);
    if (!$customer_id) {
        error_log('Failed to create or retrieve Stripe customer for user: ' . $user_id);
        return false;
    }
    
    try {
        // Create checkout session
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $price_id,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'subscription_data' => [
                'metadata' => [
                    'wordpress_user_id' => $user_id,
                    'plan' => $plan_id,
                ],
            ],
            'customer' => $customer_id,
            'success_url' => site_url('/my-account/?subscription=success'),
            'cancel_url' => site_url('/subscriptions/?subscription=canceled'),
            'metadata' => [
                'wordpress_user_id' => $user_id,
                'plan' => $plan_id,
            ],
        ]);
        
        return [
            'session_id' => $checkout_session->id,
            'url' => $checkout_session->url,
        ];
    } catch (\Exception $e) {
        error_log('Error creating checkout session: ' . $e->getMessage());
        return false;
    }
}

/**
 * Create a Stripe customer portal session
 *
 * @param int $user_id WordPress user ID
 * @return string|bool Portal URL or false on failure
 */
function mifeco_create_customer_portal_session($user_id) {
    // Initialize Stripe API
    if (!mifeco_init_stripe_api()) {
        return false;
    }
    
    // Get Stripe customer ID
    $customer_id = get_user_meta($user_id, 'mifeco_stripe_customer_id', true);
    if (empty($customer_id)) {
        error_log('No Stripe customer ID found for user: ' . $user_id);
        return false;
    }
    
    try {
        // Create portal session
        $session = \Stripe\BillingPortal\Session::create([
            'customer' => $customer_id,
            'return_url' => site_url('/my-account/'),
        ]);
        
        return $session->url;
    } catch (\Exception $e) {
        error_log('Error creating customer portal session: ' . $e->getMessage());
        return false;
    }
}

/**
 * Cancel a user's subscription
 *
 * @param int $user_id WordPress user ID
 * @param bool $at_period_end Whether to cancel at period end or immediately
 * @return bool Success status
 */
function mifeco_cancel_subscription($user_id, $at_period_end = true) {
    // Initialize Stripe API
    if (!mifeco_init_stripe_api()) {
        return false;
    }
    
    // Get subscription ID
    $subscription_id = get_user_meta($user_id, 'mifeco_stripe_subscription_id', true);
    if (empty($subscription_id)) {
        error_log('No subscription ID found for user: ' . $user_id);
        return false;
    }
    
    try {
        // Retrieve subscription
        $subscription = \Stripe\Subscription::retrieve($subscription_id);
        
        if ($at_period_end) {
            // Cancel at period end
            $subscription->cancel_at_period_end = true;
            $subscription->save();
            
            // Update user meta
            update_user_meta($user_id, 'mifeco_subscription_status', 'cancelled');
            
            // Send cancellation email with end date
            $expiry_date = date('Y-m-d', $subscription->current_period_end);
            mifeco_send_subscription_cancelled_email($user_id, $expiry_date);
        } else {
            // Cancel immediately
            $subscription->cancel();
            
            // Update user meta
            update_user_meta($user_id, 'mifeco_subscription_status', 'cancelled');
            
            // Send immediate cancellation email
            mifeco_send_subscription_cancelled_email($user_id);
            
            // Remove user roles
            $user = new WP_User($user_id);
            $user->remove_role('mifeco_saas_basic');
            $user->remove_role('mifeco_saas_premium');
            $user->remove_role('mifeco_saas_enterprise');
        }
        
        return true;
    } catch (\Exception $e) {
        error_log('Error cancelling subscription: ' . $e->getMessage());
        return false;
    }
}

/**
 * Change a user's subscription plan
 *
 * @param int $user_id WordPress user ID
 * @param string $new_plan_id New subscription plan ID
 * @return bool Success status
 */
function mifeco_change_subscription_plan($user_id, $new_plan_id) {
    // Initialize Stripe API
    if (!mifeco_init_stripe_api()) {
        return false;
    }
    
    // Get subscription ID
    $subscription_id = get_user_meta($user_id, 'mifeco_stripe_subscription_id', true);
    if (empty($subscription_id)) {
        error_log('No subscription ID found for user: ' . $user_id);
        return false;
    }
    
    // Get price ID based on plan
    $price_id = '';
    switch ($new_plan_id) {
        case 'basic':
            $price_id = get_option('mifeco_stripe_basic_price_id', '');
            break;
        case 'premium':
            $price_id = get_option('mifeco_stripe_premium_price_id', '');
            break;
        case 'enterprise':
            $price_id = get_option('mifeco_stripe_enterprise_price_id', '');
            break;
    }
    
    if (empty($price_id)) {
        error_log('No price ID configured for plan: ' . $new_plan_id);
        return false;
    }
    
    try {
        // Retrieve subscription
        $subscription = \Stripe\Subscription::retrieve($subscription_id);
        
        // Update subscription with new price
        \Stripe\Subscription::update($subscription_id, [
            'cancel_at_period_end' => false,
            'proration_behavior' => 'create_prorations',
            'items' => [
                [
                    'id' => $subscription->items->data[0]->id,
                    'price' => $price_id,
                ],
            ],
            'metadata' => [
                'plan' => $new_plan_id,
            ],
        ]);
        
        // Update user meta
        update_user_meta($user_id, 'mifeco_subscription_plan', $new_plan_id);
        update_user_meta($user_id, 'mifeco_subscription_status', 'active');
        
        // Update user role
        $user = new WP_User($user_id);
        
        // Remove old roles first
        $user->remove_role('mifeco_saas_basic');
        $user->remove_role('mifeco_saas_premium');
        $user->remove_role('mifeco_saas_enterprise');
        
        // Add new role based on plan
        switch ($new_plan_id) {
            case 'basic':
                $user->add_role('mifeco_saas_basic');
                break;
            case 'premium':
                $user->add_role('mifeco_saas_premium');
                break;
            case 'enterprise':
                $user->add_role('mifeco_saas_enterprise');
                break;
        }
        
        return true;
    } catch (\Exception $e) {
        error_log('Error changing subscription plan: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get subscription details for a user
 *
 * @param int $user_id WordPress user ID
 * @return array|bool Subscription details or false on failure
 */
function mifeco_get_subscription_details($user_id) {
    // Initialize Stripe API
    if (!mifeco_init_stripe_api()) {
        return false;
    }
    
    // Get subscription ID
    $subscription_id = get_user_meta($user_id, 'mifeco_stripe_subscription_id', true);
    if (empty($subscription_id)) {
        return false;
    }
    
    try {
        // Retrieve subscription
        $subscription = \Stripe\Subscription::retrieve([
            'id' => $subscription_id,
            'expand' => ['latest_invoice', 'customer'],
        ]);
        
        // Format response
        $details = [
            'id' => $subscription->id,
            'status' => $subscription->status,
            'current_period_start' => date('Y-m-d', $subscription->current_period_start),
            'current_period_end' => date('Y-m-d', $subscription->current_period_end),
            'cancel_at_period_end' => $subscription->cancel_at_period_end,
            'plan' => [
                'id' => $subscription->items->data[0]->price->id,
                'name' => isset($subscription->items->data[0]->price->nickname) ? $subscription->items->data[0]->price->nickname : '',
                'amount' => $subscription->items->data[0]->price->unit_amount / 100,
                'currency' => $subscription->items->data[0]->price->currency,
                'interval' => $subscription->items->data[0]->price->recurring->interval,
                'interval_count' => $subscription->items->data[0]->price->recurring->interval_count,
            ],
            'customer' => [
                'id' => $subscription->customer->id,
                'name' => $subscription->customer->name,
                'email' => $subscription->customer->email,
            ],
        ];
        
        // Add latest invoice if available
        if (isset($subscription->latest_invoice) && $subscription->latest_invoice) {
            $details['latest_invoice'] = [
                'id' => $subscription->latest_invoice->id,
                'number' => $subscription->latest_invoice->number,
                'amount_paid' => $subscription->latest_invoice->amount_paid / 100,
                'currency' => $subscription->latest_invoice->currency,
                'status' => $subscription->latest_invoice->status,
                'created' => date('Y-m-d', $subscription->latest_invoice->created),
                'hosted_invoice_url' => $subscription->latest_invoice->hosted_invoice_url,
            ];
        }
        
        return $details;
    } catch (\Exception $e) {
        error_log('Error retrieving subscription details: ' . $e->getMessage());
        return false;
    }
}

/**
 * Create or update a Stripe webhook endpoint
 *
 * @return array|bool Result array or false on failure
 */
function mifeco_setup_stripe_webhook() {
    // Initialize Stripe API
    if (!mifeco_init_stripe_api()) {
        return false;
    }
    
    try {
        // Get all webhooks
        $webhooks = \Stripe\WebhookEndpoint::all(['limit' => 100]);
        $webhook_url = get_rest_url(null, 'mifeco/v1/stripe-webhook');
        $existing_webhook = null;
        
        // Check if webhook already exists
        foreach ($webhooks->data as $webhook) {
            if ($webhook->url === $webhook_url) {
                $existing_webhook = $webhook;
                break;
            }
        }
        
        if ($existing_webhook) {
            // Update existing webhook
            $webhook = \Stripe\WebhookEndpoint::update($existing_webhook->id, [
                'enabled_events' => [
                    'checkout.session.completed',
                    'customer.subscription.created',
                    'customer.subscription.updated',
                    'customer.subscription.deleted',
                    'invoice.payment_succeeded',
                    'invoice.payment_failed',
                ],
            ]);
            
            $message = __('Webhook endpoint updated successfully.', 'mifeco-suite');
        } else {
            // Create new webhook
            $webhook = \Stripe\WebhookEndpoint::create([
                'url' => $webhook_url,
                'enabled_events' => [
                    'checkout.session.completed',
                    'customer.subscription.created',
                    'customer.subscription.updated',
                    'customer.subscription.deleted',
                    'invoice.payment_succeeded',
                    'invoice.payment_failed',
                ],
                'description' => 'MIFECO Suite WordPress plugin webhook',
            ]);
            
            $message = __('Webhook endpoint created successfully.', 'mifeco-suite');
        }
        
        // Return result
        return [
            'success' => true,
            'message' => $message,
            'webhook_id' => $webhook->id,
            'webhook_secret' => $webhook->secret,
            'webhook_url' => $webhook->url,
        ];
    } catch (\Exception $e) {
        error_log('Error setting up Stripe webhook: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => __('Error: ', 'mifeco-suite') . $e->getMessage(),
        ];
    }
}