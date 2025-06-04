<?php
/**
 * Handle Stripe integration for payments and subscriptions.
 *
 * @package    MIFECO_Suite
 */

class MIFECO_Stripe {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Stripe API Key
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_key    The Stripe API key.
     */
    private $api_key;

    /**
     * Stripe Publishable Key
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $publishable_key    The Stripe publishable key.
     */
    private $publishable_key;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Get Stripe API keys from settings
        $settings = get_option('mifeco_suite_settings');
        $mode = $settings['stripe_mode'] ?? 'test';
        
        if ($mode === 'test') {
            $this->api_key = $settings['stripe_test_secret_key'] ?? '';
            $this->publishable_key = $settings['stripe_test_publishable_key'] ?? '';
        } else {
            $this->api_key = $settings['stripe_live_secret_key'] ?? '';
            $this->publishable_key = $settings['stripe_live_publishable_key'] ?? '';
        }
    }

    /**
     * Register REST API endpoints for Stripe webhooks
     */
    public function register_stripe_endpoints() {
        register_rest_route('mifeco/v1', '/stripe/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_stripe_webhook'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Process payment
     */
    public function process_payment() {
        // Check nonce for security
        check_ajax_referer('mifeco_payment_nonce', 'nonce');

        // Ensure Stripe API is loaded
        if (!class_exists('Stripe\Stripe')) {
            require_once MIFECO_PLUGIN_DIR . 'includes/stripe/stripe-php/init.php';
        }

        // Set API key
        \Stripe\Stripe::setApiKey($this->api_key);

        // Get form data
        $token = sanitize_text_field($_POST['token']);
        $product_id = intval($_POST['product_id']);
        $product_type = sanitize_text_field($_POST['product_type']); // 'consulting' or 'saas'
        $billing_cycle = isset($_POST['billing_cycle']) ? sanitize_text_field($_POST['billing_cycle']) : 'monthly'; // 'monthly' or 'annual'
        $amount = floatval($_POST['amount']) * 100; // Convert to cents for Stripe
        $email = sanitize_email($_POST['email']);
        $name = sanitize_text_field($_POST['name']);

        // Validate required fields
        if (empty($token) || empty($product_id) || empty($amount) || empty($email) || empty($name)) {
            wp_send_json_error(array('message' => __('All payment fields are required.', 'mifeco-suite')));
            return;
        }

        try {
            // Create or get customer
            $customer = $this->get_or_create_customer($email, $token, $name);
            
            if (!$customer) {
                throw new Exception(__('Failed to create customer in Stripe.', 'mifeco-suite'));
            }

            if ($product_type === 'consulting') {
                // One-time charge for consulting
                $charge = \Stripe\Charge::create(array(
                    'amount' => $amount,
                    'currency' => 'usd',
                    'description' => __('MIFECO Consulting Service', 'mifeco-suite'),
                    'customer' => $customer->id,
                    'metadata' => array(
                        'product_id' => $product_id,
                        'product_type' => 'consulting'
                    )
                ));
                
                // Create order record
                $this->create_consulting_order($product_id, get_current_user_id(), $customer->id, $amount / 100, $charge->id);
                
                $response = array(
                    'success' => true,
                    'message' => __('Payment successful! You will receive a confirmation email shortly.', 'mifeco-suite'),
                    'charge_id' => $charge->id
                );
            } else {
                // Get product details
                global $wpdb;
                $products_table = $wpdb->prefix . 'mifeco_products';
                $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM $products_table WHERE id = %d", $product_id));
                
                if (!$product) {
                    throw new Exception(__('Product not found.', 'mifeco-suite'));
                }
                
                // Use subscription handling for SaaS products
                $stripe_price_id = $billing_cycle === 'annual' ? $product->stripe_annual_price_id : $product->stripe_monthly_price_id;
                
                // Create the subscription if price ID exists
                if ($stripe_price_id) {
                    $subscription = \Stripe\Subscription::create(array(
                        'customer' => $customer->id,
                        'items' => array(
                            array('price' => $stripe_price_id),
                        ),
                        'metadata' => array(
                            'product_id' => $product_id,
                            'product_name' => $product->name,
                            'billing_cycle' => $billing_cycle
                        )
                    ));
                    
                    // Update subscription record or create new one
                    $this->create_or_update_subscription($product_id, get_current_user_id(), $customer->id, $subscription->id, $billing_cycle);
                    
                    $response = array(
                        'success' => true,
                        'message' => sprintf(__('You are now subscribed to %s! You will receive a confirmation email shortly.', 'mifeco-suite'), $product->name),
                        'subscription_id' => $subscription->id
                    );
                } else {
                    // If no Stripe price ID exists, create product and price in Stripe
                    $stripe_product = \Stripe\Product::create(array(
                        'name' => $product->name,
                        'description' => $product->description
                    ));
                    
                    $price_amount = $billing_cycle === 'annual' ? $product->annual_price * 100 : $product->monthly_price * 100;
                    $interval = $billing_cycle === 'annual' ? 'year' : 'month';
                    
                    $stripe_price = \Stripe\Price::create(array(
                        'product' => $stripe_product->id,
                        'unit_amount' => $price_amount,
                        'currency' => 'usd',
                        'recurring' => array(
                            'interval' => $interval,
                        )
                    ));
                    
                    // Update product in database with Stripe IDs
                    if ($billing_cycle === 'annual') {
                        $wpdb->update(
                            $products_table,
                            array(
                                'stripe_product_id' => $stripe_product->id,
                                'stripe_annual_price_id' => $stripe_price->id
                            ),
                            array('id' => $product_id)
                        );
                    } else {
                        $wpdb->update(
                            $products_table,
                            array(
                                'stripe_product_id' => $stripe_product->id,
                                'stripe_monthly_price_id' => $stripe_price->id
                            ),
                            array('id' => $product_id)
                        );
                    }
                    
                    // Create subscription with newly created price
                    $subscription = \Stripe\Subscription::create(array(
                        'customer' => $customer->id,
                        'items' => array(
                            array('price' => $stripe_price->id),
                        ),
                        'metadata' => array(
                            'product_id' => $product_id,
                            'product_name' => $product->name,
                            'billing_cycle' => $billing_cycle
                        )
                    ));
                    
                    // Update subscription record or create new one
                    $this->create_or_update_subscription($product_id, get_current_user_id(), $customer->id, $subscription->id, $billing_cycle);
                    
                    $response = array(
                        'success' => true,
                        'message' => sprintf(__('You are now subscribed to %s! You will receive a confirmation email shortly.', 'mifeco-suite'), $product->name),
                        'subscription_id' => $subscription->id
                    );
                }
            }
            
            wp_send_json_success($response);
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => __('Payment error: ', 'mifeco-suite') . $e->getMessage()
            ));
        }
    }

    /**
     * Create subscription
     */
    public function create_subscription() {
        // Check nonce for security
        check_ajax_referer('mifeco_subscription_nonce', 'nonce');

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to create a subscription.', 'mifeco-suite')));
            return;
        }

        // Ensure Stripe API is loaded
        if (!class_exists('Stripe\Stripe')) {
            require_once MIFECO_PLUGIN_DIR . 'includes/stripe/stripe-php/init.php';
        }

        // Set API key
        \Stripe\Stripe::setApiKey($this->api_key);

        // Get form data
        $token = sanitize_text_field($_POST['token']);
        $product_id = intval($_POST['product_id']);
        $billing_cycle = sanitize_text_field($_POST['billing_cycle']); // 'monthly' or 'annual'
        
        $user_id = get_current_user_id();
        $user_info = get_userdata($user_id);
        
        // Get product details
        global $wpdb;
        $products_table = $wpdb->prefix . 'mifeco_products';
        $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM $products_table WHERE id = %d", $product_id));
        
        if (!$product) {
            wp_send_json_error(array('message' => __('Product not found.', 'mifeco-suite')));
            return;
        }

        try {
            // Create or get customer
            $customer = $this->get_or_create_customer($user_info->user_email, $token, $user_info->display_name);
            
            if (!$customer) {
                throw new Exception(__('Failed to create customer in Stripe.', 'mifeco-suite'));
            }
            
            // Check if user already has a trial for this product
            $subscriptions_table = $wpdb->prefix . 'mifeco_subscriptions';
            $existing_subscription = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $subscriptions_table WHERE user_id = %d AND product_id = %d AND status = 'trialing'",
                $user_id,
                $product_id
            ));
            
            // Set trial_end to 'now' if the user is upgrading from a trial
            $trial_end = $existing_subscription ? 'now' : null;
            
            // Use subscription handling
            $stripe_price_id = $billing_cycle === 'annual' ? $product->stripe_annual_price_id : $product->stripe_monthly_price_id;
            
            // Create the subscription if price ID exists
            if ($stripe_price_id) {
                $subscription_params = array(
                    'customer' => $customer->id,
                    'items' => array(
                        array('price' => $stripe_price_id),
                    ),
                    'metadata' => array(
                        'product_id' => $product_id,
                        'product_name' => $product->name,
                        'billing_cycle' => $billing_cycle
                    )
                );
                
                if ($trial_end) {
                    $subscription_params['trial_end'] = $trial_end;
                }
                
                $subscription = \Stripe\Subscription::create($subscription_params);
                
                // Update subscription record or create new one
                $this->create_or_update_subscription($product_id, $user_id, $customer->id, $subscription->id, $billing_cycle);
                
                $response = array(
                    'success' => true,
                    'message' => sprintf(__('You are now subscribed to %s! You will receive a confirmation email shortly.', 'mifeco-suite'), $product->name),
                    'subscription_id' => $subscription->id,
                    'redirect_url' => get_permalink(get_option('mifeco_pages')['dashboard'])
                );
            } else {
                // If no Stripe price ID exists, create product and price in Stripe
                $stripe_product = \Stripe\Product::create(array(
                    'name' => $product->name,
                    'description' => $product->description
                ));
                
                $price_amount = $billing_cycle === 'annual' ? $product->annual_price * 100 : $product->monthly_price * 100;
                $interval = $billing_cycle === 'annual' ? 'year' : 'month';
                
                $stripe_price = \Stripe\Price::create(array(
                    'product' => $stripe_product->id,
                    'unit_amount' => $price_amount,
                    'currency' => 'usd',
                    'recurring' => array(
                        'interval' => $interval,
                    )
                ));
                
                // Update product in database with Stripe IDs
                if ($billing_cycle === 'annual') {
                    $wpdb->update(
                        $products_table,
                        array(
                            'stripe_product_id' => $stripe_product->id,
                            'stripe_annual_price_id' => $stripe_price->id
                        ),
                        array('id' => $product_id)
                    );
                } else {
                    $wpdb->update(
                        $products_table,
                        array(
                            'stripe_product_id' => $stripe_product->id,
                            'stripe_monthly_price_id' => $stripe_price->id
                        ),
                        array('id' => $product_id)
                    );
                }
                
                // Create subscription with newly created price
                $subscription_params = array(
                    'customer' => $customer->id,
                    'items' => array(
                        array('price' => $stripe_price->id),
                    ),
                    'metadata' => array(
                        'product_id' => $product_id,
                        'product_name' => $product->name,
                        'billing_cycle' => $billing_cycle
                    )
                );
                
                if ($trial_end) {
                    $subscription_params['trial_end'] = $trial_end;
                }
                
                $subscription = \Stripe\Subscription::create($subscription_params);
                
                // Update subscription record or create new one
                $this->create_or_update_subscription($product_id, $user_id, $customer->id, $subscription->id, $billing_cycle);
                
                $response = array(
                    'success' => true,
                    'message' => sprintf(__('You are now subscribed to %s! You will receive a confirmation email shortly.', 'mifeco-suite'), $product->name),
                    'subscription_id' => $subscription->id,
                    'redirect_url' => get_permalink(get_option('mifeco_pages')['dashboard'])
                );
            }
            
            // Send confirmation email
            $this->send_subscription_confirmation_email($user_id, $product->name, $billing_cycle);
            
            wp_send_json_success($response);
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => __('Subscription error: ', 'mifeco-suite') . $e->getMessage()
            ));
        }
    }

    /**
     * Update payment method
     */
    public function update_payment_method() {
        // Check nonce for security
        check_ajax_referer('mifeco_payment_method_nonce', 'nonce');

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to update your payment method.', 'mifeco-suite')));
            return;
        }

        // Ensure Stripe API is loaded
        if (!class_exists('Stripe\Stripe')) {
            require_once MIFECO_PLUGIN_DIR . 'includes/stripe/stripe-php/init.php';
        }

        // Set API key
        \Stripe\Stripe::setApiKey($this->api_key);

        // Get form data
        $token = sanitize_text_field($_POST['token']);
        $subscription_id = sanitize_text_field($_POST['subscription_id']);
        
        $user_id = get_current_user_id();
        
        // Verify the subscription belongs to the user
        global $wpdb;
        $subscriptions_table = $wpdb->prefix . 'mifeco_subscriptions';
        $subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $subscriptions_table WHERE id = %d AND user_id = %d",
            $subscription_id,
            $user_id
        ));
        
        if (!$subscription) {
            wp_send_json_error(array('message' => __('Subscription not found or does not belong to you.', 'mifeco-suite')));
            return;
        }

        try {
            // Update the customer's payment method
            $customer = \Stripe\Customer::update($subscription->stripe_customer_id, array(
                'source' => $token
            ));
            
            wp_send_json_success(array(
                'message' => __('Payment method updated successfully!', 'mifeco-suite')
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => __('Failed to update payment method: ', 'mifeco-suite') . $e->getMessage()
            ));
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel_subscription() {
        // Check nonce for security
        check_ajax_referer('mifeco_cancel_subscription_nonce', 'nonce');

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to cancel a subscription.', 'mifeco-suite')));
            return;
        }

        // Ensure Stripe API is loaded
        if (!class_exists('Stripe\Stripe')) {
            require_once MIFECO_PLUGIN_DIR . 'includes/stripe/stripe-php/init.php';
        }

        // Set API key
        \Stripe\Stripe::setApiKey($this->api_key);

        // Get form data
        $subscription_id = intval($_POST['subscription_id']);
        
        $user_id = get_current_user_id();
        
        // Verify the subscription belongs to the user
        global $wpdb;
        $subscriptions_table = $wpdb->prefix . 'mifeco_subscriptions';
        $subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $subscriptions_table WHERE id = %d AND user_id = %d",
            $subscription_id,
            $user_id
        ));
        
        if (!$subscription) {
            wp_send_json_error(array('message' => __('Subscription not found or does not belong to you.', 'mifeco-suite')));
            return;
        }

        try {
            if ($subscription->stripe_subscription_id) {
                // Cancel the subscription in Stripe
                $stripe_subscription = \Stripe\Subscription::retrieve($subscription->stripe_subscription_id);
                $stripe_subscription->cancel();
            }
            
            // Update subscription status in database
            $wpdb->update(
                $subscriptions_table,
                array('status' => 'cancelled'),
                array('id' => $subscription_id)
            );
            
            // Also update post meta if exists
            $args = array(
                'post_type' => 'mifeco_subscription',
                'meta_query' => array(
                    array(
                        'key' => '_subscription_id',
                        'value' => $subscription_id
                    )
                )
            );
            
            $subscription_posts = get_posts($args);
            
            if (!empty($subscription_posts)) {
                foreach ($subscription_posts as $post) {
                    update_post_meta($post->ID, '_status', 'cancelled');
                }
            }
            
            wp_send_json_success(array(
                'message' => __('Your subscription has been cancelled.', 'mifeco-suite')
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => __('Failed to cancel subscription: ', 'mifeco-suite') . $e->getMessage()
            ));
        }
    }

    /**
     * Handle Stripe webhook events
     */
    public function handle_stripe_webhook($request) {
        // Ensure Stripe API is loaded
        if (!class_exists('Stripe\Stripe')) {
            require_once MIFECO_PLUGIN_DIR . 'includes/stripe/stripe-php/init.php';
        }

        // Set API key
        \Stripe\Stripe::setApiKey($this->api_key);

        // Get the event by verifying the signature using the raw body and secret
        $payload = $request->get_body();
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $endpoint_secret = get_option('mifeco_stripe_webhook_secret', '');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return new WP_REST_Response(array('message' => 'Invalid payload'), 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return new WP_REST_Response(array('message' => 'Invalid signature'), 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'customer.subscription.created':
                $subscription = $event->data->object;
                // Handle subscription created
                $this->handle_subscription_created($subscription);
                break;
                
            case 'customer.subscription.updated':
                $subscription = $event->data->object;
                // Handle subscription updated
                $this->handle_subscription_updated($subscription);
                break;
                
            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                // Handle subscription cancelled
                $this->handle_subscription_cancelled($subscription);
                break;
                
            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;
                // Handle successful payment
                $this->handle_payment_succeeded($invoice);
                break;
                
            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                // Handle failed payment
                $this->handle_payment_failed($invoice);
                break;
                
            default:
                // Unexpected event type
                return new WP_REST_Response(array('message' => 'Unexpected event type'), 400);
        }

        return new WP_REST_Response(array('message' => 'Webhook received'), 200);
    }

    /**
     * Create or get a Stripe customer
     */
    private function get_or_create_customer($email, $token, $name = '') {
        try {
            // Check if customer already exists
            $existing_customers = \Stripe\Customer::all([
                'email' => $email,
                'limit' => 1
            ]);
            
            if (!empty($existing_customers->data)) {
                $customer = $existing_customers->data[0];
                
                // Update the payment method if token is provided
                if ($token) {
                    $customer->source = $token;
                    $customer->save();
                }
                
                return $customer;
            }
            
            // Create new customer
            return \Stripe\Customer::create([
                'email' => $email,
                'source' => $token,
                'name' => $name
            ]);
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create consulting order record
     */
    private function create_consulting_order($product_id, $user_id, $stripe_customer_id, $amount, $charge_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mifeco_orders';
        
        // Create orders table if it doesn't exist
        $this->maybe_create_orders_table();
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'product_id' => $product_id,
                'product_type' => 'consulting',
                'amount' => $amount,
                'stripe_customer_id' => $stripe_customer_id,
                'stripe_charge_id' => $charge_id,
                'status' => 'completed'
            )
        );
        
        if ($result) {
            $order_id = $wpdb->insert_id;
            
            // Create order post for admin
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
                update_post_meta($post_id, '_amount', $amount);
                update_post_meta($post_id, '_charge_id', $charge_id);
            }
            
            return $order_id;
        }
        
        return false;
    }

    /**
     * Create or update subscription record
     */
    private function create_or_update_subscription($product_id, $user_id, $stripe_customer_id, $stripe_subscription_id, $plan_name) {
        global $wpdb;
        $subscriptions_table = $wpdb->prefix . 'mifeco_subscriptions';
        
        // Check if a subscription already exists for this user and product
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $subscriptions_table WHERE user_id = %d AND product_id = %d",
            $user_id,
            $product_id
        ));
        
        if ($existing) {
            // Update existing subscription
            $wpdb->update(
                $subscriptions_table,
                array(
                    'stripe_subscription_id' => $stripe_subscription_id,
                    'stripe_customer_id' => $stripe_customer_id,
                    'plan_name' => $plan_name,
                    'status' => 'active',
                    'trial_end' => null
                ),
                array('id' => $existing->id)
            );
            
            // Update subscription post if exists
            $args = array(
                'post_type' => 'mifeco_subscription',
                'meta_query' => array(
                    array(
                        'key' => '_subscription_id',
                        'value' => $existing->id
                    )
                )
            );
            
            $subscription_posts = get_posts($args);
            
            if (!empty($subscription_posts)) {
                foreach ($subscription_posts as $post) {
                    update_post_meta($post->ID, '_status', 'active');
                    update_post_meta($post->ID, '_stripe_subscription_id', $stripe_subscription_id);
                    update_post_meta($post->ID, '_stripe_customer_id', $stripe_customer_id);
                    update_post_meta($post->ID, '_plan_name', $plan_name);
                }
            }
            
            return $existing->id;
        } else {
            // Create new subscription
            $result = $wpdb->insert(
                $subscriptions_table,
                array(
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'stripe_subscription_id' => $stripe_subscription_id,
                    'stripe_customer_id' => $stripe_customer_id,
                    'plan_name' => $plan_name,
                    'status' => 'active'
                )
            );
            
            if ($result) {
                $subscription_id = $wpdb->insert_id;
                
                // Create subscription post for admin
                $products_table = $wpdb->prefix . 'mifeco_products';
                $product = $wpdb->get_row($wpdb->prepare("SELECT name FROM $products_table WHERE id = %d", $product_id));
                $product_name = $product ? $product->name : 'Product #' . $product_id;
                
                $subscription_post = array(
                    'post_title'   => get_userdata($user_id)->display_name . ' - ' . $product_name . ' (' . ucfirst($plan_name) . ')',
                    'post_status'  => 'publish',
                    'post_type'    => 'mifeco_subscription',
                );
                
                $post_id = wp_insert_post($subscription_post);
                
                if ($post_id) {
                    update_post_meta($post_id, '_subscription_id', $subscription_id);
                    update_post_meta($post_id, '_user_id', $user_id);
                    update_post_meta($post_id, '_product_id', $product_id);
                    update_post_meta($post_id, '_status', 'active');
                    update_post_meta($post_id, '_stripe_subscription_id', $stripe_subscription_id);
                    update_post_meta($post_id, '_stripe_customer_id', $stripe_customer_id);
                    update_post_meta($post_id, '_plan_name', $plan_name);
                }
                
                return $subscription_id;
            }
        }
        
        return false;
    }

    /**
     * Handle subscription created webhook
     */
    private function handle_subscription_created($subscription) {
        // This is handled by our create_or_update_subscription method
        // when subscription is created through our interface
        // But we can still use this for subscriptions created directly in Stripe
        
        // Check if we have metadata with product_id
        if (isset($subscription->metadata->product_id)) {
            $product_id = $subscription->metadata->product_id;
            $billing_cycle = $subscription->metadata->billing_cycle ?? 'monthly';
            
            // Find user by customer ID
            $customer_id = $subscription->customer;
            $user_id = $this->get_user_id_by_stripe_customer($customer_id);
            
            if ($user_id && $product_id) {
                $this->create_or_update_subscription($product_id, $user_id, $customer_id, $subscription->id, $billing_cycle);
            }
        }
    }

    /**
     * Handle subscription updated webhook
     */
    private function handle_subscription_updated($subscription) {
        global $wpdb;
        $subscriptions_table = $wpdb->prefix . 'mifeco_subscriptions';
        
        // Find the subscription in our database
        $db_subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $subscriptions_table WHERE stripe_subscription_id = %s",
            $subscription->id
        ));
        
        if ($db_subscription) {
            // Update status based on Stripe subscription status
            $status = $subscription->status;
            if ($status === 'trialing' || $status === 'active' || $status === 'past_due' || $status === 'canceled') {
                $wpdb->update(
                    $subscriptions_table,
                    array('status' => $status),
                    array('id' => $db_subscription->id)
                );
                
                // Update post meta if exists
                $args = array(
                    'post_type' => 'mifeco_subscription',
                    'meta_query' => array(
                        array(
                            'key' => '_subscription_id',
                            'value' => $db_subscription->id
                        )
                    )
                );
                
                $subscription_posts = get_posts($args);
                
                if (!empty($subscription_posts)) {
                    foreach ($subscription_posts as $post) {
                        update_post_meta($post->ID, '_status', $status);
                    }
                }
            }
            
            // If there's a trial, update trial end date
            if ($subscription->trial_end) {
                $trial_end = date('Y-m-d H:i:s', $subscription->trial_end);
                
                $wpdb->update(
                    $subscriptions_table,
                    array('trial_end' => $trial_end),
                    array('id' => $db_subscription->id)
                );
                
                // Update post meta if exists
                if (!empty($subscription_posts)) {
                    foreach ($subscription_posts as $post) {
                        update_post_meta($post->ID, '_trial_end', $trial_end);
                    }
                }
            }
            
            // Update current period end
            if ($subscription->current_period_end) {
                $current_period_end = date('Y-m-d H:i:s', $subscription->current_period_end);
                
                $wpdb->update(
                    $subscriptions_table,
                    array('current_period_end' => $current_period_end),
                    array('id' => $db_subscription->id)
                );
                
                // Update post meta if exists
                if (!empty($subscription_posts)) {
                    foreach ($subscription_posts as $post) {
                        update_post_meta($post->ID, '_current_period_end', $current_period_end);
                    }
                }
            }
        }
    }

    /**
     * Handle subscription cancelled webhook
     */
    private function handle_subscription_cancelled($subscription) {
        global $wpdb;
        $subscriptions_table = $wpdb->prefix . 'mifeco_subscriptions';
        
        // Find the subscription in our database
        $db_subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $subscriptions_table WHERE stripe_subscription_id = %s",
            $subscription->id
        ));
        
        if ($db_subscription) {
            // Update status to cancelled
            $wpdb->update(
                $subscriptions_table,
                array('status' => 'cancelled'),
                array('id' => $db_subscription->id)
            );
            
            // Update post meta if exists
            $args = array(
                'post_type' => 'mifeco_subscription',
                'meta_query' => array(
                    array(
                        'key' => '_subscription_id',
                        'value' => $db_subscription->id
                    )
                )
            );
            
            $subscription_posts = get_posts($args);
            
            if (!empty($subscription_posts)) {
                foreach ($subscription_posts as $post) {
                    update_post_meta($post->ID, '_status', 'cancelled');
                }
            }
        }
    }

    /**
     * Handle payment succeeded webhook
     */
    private function handle_payment_succeeded($invoice) {
        // If this is a subscription invoice, update the subscription
        if ($invoice->subscription) {
            global $wpdb;
            $subscriptions_table = $wpdb->prefix . 'mifeco_subscriptions';
            
            // Find the subscription in our database
            $db_subscription = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $subscriptions_table WHERE stripe_subscription_id = %s",
                $invoice->subscription
            ));
            
            if ($db_subscription) {
                // Ensure status is active
                $wpdb->update(
                    $subscriptions_table,
                    array('status' => 'active'),
                    array('id' => $db_subscription->id)
                );
                
                // Update post meta if exists
                $args = array(
                    'post_type' => 'mifeco_subscription',
                    'meta_query' => array(
                        array(
                            'key' => '_subscription_id',
                            'value' => $db_subscription->id
                        )
                    )
                );
                
                $subscription_posts = get_posts($args);
                
                if (!empty($subscription_posts)) {
                    foreach ($subscription_posts as $post) {
                        update_post_meta($post->ID, '_status', 'active');
                    }
                }
                
                // Send payment receipt email to user
                $user_id = $db_subscription->user_id;
                $product_id = $db_subscription->product_id;
                
                $products_table = $wpdb->prefix . 'mifeco_products';
                $product = $wpdb->get_row($wpdb->prepare("SELECT name FROM $products_table WHERE id = %d", $product_id));
                
                if ($user_id && $product) {
                    $this->send_payment_receipt_email($user_id, $product->name, $invoice->amount_paid / 100);
                }
            }
        }
    }

    /**
     * Handle payment failed webhook
     */
    private function handle_payment_failed($invoice) {
        // If this is a subscription invoice, update the subscription
        if ($invoice->subscription) {
            global $wpdb;
            $subscriptions_table = $wpdb->prefix . 'mifeco_subscriptions';
            
            // Find the subscription in our database
            $db_subscription = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $subscriptions_table WHERE stripe_subscription_id = %s",
                $invoice->subscription
            ));
            
            if ($db_subscription) {
                // Update status to past_due
                $wpdb->update(
                    $subscriptions_table,
                    array('status' => 'past_due'),
                    array('id' => $db_subscription->id)
                );
                
                // Update post meta if exists
                $args = array(
                    'post_type' => 'mifeco_subscription',
                    'meta_query' => array(
                        array(
                            'key' => '_subscription_id',
                            'value' => $db_subscription->id
                        )
                    )
                );
                
                $subscription_posts = get_posts($args);
                
                if (!empty($subscription_posts)) {
                    foreach ($subscription_posts as $post) {
                        update_post_meta($post->ID, '_status', 'past_due');
                    }
                }
                
                // Send payment failed email to user
                $user_id = $db_subscription->user_id;
                $product_id = $db_subscription->product_id;
                
                $products_table = $wpdb->prefix . 'mifeco_products';
                $product = $wpdb->get_row($wpdb->prepare("SELECT name FROM $products_table WHERE id = %d", $product_id));
                
                if ($user_id && $product) {
                    $this->send_payment_failed_email($user_id, $product->name);
                }
            }
        }
    }

    /**
     * Get user ID by Stripe customer ID
     */
    private function get_user_id_by_stripe_customer($customer_id) {
        global $wpdb;
        $subscriptions_table = $wpdb->prefix . 'mifeco_subscriptions';
        
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $subscriptions_table WHERE stripe_customer_id = %s LIMIT 1",
            $customer_id
        ));
        
        return $user_id;
    }

    /**
     * Maybe create orders table
     */
    private function maybe_create_orders_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mifeco_orders';
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20),
                product_id bigint(20) NOT NULL,
                product_type varchar(20) NOT NULL,
                amount decimal(10,2) NOT NULL,
                stripe_customer_id varchar(100),
                stripe_charge_id varchar(100),
                status varchar(20) DEFAULT 'pending',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            
            // Register custom post type for orders
            register_post_type('mifeco_order', array(
                'labels' => array(
                    'name' => _x('Orders', 'post type general name', 'mifeco-suite'),
                    'singular_name' => _x('Order', 'post type singular name', 'mifeco-suite'),
                    'menu_name' => _x('Orders', 'admin menu', 'mifeco-suite'),
                ),
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_menu' => 'mifeco-suite',
                'query_var' => true,
                'capability_type' => 'post',
                'has_archive' => false,
                'hierarchical' => false,
                'supports' => array('title', 'custom-fields'),
            ));
        }
    }

    /**
     * Send subscription confirmation email
     */
    private function send_subscription_confirmation_email($user_id, $product_name, $billing_cycle) {
        $user_info = get_userdata($user_id);
        $name = $user_info->display_name;
        $email = $user_info->user_email;
        
        $settings = get_option('mifeco_suite_settings');
        $company_name = $settings['company_name'] ?? 'MIFECO';
        
        $email_templates = get_option('mifeco_email_templates');
        $subject = $email_templates['subscription_confirmation'] ?? 'Thank you for subscribing to ' . $product_name;
        $subject = str_replace(array('{product}'), array($product_name), $subject);
        
        $cycle_text = $billing_cycle === 'annual' ? 'annual' : 'monthly';
        
        $body = "Dear $name,\n\n";
        $body .= "Thank you for subscribing to $product_name!\n\n";
        $body .= "Your $cycle_text subscription is now active. You can access your subscription and software tools at any time through your account dashboard.\n\n";
        $body .= "Dashboard: " . get_permalink(get_option('mifeco_pages')['dashboard']) . "\n\n";
        $body .= "If you have any questions or need assistance, please don't hesitate to contact our support team.\n\n";
        $body .= "Best regards,\n";
        $body .= "$company_name Team\n";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($email, $subject, $body, $headers);
    }

    /**
     * Send payment receipt email
     */
    private function send_payment_receipt_email($user_id, $product_name, $amount) {
        $user_info = get_userdata($user_id);
        $name = $user_info->display_name;
        $email = $user_info->user_email;
        
        $settings = get_option('mifeco_suite_settings');
        $company_name = $settings['company_name'] ?? 'MIFECO';
        
        $subject = 'Payment Receipt for ' . $product_name;
        
        $body = "Dear $name,\n\n";
        $body .= "We've processed your payment of $" . number_format($amount, 2) . " for $product_name.\n\n";
        $body .= "Your subscription remains active and you can continue to enjoy all features and benefits.\n\n";
        $body .= "You can view your subscription details and payment history in your account dashboard:\n";
        $body .= get_permalink(get_option('mifeco_pages')['dashboard']) . "\n\n";
        $body .= "Thank you for your continued subscription.\n\n";
        $body .= "Best regards,\n";
        $body .= "$company_name Team\n";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($email, $subject, $body, $headers);
    }

    /**
     * Send payment failed email
     */
    private function send_payment_failed_email($user_id, $product_name) {
        $user_info = get_userdata($user_id);
        $name = $user_info->display_name;
        $email = $user_info->user_email;
        
        $settings = get_option('mifeco_suite_settings');
        $company_name = $settings['company_name'] ?? 'MIFECO';
        
        $subject = 'Action Required: Payment Failed for ' . $product_name;
        
        $body = "Dear $name,\n\n";
        $body .= "We were unable to process your payment for $product_name subscription.\n\n";
        $body .= "To ensure continued access to your subscription, please update your payment information as soon as possible by visiting your account dashboard:\n";
        $body .= get_permalink(get_option('mifeco_pages')['dashboard']) . "\n\n";
        $body .= "If you need any assistance, please don't hesitate to contact our support team.\n\n";
        $body .= "Best regards,\n";
        $body .= "$company_name Team\n";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($email, $subject, $body, $headers);
    }
}