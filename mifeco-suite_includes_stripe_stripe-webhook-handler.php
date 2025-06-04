<?php
/**
 * Handler for Stripe webhooks
 *
 * This file contains standalone functions for handling Stripe webhook events.
 * These functions are called by the Mifeco_Stripe class when webhook events are received.
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
 * Process a successful checkout session completion
 *
 * @param object $session The Stripe Checkout Session object
 */
function mifeco_process_checkout_session($session) {
    // Log the event
    error_log('Processing checkout session: ' . $session->id);
    
    // Get WordPress user ID from session metadata
    $user_id = isset($session->metadata->wordpress_user_id) ? $session->metadata->wordpress_user_id : null;
    
    if (!$user_id) {
        error_log('No WordPress user ID found in Checkout Session');
        return;
    }
    
    // Get plan from session metadata
    $plan = isset($session->metadata->plan) ? $session->metadata->plan : null;
    
    if (!$plan) {
        error_log('No plan found in Checkout Session');
        return;
    }
    
    // Get subscription ID
    $subscription_id = $session->subscription;
    
    if ($subscription_id) {
        // Store subscription ID in user meta
        update_user_meta($user_id, 'mifeco_stripe_subscription_id', $subscription_id);
        
        // Set subscription status to active
        update_user_meta($user_id, 'mifeco_subscription_status', 'active');
        
        // Set subscription plan
        update_user_meta($user_id, 'mifeco_subscription_plan', $plan);
        
        // Update user role based on plan
        $user = new WP_User($user_id);
        
        // Remove old roles first
        $user->remove_role('mifeco_saas_basic');
        $user->remove_role('mifeco_saas_premium');
        $user->remove_role('mifeco_saas_enterprise');
        
        // Add new role based on plan
        switch ($plan) {
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
        
        // Send welcome email
        mifeco_send_subscription_welcome_email($user_id, $plan);
        
        // Log success
        error_log('User ' . $user_id . ' subscribed to ' . $plan . ' plan with subscription ID: ' . $subscription_id);
    }
}

/**
 * Process a subscription update
 *
 * @param object $subscription The Stripe Subscription object
 */
function mifeco_process_subscription_update($subscription) {
    // Log the event
    error_log('Processing subscription update: ' . $subscription->id);
    
    // Get WordPress user ID from subscription metadata
    $user_id = isset($subscription->metadata->wordpress_user_id) ? $subscription->metadata->wordpress_user_id : null;
    
    if (!$user_id) {
        // Try to find user by customer ID
        $customer_id = $subscription->customer;
        $user_id = mifeco_get_user_by_stripe_customer($customer_id);
    }
    
    if (!$user_id) {
        error_log('No WordPress user found for Subscription');
        return;
    }
    
    // Get plan from subscription metadata or items
    $plan = isset($subscription->metadata->plan) ? $subscription->metadata->plan : null;
    
    if (!$plan && isset($subscription->items->data[0]->price->id)) {
        // Lookup plan by price ID
        $price_id = $subscription->items->data[0]->price->id;
        $plan = mifeco_get_plan_by_price_id($price_id);
    }
    
    if (!$plan) {
        error_log('No plan found for Subscription');
        return;
    }
    
    // Update subscription details in user meta
    update_user_meta($user_id, 'mifeco_stripe_subscription_id', $subscription->id);
    
    // Update status based on subscription status
    $status = $subscription->status;
    switch ($status) {
        case 'active':
        case 'trialing':
            update_user_meta($user_id, 'mifeco_subscription_status', 'active');
            break;
        case 'past_due':
        case 'unpaid':
            update_user_meta($user_id, 'mifeco_subscription_status', 'pending');
            break;
        case 'canceled':
        case 'incomplete_expired':
            update_user_meta($user_id, 'mifeco_subscription_status', 'cancelled');
            break;
        case 'incomplete':
            update_user_meta($user_id, 'mifeco_subscription_status', 'pending');
            break;
    }
    
    // Update plan
    update_user_meta($user_id, 'mifeco_subscription_plan', $plan);
    
    // Calculate and store expiry date
    $current_period_end = $subscription->current_period_end;
    $expiry_date = date('Y-m-d', $current_period_end);
    update_user_meta($user_id, 'mifeco_subscription_expiry', $expiry_date);
    
    // Update user role based on plan and status
    if ($status === 'active' || $status === 'trialing') {
        $user = new WP_User($user_id);
        
        // Remove old roles first
        $user->remove_role('mifeco_saas_basic');
        $user->remove_role('mifeco_saas_premium');
        $user->remove_role('mifeco_saas_enterprise');
        
        // Add new role based on plan
        switch ($plan) {
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
    }
    
    // Log success
    error_log('User ' . $user_id . ' subscription updated to ' . $plan . ' plan with status: ' . $status);
}

/**
 * Process a subscription cancellation
 *
 * @param object $subscription The Stripe Subscription object
 */
function mifeco_process_subscription_deleted($subscription) {
    // Log the event
    error_log('Processing subscription cancellation: ' . $subscription->id);
    
    // Get WordPress user ID from subscription metadata
    $user_id = isset($subscription->metadata->wordpress_user_id) ? $subscription->metadata->wordpress_user_id : null;
    
    if (!$user_id) {
        // Try to find user by customer ID
        $customer_id = $subscription->customer;
        $user_id = mifeco_get_user_by_stripe_customer($customer_id);
    }
    
    if (!$user_id) {
        error_log('No WordPress user found for Subscription');
        return;
    }
    
    // Update subscription status to cancelled
    update_user_meta($user_id, 'mifeco_subscription_status', 'cancelled');
    
    // Check if subscription has an end date in the future (grace period)
    $current_period_end = $subscription->current_period_end;
    $now = time();
    
    if ($current_period_end > $now) {
        // Subscription will remain active until the end of the period
        // Keep the current role until then
        $expiry_date = date('Y-m-d', $current_period_end);
        update_user_meta($user_id, 'mifeco_subscription_expiry', $expiry_date);
        
        // Send cancellation email with end date
        mifeco_send_subscription_cancelled_email($user_id, $expiry_date);
    } else {
        // Subscription is cancelled immediately
        // Remove user roles
        $user = new WP_User($user_id);
        $user->remove_role('mifeco_saas_basic');
        $user->remove_role('mifeco_saas_premium');
        $user->remove_role('mifeco_saas_enterprise');
        
        // Send immediate cancellation email
        mifeco_send_subscription_cancelled_email($user_id);
    }
    
    // Log success
    error_log('User ' . $user_id . ' subscription cancelled');
}

/**
 * Process a successful invoice payment
 *
 * @param object $invoice The Stripe Invoice object
 */
function mifeco_process_invoice_payment_succeeded($invoice) {
    // Log the event
    error_log('Processing successful invoice payment: ' . $invoice->id);
    
    // Only handle subscription invoices
    if (!$invoice->subscription) {
        return;
    }
    
    // Get WordPress user ID by customer ID
    $customer_id = $invoice->customer;
    $user_id = mifeco_get_user_by_stripe_customer($customer_id);
    
    if (!$user_id) {
        error_log('No WordPress user found for Invoice');
        return;
    }
    
    // Get subscription from invoice
    try {
        // Ensure we're using the Stripe API
        if (!class_exists('\Stripe\Stripe')) {
            error_log('Stripe API not available');
            return;
        }
        
        // Get subscription details
        $subscription = \Stripe\Subscription::retrieve($invoice->subscription);
        
        // Update expiry date
        $current_period_end = $subscription->current_period_end;
        $expiry_date = date('Y-m-d', $current_period_end);
        update_user_meta($user_id, 'mifeco_subscription_expiry', $expiry_date);
        
        // Ensure status is active
        update_user_meta($user_id, 'mifeco_subscription_status', 'active');
        
        // Send renewal receipt email
        mifeco_send_subscription_renewal_email($user_id, $invoice->amount_paid / 100, $expiry_date);
        
        // Log success
        error_log('User ' . $user_id . ' subscription renewed until ' . $expiry_date);
    } catch (\Exception $e) {
        error_log('Error retrieving subscription: ' . $e->getMessage());
    }
}

/**
 * Process a failed invoice payment
 *
 * @param object $invoice The Stripe Invoice object
 */
function mifeco_process_invoice_payment_failed($invoice) {
    // Log the event
    error_log('Processing failed invoice payment: ' . $invoice->id);
    
    // Only handle subscription invoices
    if (!$invoice->subscription) {
        return;
    }
    
    // Get WordPress user ID by customer ID
    $customer_id = $invoice->customer;
    $user_id = mifeco_get_user_by_stripe_customer($customer_id);
    
    if (!$user_id) {
        error_log('No WordPress user found for Invoice');
        return;
    }
    
    // Update subscription status to pending
    update_user_meta($user_id, 'mifeco_subscription_status', 'pending');
    
    // Send payment failed email
    mifeco_send_payment_failed_email($user_id, $invoice->attempt_count);
    
    // Log failure
    error_log('User ' . $user_id . ' payment failed for invoice: ' . $invoice->id);
}

/**
 * Get WordPress user ID by Stripe customer ID
 *
 * @param string $customer_id The Stripe customer ID
 * @return int|bool User ID if found, false otherwise
 */
function mifeco_get_user_by_stripe_customer($customer_id) {
    global $wpdb;
    
    $user_id = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'mifeco_stripe_customer_id' AND meta_value = %s LIMIT 1",
        $customer_id
    ));
    
    return $user_id ? intval($user_id) : false;
}

