<?php
/**
 * The main Stripe integration class.
 *
 * @link       https://mifeco.com
 * @since      1.0.0
 *
 * @package    Mifeco_Suite
 * @subpackage Mifeco_Suite/includes
 */

/**
 * The main Stripe integration class.
 *
 * This is the main class that integrates Stripe with the rest of the plugin.
 *
 * @package    Mifeco_Suite
 * @subpackage Mifeco_Suite/includes
 * @author     MIFECO <contact@mifeco.com>
 */
class Mifeco_Stripe_Integration {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Mifeco_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The Stripe main class instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Mifeco_Stripe    $stripe    The Stripe main class instance.
     */
    protected $stripe;

    /**
     * The Stripe admin class instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Mifeco_Stripe_Admin    $stripe_admin    The Stripe admin class instance.
     */
    protected $stripe_admin;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    Mifeco_Loader    $loader    The loader that's responsible for maintaining and registering all hooks.
     */
    public function __construct($loader) {
        $this->loader = $loader;
        
        // Initialize Stripe components
        $this->init_stripe_components();
        
        // Register hooks
        $this->define_hooks();
    }

    /**
     * Initialize Stripe components.
     *
     * @since    1.0.0
     * @access   private
     */
    private function init_stripe_components() {
        // Include required files
        require_once plugin_dir_path(__FILE__) . 'stripe/stripe-setup.php';
        require_once plugin_dir_path(__FILE__) . 'stripe/stripe-api-helper.php';
        require_once plugin_dir_path(__FILE__) . 'stripe/stripe-webhook-handler.php';
        require_once plugin_dir_path(__FILE__) . 'stripe/upgrade-helpers.php';
        require_once plugin_dir_path(__FILE__) . 'stripe/email-templates.php';
        require_once plugin_dir_path(__FILE__) . 'stripe/class-mifeco-stripe.php';
        require_once plugin_dir_path(__FILE__) . 'stripe/class-mifeco-stripe-admin.php';
        require_once plugin_dir_path(__FILE__) . 'class-mifeco-stripe-webhook.php';
        
        // Initialize Stripe components
        $this->stripe = new Mifeco_Stripe($this->loader);
        $this->stripe_admin = new Mifeco_Stripe_Admin($this->loader);
    }

    /**
     * Register all hooks related to Stripe integration.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_hooks() {
        // Register REST API endpoint for Stripe webhooks
        add_action('rest_api_init', array('Mifeco_Stripe_Webhook', 'register_webhook_endpoint'));
        
        // Handle plugin activation and upgrade
        add_action('mifeco_suite_upgraded', array($this, 'handle_plugin_upgrade'), 10, 2);
        
        // Schedule cron events
        add_action('mifeco_suite_activated', array($this, 'schedule_cron_events'));
        add_action('mifeco_suite_deactivated', array($this, 'unschedule_cron_events'));
        
        // Cron event handlers
        add_action('mifeco_check_subscription_expirations', 'mifeco_check_subscription_expirations');
        add_action('mifeco_sync_subscription_roles', 'mifeco_sync_subscription_roles');
    }

    /**
     * Handle plugin upgrade.
     *
     * @since    1.0.0
     * @param    string    $old_version    Previous plugin version.
     * @param    string    $new_version    New plugin version.
     */
    public function handle_plugin_upgrade($old_version, $new_version) {
        // Upgrade Stripe settings if needed
        if (function_exists('mifeco_upgrade_stripe_settings')) {
            mifeco_upgrade_stripe_settings($old_version, $new_version);
        }
    }

    /**
     * Schedule cron events.
     *
     * @since    1.0.0
     */
    public function schedule_cron_events() {
        // Schedule daily check for subscription expirations
        if (!wp_next_scheduled('mifeco_check_subscription_expirations')) {
            wp_schedule_event(time(), 'daily', 'mifeco_check_subscription_expirations');
        }
        
        // Schedule weekly sync of subscription roles
        if (!wp_next_scheduled('mifeco_sync_subscription_roles')) {
            wp_schedule_event(time(), 'weekly', 'mifeco_sync_subscription_roles');
        }
    }

    /**
     * Unschedule cron events.
     *
     * @since    1.0.0
     */
    public function unschedule_cron_events() {
        // Clear scheduled hooks
        wp_clear_scheduled_hook('mifeco_check_subscription_expirations');
        wp_clear_scheduled_hook('mifeco_sync_subscription_roles');
    }

    /**
     * Get the Stripe instance.
     *
     * @since    1.0.0
     * @return   Mifeco_Stripe    The Stripe instance.
     */
    public function get_stripe() {
        return $this->stripe;
    }

    /**
     * Get the Stripe admin instance.
     *
     * @since    1.0.0
     * @return   Mifeco_Stripe_Admin    The Stripe admin instance.
     */
    public function get_stripe_admin() {
        return $this->stripe_admin;
    }

    /**
     * Check if Stripe is properly configured.
     *
     * @since    1.0.0
     * @return   bool    Whether Stripe is properly configured.
     */
    public function is_stripe_configured() {
        return mifeco_is_stripe_configured();
    }

    /**
     * Add a notice if Stripe is not configured.
     *
     * @since    1.0.0
     */
    public function add_stripe_not_configured_notice() {
        if (!$this->is_stripe_configured() && current_user_can('manage_options')) {
            add_action('admin_notices', array($this, 'display_stripe_not_configured_notice'));
        }
    }

    /**
     * Display a notice that Stripe is not configured.
     *
     * @since    1.0.0
     */
    public function display_stripe_not_configured_notice() {
        ?>
        <div class="notice notice-warning">
            <p>
                <?php _e('MIFECO Suite: Stripe is not configured. Payment processing is disabled.', 'mifeco-suite'); ?>
                <a href="<?php echo admin_url('admin.php?page=mifeco_stripe_settings'); ?>"><?php _e('Configure Stripe', 'mifeco-suite'); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * Install Stripe dependencies if needed.
     *
     * @since    1.0.0
     * @return   bool    Whether the installation was successful.
     */
    public function maybe_install_stripe_dependencies() {
        // Check if Stripe is already available
        if (class_exists('\Stripe\Stripe')) {
            return true;
        }
        
        // Try to install Stripe library
        return mifeco_install_stripe_library();
    }
}