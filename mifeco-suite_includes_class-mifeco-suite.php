<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    MIFECO_Suite
 */

class MIFECO_Suite {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     *
     * @since    1.0.0
     * @access   protected
     * @var      MIFECO_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('MIFECO_VERSION')) {
            $this->version = MIFECO_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'mifeco-suite';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_lead_generation_hooks();
        $this->define_saas_hooks();
        $this->define_stripe_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the core plugin.
         */
        require_once MIFECO_PLUGIN_DIR . 'includes/class-mifeco-loader.php';

        /**
         * The class responsible for defining all actions for admin area.
         */
        require_once MIFECO_PLUGIN_DIR . 'admin/class-mifeco-admin.php';

        /**
         * The class responsible for defining all actions for public-facing side.
         */
        require_once MIFECO_PLUGIN_DIR . 'public/class-mifeco-public.php';

        /**
         * The class responsible for handling lead generation functionality.
         */
        require_once MIFECO_PLUGIN_DIR . 'includes/lead-generation/class-mifeco-lead-generation.php';

        /**
         * The class responsible for handling SaaS functionality.
         */
        require_once MIFECO_PLUGIN_DIR . 'includes/saas/class-mifeco-saas.php';

        /**
         * The class responsible for Stripe integration.
         */
        require_once MIFECO_PLUGIN_DIR . 'includes/stripe/class-mifeco-stripe.php';

        $this->loader = new MIFECO_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new MIFECO_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new MIFECO_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');
    }

    /**
     * Register all of the hooks related to lead generation functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_lead_generation_hooks() {
        $lead_generation = new MIFECO_Lead_Generation($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('init', $lead_generation, 'register_lead_post_type');
        $this->loader->add_action('init', $lead_generation, 'register_consultation_post_type');
        $this->loader->add_action('wp_ajax_mifeco_submit_contact_form', $lead_generation, 'process_contact_form');
        $this->loader->add_action('wp_ajax_nopriv_mifeco_submit_contact_form', $lead_generation, 'process_contact_form');
        $this->loader->add_action('wp_ajax_mifeco_book_consultation', $lead_generation, 'process_consultation_booking');
        $this->loader->add_action('wp_ajax_nopriv_mifeco_book_consultation', $lead_generation, 'process_consultation_booking');
    }

    /**
     * Register all of the hooks related to SaaS functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_saas_hooks() {
        $saas = new MIFECO_SaaS($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('init', $saas, 'register_product_post_type');
        $this->loader->add_action('init', $saas, 'register_subscription_post_type');
        $this->loader->add_action('wp_ajax_mifeco_start_trial', $saas, 'process_trial_signup');
        $this->loader->add_action('wp_ajax_nopriv_mifeco_start_trial', $saas, 'process_trial_signup');
        $this->loader->add_action('wp_ajax_mifeco_user_dashboard', $saas, 'render_user_dashboard');
    }

    /**
     * Register all of the hooks related to Stripe integration.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_stripe_hooks() {
        $stripe = new MIFECO_Stripe($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('rest_api_init', $stripe, 'register_stripe_endpoints');
        $this->loader->add_action('wp_ajax_mifeco_process_payment', $stripe, 'process_payment');
        $this->loader->add_action('wp_ajax_nopriv_mifeco_process_payment', $stripe, 'process_payment');
        $this->loader->add_action('wp_ajax_mifeco_create_subscription', $stripe, 'create_subscription');
        $this->loader->add_action('wp_ajax_mifeco_update_payment_method', $stripe, 'update_payment_method');
        $this->loader->add_action('wp_ajax_mifeco_cancel_subscription', $stripe, 'cancel_subscription');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    MIFECO_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}