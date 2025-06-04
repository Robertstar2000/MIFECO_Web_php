<?php
/**
 * Provide a public-facing view for Stripe Checkout
 *
 * This file is used to markup the public-facing aspects of the Stripe Checkout.
 *
 * @link       https://mifeco.com
 * @since      1.0.0
 *
 * @package    Mifeco_Suite
 * @subpackage Mifeco_Suite/public/partials/payment
 */

// Ensure direct file access isn't allowed
if (!defined('ABSPATH')) {
    exit;
}

// Get plan details from shortcode attributes
$plan = isset($atts['plan']) ? sanitize_text_field($atts['plan']) : '';
$button_text = isset($atts['button_text']) ? sanitize_text_field($atts['button_text']) : __('Subscribe Now', 'mifeco-suite');
$show_description = isset($atts['show_description']) ? filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN) : true;

// Get plan details
$plan_data = array();
switch ($plan) {
    case 'basic':
        $plan_data = array(
            'name' => __('Basic Plan', 'mifeco-suite'),
            'price' => '$99',
            'period' => __('per month', 'mifeco-suite'),
            'description' => __('Essential tools for business research', 'mifeco-suite'),
            'price_id' => get_option('mifeco_stripe_basic_price_id', '')
        );
        break;
    case 'premium':
        $plan_data = array(
            'name' => __('Premium Plan', 'mifeco-suite'),
            'price' => '$199',
            'period' => __('per month', 'mifeco-suite'),
            'description' => __('Advanced tools for business optimization', 'mifeco-suite'),
            'price_id' => get_option('mifeco_stripe_premium_price_id', '')
        );
        break;
    case 'enterprise':
        $plan_data = array(
            'name' => __('Enterprise Plan', 'mifeco-suite'),
            'price' => '$499',
            'period' => __('per month', 'mifeco-suite'),
            'description' => __('Complete solution for enterprise needs', 'mifeco-suite'),
            'price_id' => get_option('mifeco_stripe_enterprise_price_id', '')
        );
        break;
    default:
        // No valid plan specified
        echo '<div class="mifeco-error-message">' . __('Invalid plan specified.', 'mifeco-suite') . '</div>';
        return;
}

// Check if Stripe is configured
if (!mifeco_is_stripe_configured()) {
    // Display message only to administrators
    if (current_user_can('manage_options')) {
        echo '<div class="mifeco-error-message">' . __('Stripe is not configured. Please set up your Stripe API keys.', 'mifeco-suite') . '</div>';
    } else {
        echo '<div class="mifeco-error-message">' . __('Payment system is currently unavailable. Please try again later.', 'mifeco-suite') . '</div>';
    }
    return;
}

// Check if price ID is set
if (empty($plan_data['price_id'])) {
    // Display message only to administrators
    if (current_user_can('manage_options')) {
        echo '<div class="mifeco-error-message">' . __('Price ID is not configured for this plan. Please set up your plan price IDs.', 'mifeco-suite') . '</div>';
    } else {
        echo '<div class="mifeco-error-message">' . __('This subscription plan is currently unavailable. Please try again later.', 'mifeco-suite') . '</div>';
    }
    return;
}

// Check if user is logged in
$user_logged_in = is_user_logged_in();
$current_plan = '';

if ($user_logged_in) {
    $user_id = get_current_user_id();
    $current_plan = get_user_meta($user_id, 'mifeco_subscription_plan', true);
    $subscription_status = get_user_meta($user_id, 'mifeco_subscription_status', true);
    
    // If user already has this plan and it's active, show a message
    if ($current_plan === $plan && $subscription_status === 'active') {
        echo '<div class="mifeco-info-message">' . __('You are already subscribed to this plan.', 'mifeco-suite') . '</div>';
        return;
    }
}
?>

<div class="mifeco-checkout-container">
    <?php if ($show_description): ?>
        <div class="mifeco-plan-details">
            <h3 class="mifeco-plan-name"><?php echo esc_html($plan_data['name']); ?></h3>
            
            <div class="mifeco-plan-price">
                <span class="mifeco-price-amount"><?php echo esc_html($plan_data['price']); ?></span>
                <span class="mifeco-price-period"><?php echo esc_html($plan_data['period']); ?></span>
            </div>
            
            <div class="mifeco-plan-description">
                <?php echo esc_html($plan_data['description']); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!$user_logged_in): ?>
        <div class="mifeco-login-required">
            <p><?php _e('Please log in or create an account to subscribe.', 'mifeco-suite'); ?></p>
            <div class="mifeco-login-buttons">
                <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="mifeco-btn mifeco-btn-secondary"><?php _e('Log In', 'mifeco-suite'); ?></a>
                <a href="<?php echo esc_url(wp_registration_url()); ?>" class="mifeco-btn mifeco-btn-outline"><?php _e('Create Account', 'mifeco-suite'); ?></a>
            </div>
        </div>
    <?php else: ?>
        <div class="mifeco-checkout-button">
            <button class="mifeco-btn mifeco-btn-primary mifeco-subscribe-btn"
                    data-plan="<?php echo esc_attr($plan); ?>"
                    data-price-id="<?php echo esc_attr($plan_data['price_id']); ?>">
                <?php echo esc_html($button_text); ?>
            </button>
            
            <?php if (!empty($current_plan)): ?>
                <div class="mifeco-current-plan-note">
                    <?php printf(__('You are currently subscribed to the %s.', 'mifeco-suite'), 
                           mifeco_get_plan_display_name($current_plan)); ?>
                    
                    <?php if ($current_plan !== $plan): ?>
                        <?php 
                        $upgrade_or_downgrade = '';
                        switch ($current_plan) {
                            case 'basic':
                                $upgrade_or_downgrade = __('Upgrading', 'mifeco-suite');
                                break;
                            case 'premium':
                                if ($plan === 'basic') {
                                    $upgrade_or_downgrade = __('Downgrading', 'mifeco-suite');
                                } else {
                                    $upgrade_or_downgrade = __('Upgrading', 'mifeco-suite');
                                }
                                break;
                            case 'enterprise':
                                $upgrade_or_downgrade = __('Downgrading', 'mifeco-suite');
                                break;
                        }
                        ?>
                        
                        <?php if (!empty($upgrade_or_downgrade)): ?>
                            <div class="mifeco-plan-change-note">
                                <?php printf(__('%s to the %s will change your current subscription immediately.', 'mifeco-suite'), 
                                       $upgrade_or_downgrade,
                                       mifeco_get_plan_display_name($plan)); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Stripe payment processing modal -->
<div class="mifeco-payment-modal" id="mifeco-payment-modal">
    <div class="mifeco-payment-modal-content">
        <span class="mifeco-payment-modal-close">&times;</span>
        <div class="mifeco-payment-modal-header">
            <h3><?php _e('Complete Your Subscription', 'mifeco-suite'); ?></h3>
        </div>
        <div class="mifeco-payment-modal-body">
            <div id="mifeco-checkout-message" class="mifeco-checkout-message"></div>
            <div id="mifeco-checkout-loading" class="mifeco-checkout-loading">
                <div class="mifeco-spinner"></div>
                <p><?php _e('Preparing secure checkout...', 'mifeco-suite'); ?></p>
            </div>
        </div>
    </div>
</div>