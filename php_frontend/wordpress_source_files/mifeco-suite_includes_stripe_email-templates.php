<?php
/**
 * Email templates for Stripe integration.
 *
 * This file contains functions to send emails related to Stripe subscriptions.
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
 * Send subscription welcome email.
 *
 * @since    1.0.0
 * @param    int       $user_id    User ID.
 * @param    string    $plan       Subscription plan.
 * @return   bool      Whether the email was sent.
 */
function mifeco_send_subscription_welcome_email($user_id, $plan) {
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    $to = $user->user_email;
    $subject = __('Welcome to Your MIFECO Subscription!', 'mifeco-suite');
    
    $plan_name = mifeco_get_plan_display_name($plan);
    
    // Email template
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                padding: 20px;
                max-width: 600px;
                margin: 0 auto;
            }
            h1 {
                color: #2563eb;
                margin-top: 0;
            }
            .header {
                border-bottom: 1px solid #eee;
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
            .footer {
                border-top: 1px solid #eee;
                padding-top: 20px;
                margin-top: 20px;
                font-size: 14px;
                color: #666;
            }
            .button {
                background-color: #2563eb;
                color: #ffffff;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                margin: 20px 0;
            }
            .features {
                background-color: #f9fafb;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .feature {
                margin-bottom: 10px;
            }
            .feature:last-child {
                margin-bottom: 0;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><?php _e('Welcome to MIFECO!', 'mifeco-suite'); ?></h1>
        </div>
        
        <p><?php printf(__('Hello %s,', 'mifeco-suite'), $user->display_name); ?></p>
        
        <p><?php printf(__('Thank you for subscribing to the %s! Your subscription is now active.', 'mifeco-suite'), $plan_name); ?></p>
        
        <p><?php _e('You now have access to the following products:', 'mifeco-suite'); ?></p>
        
        <div class="features">
            <?php switch ($plan): 
                case 'enterprise': ?>
                    <div class="feature">
                        <strong><?php _e('Proposal Evaluation Tool', 'mifeco-suite'); ?></strong><br>
                        <?php _e('Comprehensive system for evaluating, scoring, and optimizing business proposals and RFPs.', 'mifeco-suite'); ?>
                    </div>
                <?php // Fall through
                case 'premium': ?>
                    <div class="feature">
                        <strong><?php _e('Ultimate Business Problem Solver', 'mifeco-suite'); ?></strong><br>
                        <?php _e('Advanced problem-solving system for identifying root causes and generating effective solutions.', 'mifeco-suite'); ?>
                    </div>
                <?php // Fall through
                case 'basic': ?>
                    <div class="feature">
                        <strong><?php _e('Advanced Research Tool', 'mifeco-suite'); ?></strong><br>
                        <?php _e('Powerful research tool for business intelligence and market analysis.', 'mifeco-suite'); ?>
                    </div>
                <?php break;
            endswitch; ?>
        </div>
        
        <p><?php _e('You can access these tools by logging into your account:', 'mifeco-suite'); ?></p>
        
        <a href="<?php echo esc_url(site_url('/my-account/')); ?>" class="button"><?php _e('Access My Account', 'mifeco-suite'); ?></a>
        
        <p><?php _e('If you have any questions or need assistance, please contact our support team.', 'mifeco-suite'); ?></p>
        
        <p><?php _e('Thank you for choosing MIFECO!', 'mifeco-suite'); ?></p>
        
        <div class="footer">
            <p>
                <?php _e('Best regards,', 'mifeco-suite'); ?><br>
                <?php _e('The MIFECO Team', 'mifeco-suite'); ?>
            </p>
        </div>
    </body>
    </html>
    <?php
    $message = ob_get_clean();
    
    // Headers for HTML email
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: MIFECO <noreply@' . parse_url(site_url(), PHP_URL_HOST) . '>'
    );
    
    return wp_mail($to, $subject, $message, $headers);
}