/**
 * Get plan by Stripe price ID
 *
 * @param string $price_id The Stripe price ID
 * @return string Plan name or empty string if not found
 */
function mifeco_get_plan_by_price_id($price_id) {
    $basic_price_id = get_option('mifeco_stripe_basic_price_id', '');
    $premium_price_id = get_option('mifeco_stripe_premium_price_id', '');
    $enterprise_price_id = get_option('mifeco_stripe_enterprise_price_id', '');
    
    if ($price_id === $basic_price_id) {
        return 'basic';
    } elseif ($price_id === $premium_price_id) {
        return 'premium';
    } elseif ($price_id === $enterprise_price_id) {
        return 'enterprise';
    }
    
    return '';
}

/**
 * Send subscription welcome email
 *
 * @param int $user_id User ID
 * @param string $plan Subscription plan
 */
function mifeco_send_subscription_welcome_email($user_id, $plan) {
    $user = get_userdata($user_id);
    if (!$user) return;
    
    $to = $user->user_email;
    $subject = __('Welcome to Your MIFECO Subscription!', 'mifeco-suite');
    
    $plan_name = '';
    switch ($plan) {
        case 'basic':
            $plan_name = __('Basic Plan', 'mifeco-suite');
            break;
        case 'premium':
            $plan_name = __('Premium Plan', 'mifeco-suite');
            break;
        case 'enterprise':
            $plan_name = __('Enterprise Plan', 'mifeco-suite');
            break;
        default:
            $plan_name = ucfirst($plan);
    }
    
    $message = sprintf(
        __('Hello %s,', 'mifeco-suite'),
        $user->display_name
    ) . "\n\n";
    
    $message .= sprintf(
        __('Thank you for subscribing to the %s! Your subscription is now active.', 'mifeco-suite'),
        $plan_name
    ) . "\n\n";
    
    $message .= __('You now have access to the following products:', 'mifeco-suite') . "\n";
    
    switch ($plan) {
        case 'enterprise':
            $message .= "- " . __('Proposal Evaluation Tool', 'mifeco-suite') . "\n";
        case 'premium':
            $message .= "- " . __('Ultimate Business Problem Solver', 'mifeco-suite') . "\n";
        case 'basic':
            $message .= "- " . __('Advanced Research Tool', 'mifeco-suite') . "\n";
            break;
    }
    
    $message .= "\n" . sprintf(
        __('You can access these tools by logging into your account: %s', 'mifeco-suite'),
        site_url('/my-account/')
    ) . "\n\n";
    
    $message .= __('If you have any questions or need assistance, please contact our support team.', 'mifeco-suite') . "\n\n";
    $message .= __('Thank you for choosing MIFECO!', 'mifeco-suite') . "\n\n";
    $message .= __('Best regards,', 'mifeco-suite') . "\n";
    $message .= __('The MIFECO Team', 'mifeco-suite');
    
    wp_mail($to, $subject, $message);
}

