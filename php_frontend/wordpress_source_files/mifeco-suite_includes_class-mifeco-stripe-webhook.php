<?php
/**
 * The webhook functionality of the plugin.
 *
 * @link       https://mifeco.com
 * @since      1.0.0
 *
 * @package    MIFECO_Suite
 * @subpackage MIFECO_Suite/includes
 */

/**
 * The webhook functionality of the plugin.
 *
 * Defines the plugin's REST API endpoint for receiving webhooks from Stripe.
 *
 * @package    MIFECO_Suite
 * @subpackage MIFECO_Suite/includes
 * @author     MIFECO <contact@mifeco.com>
 */
class MIFECO_Stripe_Webhook {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_rest_endpoints'));
    }

    /**
     * Register REST API endpoints
     */
    public function register_rest_endpoints() {
        register_rest_route('mifeco/v1', '/stripe-webhook', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_webhook'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Handle webhook event
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function handle_webhook($request) {
        // Get the event payload
        $payload = $request->get_body();
        
        // Get the signature header
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        
        // Get webhook secret
        $webhook_secret = get_option('mifeco_stripe_webhook_secret', '');
        
        if (empty($webhook_secret)) {
            error_log('Stripe webhook secret is not configured');
            return new WP_REST_Response(['status' => 'error', 'message' => 'Configuration error'], 500);
        }
        
        // Verify webhook signature
        try {
            // Ensure Stripe API is loaded
            if (!function_exists('mifeco_init_stripe_api')) {
                require_once plugin_dir_path(dirname(__FILE__)) . 'includes/stripe/stripe-api-helper.php';
            }
            
            mifeco_init_stripe_api();
            
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $webhook_secret);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            error_log('Stripe webhook error: Invalid payload - ' . $e->getMessage());
            return new WP_REST_Response(['status' => 'error', 'message' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            error_log('Stripe webhook error: Invalid signature - ' . $e->getMessage());
            return new WP_REST_Response(['status' => 'error', 'message' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            // General error
            error_log('Stripe webhook error: ' . $e->getMessage());
            return new WP_REST_Response(['status' => 'error', 'message' => 'Error'], 500);
        }
        
        // Process the event
        try {
            // Load webhook handler functions
            if (!function_exists('mifeco_process_checkout_session')) {
                require_once plugin_dir_path(dirname(__FILE__)) . 'includes/stripe/stripe-webhook-handler.php';
            }
            
            // Handle the event based on its type
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    mifeco_process_checkout_session($session);
                    break;
                    
                case 'customer.subscription.created':
                    $subscription = $event->data->object;
                    mifeco_process_subscription_update($subscription);
                    break;
                    
                case 'customer.subscription.updated':
                    $subscription = $event->data->object;
                    mifeco_process_subscription_update($subscription);
                    break;
                    
                case 'customer.subscription.deleted':
                    $subscription = $event->data->object;
                    mifeco_process_subscription_deleted($subscription);
                    break;
                    
                case 'invoice.payment_succeeded':
                    $invoice = $event->data->object;
                    mifeco_process_invoice_payment_succeeded($invoice);
                    break;
                    
                case 'invoice.payment_failed':
                    $invoice = $event->data->object;
                    mifeco_process_invoice_payment_failed($invoice);
                    break;
                    
                case 'charge.succeeded':
                    $charge = $event->data->object;
                    $this->process_charge_succeeded($charge);
                    break;
                    
                case 'charge.failed':
                    $charge = $event->data->object;
                    $this->process_charge_failed($charge);
                    break;
                    
                case 'payment_intent.succeeded':
                    $payment_intent = $event->data->object;
                    $this->process_payment_intent_succeeded($payment_intent);
                    break;
                    
                case 'payment_intent.payment_failed':
                    $payment_intent = $event->data->object;
                    $this->process_payment_intent_failed($payment_intent);
                    break;
                    
                default:
                    // Log unhandled event type
                    error_log('Unhandled Stripe webhook event: ' . $event->type);
            }
            
            // Return success response
            return new WP_REST_Response(['status' => 'success', 'event' => $event->type], 200);
            
        } catch (\Exception $e) {
            // Log and return error response
            error_log('Error processing Stripe webhook event: ' . $e->getMessage());
            return new WP_REST_Response(['status' => 'error', 'message' => 'Event processing error'], 500);
        }
    }
    
    /**
     * Process charge.succeeded event
     *
     * @param object $charge Stripe Charge object
     */
    private function process_charge_succeeded($charge) {
        // Get WordPress user ID from metadata
        $user_id = null;
        
        // Check charge metadata
        if (isset($charge->metadata->wordpress_user_id)) {
            $user_id = $charge->metadata->wordpress_user_id;
        }
        
        // Try to get user from customer
        if (!$user_id && isset($charge->customer)) {
            // Find user by customer ID
            global $wpdb;
            $user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'mifeco_stripe_customer_id' AND meta_value = %s LIMIT 1",
                $charge->customer
            ));
        }
        
        if (!$user_id) {
            error_log('No WordPress user found for Charge: ' . $charge->id);
            return;
        }
        
        // Get product ID from metadata
        $product_id = isset($charge->metadata->product_id) ? $charge->metadata->product_id : null;
        $product_type = isset($charge->metadata->product_type) ? $charge->metadata->product_type : 'consulting';
        
        // Create order record if this is for a product
        if ($product_id && $product_type === 'consulting') {
            global $wpdb;
            $table_name = $wpdb->prefix . 'mifeco_orders';
            
            // Check if order already exists
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE stripe_charge_id = %s",
                $charge->id
            ));
            
            if (!$existing) {
                // Insert new order
                $wpdb->insert(
                    $table_name,
                    array(
                        'user_id' => $user_id,
                        'product_id' => $product_id,
                        'product_type' => $product_type,
                        'amount' => $charge->amount / 100,
                        'stripe_customer_id' => $charge->customer,
                        'stripe_charge_id' => $charge->id,
                        'status' => 'completed'
                    )
                );
                
                // Create order post for admin
                $order_id = $wpdb->insert_id;
                $order_post = array(
                    'post_title'   => ($user_id ? get_userdata($user_id)->display_name : 'Guest') . ' - Consulting Order - ' . date('Y-m-d H:i:s'),
                    'post_status'  => 'publish',
                    'post_type'    => 'mifeco_order',
                );
                
                $post_id = wp_insert_post($order_post);
                
                if ($post_id) {
                    update_post_meta($post_id, '_order_id', $order_id);
                    update_post_meta($post_id, '_user_id', $user_id);
                    update_post_meta($post_id, '_product_id', $product_id);
                    update_post_meta($post_id, '_amount', $charge->amount / 100);
                    update_post_meta($post_id, '_charge_id', $charge->id);
                }
                
                // Send confirmation email
                $this->send_payment_confirmation_email($user_id, $product_id, $charge->amount / 100);
            }
        }
    }
    
    /**
     * Process charge.failed event
     *
     * @param object $charge Stripe Charge object
     */
    private function process_charge_failed($charge) {
        // Get WordPress user ID from metadata
        $user_id = null;
        
        // Check charge metadata
        if (isset($charge->metadata->wordpress_user_id)) {
            $user_id = $charge->metadata->wordpress_user_id;
        }
        
        // Try to get user from customer
        if (!$user_id && isset($charge->customer)) {
            // Find user by customer ID
            global $wpdb;
            $user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'mifeco_stripe_customer_id' AND meta_value = %s LIMIT 1",
                $charge->customer
            ));
        }
        
        if (!$user_id) {
            error_log('No WordPress user found for Charge: ' . $charge->id);
            return;
        }
        
        // Get product ID from metadata
        $product_id = isset($charge->metadata->product_id) ? $charge->metadata->product_id : null;
        $product_type = isset($charge->metadata->product_type) ? $charge->metadata->product_type : 'consulting';
        
        // Create order record if this is for a product
        if ($product_id && $product_type === 'consulting') {
            global $wpdb;
            $table_name = $wpdb->prefix . 'mifeco_orders';
            
            // Check if order already exists
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE stripe_charge_id = %s",
                $charge->id
            ));
            
            if (!$existing) {
                // Insert new order
                $wpdb->insert(
                    $table_name,
                    array(
                        'user_id' => $user_id,
                        'product_id' => $product_id,
                        'product_type' => $product_type,
                        'amount' => $charge->amount / 100,
                        'stripe_customer_id' => $charge->customer,
                        'stripe_charge_id' => $charge->id,
                        'status' => 'failed'
                    )
                );
                
                // Create order post for admin
                $order_id = $wpdb->insert_id;
                $order_post = array(
                    'post_title'   => ($user_id ? get_userdata($user_id)->display_name : 'Guest') . ' - Failed Consulting Order - ' . date('Y-m-d H:i:s'),
                    'post_status'  => 'publish',
                    'post_type'    => 'mifeco_order',
                );
                
                $post_id = wp_insert_post($order_post);
                
                if ($post_id) {
                    update_post_meta($post_id, '_order_id', $order_id);
                    update_post_meta($post_id, '_user_id', $user_id);
                    update_post_meta($post_id, '_product_id', $product_id);
                    update_post_meta($post_id, '_amount', $charge->amount / 100);
                    update_post_meta($post_id, '_charge_id', $charge->id);
                    update_post_meta($post_id, '_status', 'failed');
                    update_post_meta($post_id, '_failure_message', $charge->failure_message);
                }
                
                // Send failed payment email
                $this->send_payment_failure_email($user_id, $product_id, $charge->amount / 100, $charge->failure_message);
            }
        }
    }
    
    /**
     * Process payment_intent.succeeded event
     *
     * @param object $payment_intent Stripe PaymentIntent object
     */
    private function process_payment_intent_succeeded($payment_intent) {
        // Most of the work is done in charge.succeeded
        // This is here for completeness and potential future use
        
        // Log successful payment intent
        error_log('PaymentIntent succeeded: ' . $payment_intent->id);
    }
    
    /**
     * Process payment_intent.payment_failed event
     *
     * @param object $payment_intent Stripe PaymentIntent object
     */
    private function process_payment_intent_failed($payment_intent) {
        // Most of the work is done in charge.failed
        // This is here for completeness and potential future use
        
        // Log failed payment intent
        error_log('PaymentIntent failed: ' . $payment_intent->id . ' - ' . $payment_intent->last_payment_error->message);
    }
    
    /**
     * Send payment confirmation email
     *
     * @param int $user_id WordPress user ID
     * @param int $product_id Product ID
     * @param float $amount Payment amount
     */
    private function send_payment_confirmation_email($user_id, $product_id, $amount) {
        $user = get_userdata($user_id);
        if (!$user) return;
        
        // Get product name
        global $wpdb;
        $product_name = '';
        
        // Check if this is a consulting product
        $consulting_products = array(
            'initial_consultation' => 'Initial Consultation',
            'business_analysis' => 'Business Analysis',
            'executive_coaching' => 'Executive Coaching',
            'strategy_session' => 'Strategy Session'
        );
        
        if (array_key_exists($product_id, $consulting_products)) {
            $product_name = $consulting_products[$product_id];
        } else {
            // Try to get from products table
            $products_table = $wpdb->prefix . 'mifeco_products';
            $product = $wpdb->get_row($wpdb->prepare("SELECT name FROM $products_table WHERE id = %d", $product_id));
            if ($product) {
                $product_name = $product->name;
            } else {
                $product_name = 'Consulting Service';
            }
        }
        
        $to = $user->user_email;
        $subject = 'Payment Confirmation - ' . $product_name;
        
        $body = "Dear " . $user->display_name . ",\n\n";
        $body .= "Thank you for your payment of $" . number_format($amount, 2) . " for " . $product_name . ".\n\n";
        
        $body .= "Your payment has been processed successfully.\n\n";
        
        $body .= "Our team will be in touch with you shortly to schedule your consultation.\n\n";
        
        $body .= "If you have any questions, please don't hesitate to contact us.\n\n";
        
        $body .= "Best regards,\n";
        $body .= get_bloginfo('name') . " Team";
        
        wp_mail($to, $subject, $body);
    }
    
    /**
     * Send payment failure email
     *
     * @param int $user_id WordPress user ID
     * @param int $product_id Product ID
     * @param float $amount Payment amount
     * @param string $failure_message Failure message
     */
    private function send_payment_failure_email($user_id, $product_id, $amount, $failure_message) {
        $user = get_userdata($user_id);
        if (!$user) return;
        
        // Get product name
        global $wpdb;
        $product_name = '';
        
        // Check if this is a consulting product
        $consulting_products = array(
            'initial_consultation' => 'Initial Consultation',
            'business_analysis' => 'Business Analysis',
            'executive_coaching' => 'Executive Coaching',
            'strategy_session' => 'Strategy Session'
        );
        
        if (array_key_exists($product_id, $consulting_products)) {
            $product_name = $consulting_products[$product_id];
        } else {
            // Try to get from products table
            $products_table = $wpdb->prefix . 'mifeco_products';
            $product = $wpdb->get_row($wpdb->prepare("SELECT name FROM $products_table WHERE id = %d", $product_id));
            if ($product) {
                $product_name = $product->name;
            } else {
                $product_name = 'Consulting Service';
            }
        }
        
        $to = $user->user_email;
        $subject = 'Payment Failed - ' . $product_name;
        
        $body = "Dear " . $user->display_name . ",\n\n";
        $body .= "We were unable to process your payment of $" . number_format($amount, 2) . " for " . $product_name . ".\n\n";
        
        $body .= "The payment was declined for the following reason: " . $failure_message . "\n\n";
        
        $body .= "Please check your payment details and try again, or contact your card issuer for more information.\n\n";
        
        $body .= "If you need assistance, please don't hesitate to contact us.\n\n";
        
        $body .= "Best regards,\n";
        $body .= get_bloginfo('name') . " Team";
        
        wp_mail($to, $subject, $body);
    }
}