/**
 * Send subscription renewal email.
 *
 * @since    1.0.0
 * @param    int       $user_id        User ID.
 * @param    float     $amount         Amount paid.
 * @param    string    $expiry_date    Subscription expiry date.
 * @return   bool      Whether the email was sent.
 */
function mifeco_send_subscription_renewal_email($user_id, $amount, $expiry_date) {
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    $to = $user->user_email;
    $subject = __('Your MIFECO Subscription Has Been Renewed', 'mifeco-suite');
    
    $plan = get_user_meta($user_id, 'mifeco_subscription_plan', true);
    $plan_name = mifeco_get_plan_display_name($plan);
    
    // Email template
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                padding: 20px;
                max-width: 600px;
                margin: 0 auto;
            }
            h1 {
                color: #2563eb;
                margin-top: 0;
            }
            .header {
                border-bottom: 1px solid #eee;
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
            .footer {
                border-top: 1px solid #eee;
                padding-top: 20px;
                margin-top: 20px;
                font-size: 14px;
                color: #666;
            }
            .button {
                background-color: #2563eb;
                color: #ffffff;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                margin: 20px 0;
            }
            .info-box {
                background-color: #f9fafb;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .info-item {
                margin-bottom: 10px;
            }
            .info-label {
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><?php _e('Subscription Renewed', 'mifeco-suite'); ?></h1>
        </div>
        
        <p><?php printf(__('Hello %s,', 'mifeco-suite'), $user->display_name); ?></p>
        
        <p><?php printf(__('Your subscription to the %s has been renewed successfully.', 'mifeco-suite'), $plan_name); ?></p>
        
        <div class="info-box">
            <div class="info-item">
                <span class="info-label"><?php _e('Amount charged:', 'mifeco-suite'); ?></span>
                $<?php echo number_format($amount, 2); ?> USD
            </div>
            
            <div class="info-item">
                <span class="info-label"><?php _e('Next billing date:', 'mifeco-suite'); ?></span>
                <?php echo date_i18n(get_option('date_format'), strtotime($expiry_date)); ?>
            </div>
        </div>
        
        <p><?php _e('You can manage your subscription from your account:', 'mifeco-suite'); ?></p>
        
        <a href="<?php echo esc_url(site_url('/my-account/')); ?>" class="button"><?php _e('Manage My Subscription', 'mifeco-suite'); ?></a>
        
        <p><?php _e('Thank you for your continued support!', 'mifeco-suite'); ?></p>
        
        <div class="footer">
            <p>
                <?php _e('Best regards,', 'mifeco-suite'); ?><br>
                <?php _e('The MIFECO Team', 'mifeco-suite'); ?>
            </p>
        </div>
    </body>
    </html>
    <?php
    $message = ob_get_clean();
    
    // Headers for HTML email
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: MIFECO <noreply@' . parse_url(site_url(), PHP_URL_HOST) . '>'
    );
    
    return wp_mail($to, $subject, $message, $headers);
}

/**
 * Send subscription cancellation email.
 *
 * @since    1.0.0
 * @param    int       $user_id        User ID.
 * @param    string    $expiry_date    Optional. Subscription expiry date for grace period.
 * @return   bool      Whether the email was sent.
 */