/**
 * Send subscription renewal email
 *
 * @param int $user_id User ID
 * @param float $amount Amount paid
 * @param string $expiry_date Subscription expiry date
 */
function mifeco_send_subscription_renewal_email($user_id, $amount, $expiry_date) {
    $user = get_userdata($user_id);
    if (!$user) return;
    
    $to = $user->user_email;
    $subject = __('Your MIFECO Subscription Has Been Renewed', 'mifeco-suite');
    
    $plan = get_user_meta($user_id, 'mifeco_subscription_plan', true);
    $plan_name = '';
    switch ($plan) {
        case 'basic':
            $plan_name = __('Basic Plan', 'mifeco-suite');
            break;
        case 'premium':
            $plan_name = __('Premium Plan', 'mifeco-suite');
            break;
        case 'enterprise':
            $plan_name = __('Enterprise Plan', 'mifeco-suite');
            break;
        default:
            $plan_name = ucfirst($plan);
    }
    
    $message = sprintf(
        __('Hello %s,', 'mifeco-suite'),
        $user->display_name
    ) . "\n\n";
    
    $message .= sprintf(
        __('Your subscription to the %s has been renewed successfully.', 'mifeco-suite'),
        $plan_name
    ) . "\n\n";
    
    $message .= sprintf(
        __('Amount charged: $%s', 'mifeco-suite'),
        number_format($amount, 2)
    ) . "\n";
    
    $message .= sprintf(
        __('Next billing date: %s', 'mifeco-suite'),
        date_i18n(get_option('date_format'), strtotime($expiry_date))
    ) . "\n\n";
    
    $message .= sprintf(
        __('You can manage your subscription from your account: %s', 'mifeco-suite'),
        site_url('/my-account/')
    ) . "\n\n";
    
    $message .= __('Thank you for your continued support!', 'mifeco-suite') . "\n\n";
    $message .= __('Best regards,', 'mifeco-suite') . "\n";
    $message .= __('The MIFECO Team', 'mifeco-suite');
    
    wp_mail($to, $subject, $message);
}