function mifeco_send_subscription_cancelled_email($user_id, $expiry_date = null) {
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    $to = $user->user_email;
    $subject = __('Your MIFECO Subscription Has Been Cancelled', 'mifeco-suite');
    
    $plan = get_user_meta($user_id, 'mifeco_subscription_plan', true);
    $plan_name = mifeco_get_plan_display_name($plan);
    
    // Email template
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                padding: 20px;
                max-width: 600px;
                margin: 0 auto;
            }
            h1 {
                color: #2563eb;
                margin-top: 0;
            }
            .header {
                border-bottom: 1px solid #eee;
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
            .footer {
                border-top: 1px solid #eee;
                padding-top: 20px;
                margin-top: 20px;
                font-size: 14px;
                color: #666;
            }
            .button {
                background-color: #2563eb;
                color: #ffffff;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                margin: 20px 0;
            }
            .notice-box {
                background-color: #fef2f2;
                border-left: 4px solid #ef4444;
                padding: 15px;
                border-radius: 0 5px 5px 0;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><?php _e('Subscription Cancelled', 'mifeco-suite'); ?></h1>
        </div>
        
        <p><?php printf(__('Hello %s,', 'mifeco-suite'), $user->display_name); ?></p>
        
        <p><?php printf(__('Your subscription to the %s has been cancelled.', 'mifeco-suite'), $plan_name); ?></p>
        
        <?php if ($expiry_date): ?>
            <div class="notice-box">
                <p><strong><?php _e('Important:', 'mifeco-suite'); ?></strong> <?php printf(__('You will continue to have access to your subscription benefits until %s.', 'mifeco-suite'), date_i18n(get_option('date_format'), strtotime($expiry_date))); ?></p>
            </div>
        <?php else: ?>
            <div class="notice-box">
                <p><strong><?php _e('Important:', 'mifeco-suite'); ?></strong> <?php _e('Your access to subscription benefits has ended.', 'mifeco-suite'); ?></p>
            </div>
        <?php endif; ?>
        
        <p><?php _e('If you wish to resubscribe, you can do so here:', 'mifeco-suite'); ?></p>
        
        <a href="<?php echo esc_url(site_url('/subscriptions/')); ?>" class="button"><?php _e('Resubscribe Now', 'mifeco-suite'); ?></a>
        
        <p><?php _e('If you cancelled by mistake or have any questions, please contact our support team.', 'mifeco-suite'); ?></p>
        
        <div class="footer">
            <p>
                <?php _e('Best regards,', 'mifeco-suite'); ?><br>
                <?php _e('The MIFECO Team', 'mifeco-suite'); ?>
            </p>
        </div>
    </body>
    </html>
    <?php
    $message = ob_get_clean();
    
    // Headers for HTML email
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: MIFECO <noreply@' . parse_url(site_url(), PHP_URL_HOST) . '>'
    );
    
    return wp_mail($to, $subject, $message, $headers);
}

/**
 * Send payment failed email.
 *
 * @since    1.0.0
 * @param    int       $user_id         User ID.
 * @param    int       $attempt_count   Number of payment attempts.
 * @return   bool      Whether the email was sent.
 */
function mifeco_send_payment_failed_email($user_id, $attempt_count) {
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    $to = $user->user_email;
    $subject = __('Action Required: Payment Failed for Your MIFECO Subscription', 'mifeco-suite');
    
    // Email template
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                padding: 20px;
                max-width: 600px;
                margin: 0 auto;
            }
            h1 {
                color: #ef4444;
                margin-top: 0;
            }
            .header {
                border-bottom: 1px solid #eee;
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
            .footer {
                border-top: 1px solid #eee;
                padding-top: 20px;
                margin-top: 20px;
                font-size: 14px;
                color: #666;
            }
            .button {
                background-color: #2563eb;
                color: #ffffff;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                margin: 20px 0;
            }
            .alert-box {
                background-color: #fef2f2;
                border-left: 4px solid #ef4444;
                padding: 15px;
                border-radius: 0 5px 5px 0;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><?php _e('Payment Failed', 'mifeco-suite'); ?></h1>
        </div>
        
        <p><?php printf(__('Hello %s,', 'mifeco-suite'), $user->display_name); ?></p>
        
        <div class="alert-box">
            <p><strong><?php _e('Action Required:', 'mifeco-suite'); ?></strong> <?php _e('We were unable to process your subscription payment.', 'mifeco-suite'); ?></p>
            
            <?php if ($attempt_count > 1): ?>
                <p><?php printf(__('This is attempt %d to charge your payment method.', 'mifeco-suite'), $attempt_count); ?></p>
            <?php endif; ?>
        </div>
        
        <p><?php _e('To ensure uninterrupted access to your subscription benefits, please update your payment information as soon as possible.', 'mifeco-suite'); ?></p>
        
        <a href="<?php echo esc_url(site_url('/my-account/')); ?>" class="button"><?php _e('Update Payment Method', 'mifeco-suite'); ?></a>
        
        <p><?php _e('If you have any questions or need assistance, please contact our support team.', 'mifeco-suite'); ?></p>
        
        <p><?php _e('Thank you for your prompt attention to this matter.', 'mifeco-suite'); ?></p>
        
        <div class="footer">
            <p>
                <?php _e('Best regards,', 'mifeco-suite'); ?><br>
                <?php _e('The MIFECO Team', 'mifeco-suite'); ?>
            </p>
        </div>
    </body>
    </html>
    <?php
    $message = ob_get_clean();
    
    // Headers for HTML email
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: MIFECO <noreply@' . parse_url(site_url(), PHP_URL_HOST) . '>'
    );
    
    return wp_mail($to, $subject, $message, $headers);
}

/**
 * Send subscription expired email.
 *
 * @since    1.0.0
 * @param    int       $user_id    User ID.
 * @return   bool      Whether the email was sent.
 */
function mifeco_send_subscription_expired_email($user_id) {
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    $to = $user->user_email;
    $subject = __('Your MIFECO Subscription Has Expired', 'mifeco-suite');
    
    $plan = get_user_meta($user_id, 'mifeco_subscription_plan', true);
    $plan_name = mifeco_get_plan_display_name($plan);
    
    // Email template
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                padding: 20px;
                max-width: 600px;
                margin: 0 auto;
            }
            h1 {
                color: #2563eb;
                margin-top: 0;
            }
            .header {
                border-bottom: 1px solid #eee;
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
            .footer {
                border-top: 1px solid #eee;
                padding-top: 20px;
                margin-top: 20px;
                font-size: 14px;
                color: #666;
            }
            .button {
                background-color: #2563eb;
                color: #ffffff;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                margin: 20px 0;
            }
            .notice-box {
                background-color: #fef2f2;
                border-left: 4px solid #ef4444;
                padding: 15px;
                border-radius: 0 5px 5px 0;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><?php _e('Subscription Expired', 'mifeco-suite'); ?></h1>
        </div>
        
        <p><?php printf(__('Hello %s,', 'mifeco-suite'), $user->display_name); ?></p>
        
        <p><?php printf(__('Your subscription to the %s has expired.', 'mifeco-suite'), $plan_name); ?></p>
        
        <div class="notice-box">
            <p><strong><?php _e('Important:', 'mifeco-suite'); ?></strong> <?php _e('Your access to subscription benefits has now ended.', 'mifeco-suite'); ?></p>
        </div>
        
        <p><?php _e('We\'d love to have you back! You can renew your subscription here:', 'mifeco-suite'); ?></p>
        
        <a href="<?php echo esc_url(site_url('/subscriptions/')); ?>" class="button"><?php _e('Renew Subscription', 'mifeco-suite'); ?></a>
        
        <p><?php _e('If you have any questions or need assistance, please contact our support team.', 'mifeco-suite'); ?></p>
        
        <div class="footer">
            <p>
                <?php _e('Best regards,', 'mifeco-suite'); ?><br>
                <?php _e('The MIFECO Team', 'mifeco-suite'); ?>
            </p>
        </div>
    </body>
    </html>
    <?php
    $message = ob_get_clean();
    
    // Headers for HTML email
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: MIFECO <noreply@' . parse_url(site_url(), PHP_URL_HOST) . '>'
    );
    
    return wp_mail($to, $subject, $message, $headers);
}

/**
 * Get plan display name.
 *
 * @since    1.0.0
 * @param    string    $plan    Plan ID (basic, premium, enterprise).
 * @return   string    Display name for the plan.
 */
function mifeco_get_plan_display_name($plan) {
    switch ($plan) {
        case 'basic':
            return __('Basic Plan', 'mifeco-suite');
        case 'premium':
            return __('Premium Plan', 'mifeco-suite');
        case 'enterprise':
            return __('Enterprise Plan', 'mifeco-suite');
        default:
            return ucfirst($plan);
    }
}