/**
 * Send subscription cancelled email
 *
 * @param int $user_id User ID
 * @param string $expiry_date Optional. Subscription expiry date for grace period
 */
function mifeco_send_subscription_cancelled_email($user_id, $expiry_date = null) {
    $user = get_userdata($user_id);
    if (!$user) return;
    
    $to = $user->user_email;
    $subject = __('Your MIFECO Subscription Has Been Cancelled', 'mifeco-suite');
    
    $plan = get_user_meta($user_id, 'mifeco_subscription_plan', true);
    $plan_name = '';
    switch ($plan) {
        case 'basic':
            $plan_name = __('Basic Plan', 'mifeco-suite');
            break;
        case 'premium':
            $plan_name = __('Premium Plan', 'mifeco-suite');
            break;
        case 'enterprise':
            $plan_name = __('Enterprise Plan', 'mifeco-suite');
            break;
        default:
            $plan_name = ucfirst($plan);
    }
    
    $message = sprintf(
        __('Hello %s,', 'mifeco-suite'),
        $user->display_name
    ) . "\n\n";
    
    $message .= sprintf(
        __('Your subscription to the %s has been cancelled.', 'mifeco-suite'),
        $plan_name
    ) . "\n\n";
    
    if ($expiry_date) {
        $message .= sprintf(
            __('You will continue to have access to your subscription benefits until %s.', 'mifeco-suite'),
            date_i18n(get_option('date_format'), strtotime($expiry_date))
        ) . "\n\n";
    } else {
        $message .= __('Your access to subscription benefits has ended.', 'mifeco-suite') . "\n\n";
    }
    
    $message .= sprintf(
        __('If you wish to resubscribe, you can do so here: %s', 'mifeco-suite'),
        site_url('/subscriptions/')
    ) . "\n\n";
    
    $message .= __('If you cancelled by mistake or have any questions, please contact our support team.', 'mifeco-suite') . "\n\n";
    $message .= __('Best regards,', 'mifeco-suite') . "\n";
    $message .= __('The MIFECO Team', 'mifeco-suite');
    
    wp_mail($to, $subject, $message);
}

/**
 * Send payment failed email
 *
 * @param int $user_id User ID
 * @param int $attempt_count Number of payment attempts
 */
function mifeco_send_payment_failed_email($user_id, $attempt_count) {
    $user = get_userdata($user_id);
    if (!$user) return;
    
    $to = $user->user_email;
    $subject = __('Action Required: Payment Failed for Your MIFECO Subscription', 'mifeco-suite');
    
    $message = sprintf(
        __('Hello %s,', 'mifeco-suite'),
        $user->display_name
    ) . "\n\n";
    
    $message .= __('We were unable to process your subscription payment.', 'mifeco-suite') . "\n\n";
    
    if ($attempt_count > 1) {
        $message .= sprintf(
            __('This is attempt %d to charge your payment method.', 'mifeco-suite'),
            $attempt_count
        ) . "\n\n";
    }
    
    $message .= __('To ensure uninterrupted access to your subscription benefits, please update your payment information as soon as possible.', 'mifeco-suite') . "\n\n";
    
    $message .= sprintf(
        __('You can update your payment details here: %s', 'mifeco-suite'),
        site_url('/my-account/')
    ) . "\n\n";
    
    $message .= __('If you have any questions or need assistance, please contact our support team.', 'mifeco-suite') . "\n\n";
    $message .= __('Thank you for your prompt attention to this matter.', 'mifeco-suite') . "\n\n";
    $message .= __('Best regards,', 'mifeco-suite') . "\n";
    $message .= __('The MIFECO Team', 'mifeco-suite');
    
    wp_mail($to, $subject, $message);
}