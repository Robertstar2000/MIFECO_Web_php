<?php
/**
 * Stripe coupon management functionality
 *
 * @link       https://mifeco.com
 * @since      1.0.0
 *
 * @package    MIFECO_Suite
 * @subpackage MIFECO_Suite/includes/stripe
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * The Stripe coupon management functionality
 *
 * Provides functionality for creating and managing Stripe coupons and promotion codes
 *
 * @package    MIFECO_Suite
 * @subpackage MIFECO_Suite/includes/stripe
 */
class MIFECO_Stripe_Coupons {

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
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Register all hooks
     */
    private function init_hooks() {
        // Add menu page
        add_action('admin_menu', array($this, 'add_coupons_menu_page'), 20);
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // AJAX handlers
        add_action('wp_ajax_mifeco_create_stripe_coupon', array($this, 'ajax_create_stripe_coupon'));
        add_action('wp_ajax_mifeco_delete_stripe_coupon', array($this, 'ajax_delete_stripe_coupon'));
        add_action('wp_ajax_mifeco_get_stripe_coupons', array($this, 'ajax_get_stripe_coupons'));
        add_action('wp_ajax_mifeco_create_stripe_promotion_code', array($this, 'ajax_create_stripe_promotion_code'));
        
        // Shortcode for coupon form
        add_shortcode('mifeco_coupon_form', array($this, 'coupon_form_shortcode'));
        
        // AJAX handler for applying coupon (frontend)
        add_action('wp_ajax_mifeco_apply_coupon', array($this, 'ajax_apply_coupon'));
    }

    /**
     * Add coupons menu page
     */
    public function add_coupons_menu_page() {
        add_submenu_page(
            'mifeco-suite',
            __('Stripe Coupons', 'mifeco-suite'),
            __('Coupons & Promotions', 'mifeco-suite'),
            'manage_options',
            'mifeco-stripe-coupons',
            array($this, 'render_coupons_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('mifeco_stripe_coupons', 'mifeco_coupon_settings');
    }

    /**
     * Render coupons page
     */
    public function render_coupons_page() {
        // Ensure Stripe API is initialized
        if (!function_exists('mifeco_init_stripe_api')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'stripe/stripe-api-helper.php';
        }
        
        $stripe_configured = mifeco_init_stripe_api();
        
        // Get settings
        $settings = get_option('mifeco_coupon_settings', array(
            'enable_coupons' => false,
            'coupon_form_title' => __('Have a coupon?', 'mifeco-suite'),
            'coupon_form_description' => __('Enter your coupon code to get a discount', 'mifeco-suite'),
            'coupon_button_text' => __('Apply Coupon', 'mifeco-suite'),
            'coupon_success_message' => __('Coupon applied successfully!', 'mifeco-suite'),
            'coupon_error_message' => __('Invalid or expired coupon code', 'mifeco-suite'),
        ));
        ?>
        <div class="wrap">
            <h1><?php _e('Stripe Coupons & Promotion Codes', 'mifeco-suite'); ?></h1>
            
            <?php if (!$stripe_configured): ?>
                <div class="notice notice-error">
                    <p><?php _e('Stripe API is not configured. Please configure it in the Stripe Settings page before creating coupons.', 'mifeco-suite'); ?></p>
                </div>
                <?php return; ?>
            <?php endif; ?>
            
            <div class="mifeco-admin-tabs">
                <nav class="nav-tab-wrapper">
                    <a href="#coupons-tab" class="nav-tab nav-tab-active"><?php _e('Coupons', 'mifeco-suite'); ?></a>
                    <a href="#promo-codes-tab" class="nav-tab"><?php _e('Promotion Codes', 'mifeco-suite'); ?></a>
                    <a href="#settings-tab" class="nav-tab"><?php _e('Settings', 'mifeco-suite'); ?></a>
                </nav>
                
                <div id="coupons-tab" class="tab-content active">
                    <h2><?php _e('Stripe Coupons', 'mifeco-suite'); ?></h2>
                    
                    <div class="mifeco-coupon-form">
                        <h3><?php _e('Create New Coupon', 'mifeco-suite'); ?></h3>
                        
                        <form id="create-coupon-form">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Coupon Name', 'mifeco-suite'); ?></th>
                                    <td>
                                        <input type="text" name="name" class="regular-text" required />
                                        <p class="description"><?php _e('A name for this coupon (e.g., "Summer Sale 2025")', 'mifeco-suite'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Discount Type', 'mifeco-suite'); ?></th>
                                    <td>
                                        <select name="discount_type" required>
                                            <option value="percentage"><?php _e('Percentage', 'mifeco-suite'); ?></option>
                                            <option value="amount"><?php _e('Fixed Amount', 'mifeco-suite'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="discount-percentage">
                                    <th scope="row"><?php _e('Percentage Off', 'mifeco-suite'); ?></th>
                                    <td>
                                        <input type="number" name="percent_off" min="1" max="100" class="small-text" required />
                                        <p class="description"><?php _e('Percentage discount (1-100)', 'mifeco-suite'); ?></p>
                                    </td>
                                </tr>
                                <tr class="discount-amount" style="display:none;">
                                    <th scope="row"><?php _e('Amount Off', 'mifeco-suite'); ?></th>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-addon">$</span>
                                            <input type="number" name="amount_off" min="1" class="small-text" />
                                        </div>
                                        <p class="description"><?php _e('Fixed amount discount in USD (e.g., 10 for $10 off)', 'mifeco-suite'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Duration', 'mifeco-suite'); ?></th>
                                    <td>
                                        <select name="duration" required>
                                            <option value="once"><?php _e('Once', 'mifeco-suite'); ?></option>
                                            <option value="repeating"><?php _e('Multiple months', 'mifeco-suite'); ?></option>
                                            <option value="forever"><?php _e('Forever', 'mifeco-suite'); ?></option>
                                        </select>
                                        <p class="description"><?php _e('How long the discount will apply', 'mifeco-suite'); ?></p>
                                    </td>
                                </tr>
                                <tr class="duration-months" style="display:none;">
                                    <th scope="row"><?php _e('Number of Months', 'mifeco-suite'); ?></th>
                                    <td>
                                        <input type="number" name="duration_in_months" min="1" class="small-text" />
                                        <p class="description"><?php _e('Number of months the discount will apply', 'mifeco-suite'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Maximum Redemptions', 'mifeco-suite'); ?></th>
                                    <td>
                                        <input type="number" name="max_redemptions" min="1" class="small-text" />
                                        <p class="description"><?php _e('Maximum number of times this coupon can be redeemed (leave empty for unlimited)', 'mifeco-suite'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Expiration Date', 'mifeco-suite'); ?></th>
                                    <td>
                                        <input type="date" name="redeem_by" class="regular-text" />
                                        <p class="description"><?php _e('Date after which the coupon can no longer be redeemed (leave empty for no expiration)', 'mifeco-suite'); ?></p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p class="submit">
                                <button type="submit" class="button button-primary"><?php _e('Create Coupon', 'mifeco-suite'); ?></button>
                                <span class="spinner" style="float:none;"></span>
                            </p>
                        </form>
                        
                        <div id="coupon-result"></div>
                    </div>
                    
                    <div class="mifeco-coupons-list">
                        <h3><?php _e('Existing Coupons', 'mifeco-suite'); ?></h3>
                        
                        <div class="tablenav top">
                            <div class="alignleft actions">
                                <button type="button" id="refresh-coupons" class="button"><?php _e('Refresh List', 'mifeco-suite'); ?></button>
                                <span class="spinner" style="float:none;"></span>
                            </div>
                        </div>
                        
                        <table class="wp-list-table widefat fixed striped coupons-table">
                            <thead>
                                <tr>
                                    <th><?php _e('ID', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Name', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Discount', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Duration', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Redemptions', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Expires', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Actions', 'mifeco-suite'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="coupons-list">
                                <tr>
                                    <td colspan="7"><?php _e('Loading coupons...', 'mifeco-suite'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div id="promo-codes-tab" class="tab-content">
                    <h2><?php _e('Stripe Promotion Codes', 'mifeco-suite'); ?></h2>
                    
                    <div class="mifeco-promo-form">
                        <h3><?php _e('Create New Promotion Code', 'mifeco-suite'); ?></h3>
                        
                        <form id="create-promo-form">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Coupon', 'mifeco-suite'); ?></th>
                                    <td>
                                        <select name="coupon_id" class="regular-text" required>
                                            <option value=""><?php _e('Select a coupon', 'mifeco-suite'); ?></option>
                                            <!-- Coupons will be populated via JavaScript -->
                                        </select>
                                        <p class="description"><?php _e('Select the coupon this promotion code will apply', 'mifeco-suite'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Promotion Code', 'mifeco-suite'); ?></th>
                                    <td>
                                        <input type="text" name="code" class="regular-text" required />
                                        <p class="description"><?php _e('The code that users will enter to redeem the coupon (e.g., "SUMMER25")', 'mifeco-suite'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Maximum Redemptions', 'mifeco-suite'); ?></th>
                                    <td>
                                        <input type="number" name="max_redemptions" min="1" class="small-text" />
                                        <p class="description"><?php _e('Maximum number of times this promotion code can be redeemed (leave empty for unlimited)', 'mifeco-suite'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Expiration Date', 'mifeco-suite'); ?></th>
                                    <td>
                                        <input type="date" name="expires_at" class="regular-text" />
                                        <p class="description"><?php _e('Date after which the promotion code can no longer be redeemed (leave empty for no expiration)', 'mifeco-suite'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Active', 'mifeco-suite'); ?></th>
                                    <td>
                                        <input type="checkbox" name="active" value="1" checked />
                                        <p class="description"><?php _e('Whether this promotion code is currently active', 'mifeco-suite'); ?></p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p class="submit">
                                <button type="submit" class="button button-primary"><?php _e('Create Promotion Code', 'mifeco-suite'); ?></button>
                                <span class="spinner" style="float:none;"></span>
                            </p>
                        </form>
                        
                        <div id="promo-result"></div>
                    </div>
                    
                    <div class="mifeco-promos-list">
                        <h3><?php _e('Existing Promotion Codes', 'mifeco-suite'); ?></h3>
                        
                        <div class="tablenav top">
                            <div class="alignleft actions">
                                <button type="button" id="refresh-promos" class="button"><?php _e('Refresh List', 'mifeco-suite'); ?></button>
                                <span class="spinner" style="float:none;"></span>
                            </div>
                        </div>
                        
                        <table class="wp-list-table widefat fixed striped promos-table">
                            <thead>
                                <tr>
                                    <th><?php _e('ID', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Code', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Coupon', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Active', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Redemptions', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Expires', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Actions', 'mifeco-suite'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="promos-list">
                                <tr>
                                    <td colspan="7"><?php _e('Loading promotion codes...', 'mifeco-suite'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div id="settings-tab" class="tab-content">
                    <h2><?php _e('Coupon Settings', 'mifeco-suite'); ?></h2>
                    
                    <form method="post" action="options.php">
                        <?php settings_fields('mifeco_stripe_coupons'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Enable Coupons', 'mifeco-suite'); ?></th>
                                <td>
                                    <input type="checkbox" name="mifeco_coupon_settings[enable_coupons]" value="1" <?php checked($settings['enable_coupons'], true); ?> />
                                    <p class="description"><?php _e('Enable coupon functionality on the frontend', 'mifeco-suite'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Coupon Form Title', 'mifeco-suite'); ?></th>
                                <td>
                                    <input type="text" name="mifeco_coupon_settings[coupon_form_title]" value="<?php echo esc_attr($settings['coupon_form_title']); ?>" class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Coupon Form Description', 'mifeco-suite'); ?></th>
                                <td>
                                    <textarea name="mifeco_coupon_settings[coupon_form_description]" class="large-text" rows="3"><?php echo esc_textarea($settings['coupon_form_description']); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Coupon Button Text', 'mifeco-suite'); ?></th>
                                <td>
                                    <input type="text" name="mifeco_coupon_settings[coupon_button_text]" value="<?php echo esc_attr($settings['coupon_button_text']); ?>" class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Success Message', 'mifeco-suite'); ?></th>
                                <td>
                                    <input type="text" name="mifeco_coupon_settings[coupon_success_message]" value="<?php echo esc_attr($settings['coupon_success_message']); ?>" class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Error Message', 'mifeco-suite'); ?></th>
                                <td>
                                    <input type="text" name="mifeco_coupon_settings[coupon_error_message]" value="<?php echo esc_attr($settings['coupon_error_message']); ?>" class="regular-text" />
                                </td>
                            </tr>
                        </table>
                        
                        <?php submit_button(); ?>
                    </form>
                    
                    <h3><?php _e('Shortcode', 'mifeco-suite'); ?></h3>
                    <p><?php _e('Use the following shortcode to display the coupon form on any page:', 'mifeco-suite'); ?></p>
                    <code>[mifeco_coupon_form]</code>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Tab Navigation
            $('.mifeco-admin-tabs .nav-tab').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                
                // Update tabs
                $('.mifeco-admin-tabs .nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                // Update content
                $('.tab-content').removeClass('active');
                $(target).addClass('active');
            });
            
            // Toggle discount type fields
            $('select[name="discount_type"]').on('change', function() {
                if ($(this).val() === 'percentage') {
                    $('.discount-percentage').show();
                    $('.discount-amount').hide();
                } else {
                    $('.discount-percentage').hide();
                    $('.discount-amount').show();
                }
            });
            
            // Toggle duration fields
            $('select[name="duration"]').on('change', function() {
                if ($(this).val() === 'repeating') {
                    $('.duration-months').show();
                } else {
                    $('.duration-months').hide();
                }
            });
            
            // Load coupons
            function loadCoupons() {
                var spinnerElement = $('#refresh-coupons').next('.spinner');
                spinnerElement.addClass('is-active');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mifeco_get_stripe_coupons',
                        nonce: '<?php echo wp_create_nonce('mifeco_stripe_coupons'); ?>'
                    },
                    success: function(response) {
                        spinnerElement.removeClass('is-active');
                        
                        if (response.success && response.data.coupons) {
                            var coupons = response.data.coupons;
                            var html = '';
                            
                            if (coupons.length === 0) {
                                html = '<tr><td colspan="7"><?php _e('No coupons found.', 'mifeco-suite'); ?></td></tr>';
                            } else {
                                $.each(coupons, function(i, coupon) {
                                    html += '<tr>';
                                    html += '<td>' + coupon.id + '</td>';
                                    html += '<td>' + (coupon.name || '-') + '</td>';
                                    
                                    if (coupon.percent_off) {
                                        html += '<td>' + coupon.percent_off + '%</td>';
                                    } else if (coupon.amount_off) {
                                        html += '<td>$' + (coupon.amount_off / 100) + '</td>';
                                    } else {
                                        html += '<td>-</td>';
                                    }
                                    
                                    var duration = coupon.duration;
                                    if (duration === 'repeating' && coupon.duration_in_months) {
                                        duration += ' (' + coupon.duration_in_months + ' months)';
                                    }
                                    html += '<td>' + duration + '</td>';
                                    
                                    html += '<td>' + (coupon.times_redeemed || 0) + (coupon.max_redemptions ? '/' + coupon.max_redemptions : '') + '</td>';
                                    
                                    if (coupon.redeem_by) {
                                        var date = new Date(coupon.redeem_by * 1000);
                                        html += '<td>' + date.toLocaleDateString() + '</td>';
                                    } else {
                                        html += '<td>-</td>';
                                    }
                                    
                                    html += '<td>';
                                    html += '<button type="button" class="button delete-coupon" data-id="' + coupon.id + '"><?php _e('Delete', 'mifeco-suite'); ?></button>';
                                    html += '</td>';
                                    
                                    html += '</tr>';
                                });
                            }
                            
                            $('#coupons-list').html(html);
                            
                            // Update select in promo form
                            var selectHtml = '<option value=""><?php _e('Select a coupon', 'mifeco-suite'); ?></option>';
                            $.each(coupons, function(i, coupon) {
                                var label = coupon.name || coupon.id;
                                label += ' - ';
                                
                                if (coupon.percent_off) {
                                    label += coupon.percent_off + '%';
                                } else if (coupon.amount_off) {
                                    label += '$' + (coupon.amount_off / 100);
                                }
                                
                                selectHtml += '<option value="' + coupon.id + '">' + label + '</option>';
                            });
                            
                            $('select[name="coupon_id"]').html(selectHtml);
                        } else {
                            $('#coupons-list').html('<tr><td colspan="7"><?php _e('Error loading coupons.', 'mifeco-suite'); ?></td></tr>');
                        }
                    },
                    error: function() {
                        spinnerElement.removeClass('is-active');
                        $('#coupons-list').html('<tr><td colspan="7"><?php _e('Error loading coupons.', 'mifeco-suite'); ?></td></tr>');
                    }
                });
            }
            
            // Load promotion codes
            function loadPromotionCodes() {
                var spinnerElement = $('#refresh-promos').next('.spinner');
                spinnerElement.addClass('is-active');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mifeco_get_stripe_promotion_codes',
                        nonce: '<?php echo wp_create_nonce('mifeco_stripe_coupons'); ?>'
                    },
                    success: function(response) {
                        spinnerElement.removeClass('is-active');
                        
                        if (response.success && response.data.promotion_codes) {
                            var promos = response.data.promotion_codes;
                            var html = '';
                            
                            if (promos.length === 0) {
                                html = '<tr><td colspan="7"><?php _e('No promotion codes found.', 'mifeco-suite'); ?></td></tr>';
                            } else {
                                $.each(promos, function(i, promo) {
                                    html += '<tr>';
                                    html += '<td>' + promo.id + '</td>';
                                    html += '<td>' + promo.code + '</td>';
                                    html += '<td>' + (promo.coupon.name || promo.coupon.id) + '</td>';
                                    html += '<td>' + (promo.active ? '<?php _e('Yes', 'mifeco-suite'); ?>' : '<?php _e('No', 'mifeco-suite'); ?>') + '</td>';
                                    html += '<td>' + (promo.times_redeemed || 0) + (promo.max_redemptions ? '/' + promo.max_redemptions : '') + '</td>';
                                    
                                    if (promo.expires_at) {
                                        var date = new Date(promo.expires_at * 1000);
                                        html += '<td>' + date.toLocaleDateString() + '</td>';
                                    } else {
                                        html += '<td>-</td>';
                                    }
                                    
                                    html += '<td>';
                                    html += '<button type="button" class="button update-promo" data-id="' + promo.id + '" data-active="' + promo.active + '">' + (promo.active ? '<?php _e('Deactivate', 'mifeco-suite'); ?>' : '<?php _e('Activate', 'mifeco-suite'); ?>') + '</button>';
                                    html += '</td>';
                                    
                                    html += '</tr>';
                                });
                            }
                            
                            $('#promos-list').html(html);
                        } else {
                            $('#promos-list').html('<tr><td colspan="7"><?php _e('Error loading promotion codes.', 'mifeco-suite'); ?></td></tr>');
                        }
                    },
                    error: function() {
                        spinnerElement.removeClass('is-active');
                        $('#promos-list').html('<tr><td colspan="7"><?php _e('Error loading promotion codes.', 'mifeco-suite'); ?></td></tr>');
                    }
                });
            }
            
            // Create coupon
            $('#create-coupon-form').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var submitButton = form.find('button[type="submit"]');
                var spinner = form.find('.spinner');
                var resultDiv = $('#coupon-result');
                
                // Disable button and show spinner
                submitButton.prop('disabled', true);
                spinner.addClass('is-active');
                resultDiv.html('');
                
                // Get form data
                var formData = {
                    action: 'mifeco_create_stripe_coupon',
                    nonce: '<?php echo wp_create_nonce('mifeco_stripe_coupons'); ?>',
                    name: form.find('input[name="name"]').val(),
                    discount_type: form.find('select[name="discount_type"]').val(),
                    percent_off: form.find('input[name="percent_off"]').val(),
                    amount_off: form.find('input[name="amount_off"]').val(),
                    duration: form.find('select[name="duration"]').val(),
                    duration_in_months: form.find('input[name="duration_in_months"]').val(),
                    max_redemptions: form.find('input[name="max_redemptions"]').val(),
                    redeem_by: form.find('input[name="redeem_by"]').val()
                };
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        submitButton.prop('disabled', false);
                        spinner.removeClass('is-active');
                        
                        if (response.success) {
                            resultDiv.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                            form.trigger('reset');
                            loadCoupons();
                        } else {
                            resultDiv.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        submitButton.prop('disabled', false);
                        spinner.removeClass('is-active');
                        resultDiv.html('<div class="notice notice-error"><p><?php _e('An error occurred. Please try again.', 'mifeco-suite'); ?></p></div>');
                    }
                });
            });
            
            // Delete coupon
            $(document).on('click', '.delete-coupon', function() {
                if (!confirm('<?php _e('Are you sure you want to delete this coupon? This action cannot be undone.', 'mifeco-suite'); ?>')) {
                    return;
                }
                
                var button = $(this);
                var couponId = button.data('id');
                var row = button.closest('tr');
                
                button.prop('disabled', true).text('<?php _e('Deleting...', 'mifeco-suite'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mifeco_delete_stripe_coupon',
                        nonce: '<?php echo wp_create_nonce('mifeco_stripe_coupons'); ?>',
                        coupon_id: couponId
                    },
                    success: function(response) {
                        if (response.success) {
                            row.fadeOut(400, function() {
                                row.remove();
                                
                                if ($('#coupons-list tr').length === 0) {
                                    $('#coupons-list').html('<tr><td colspan="7"><?php _e('No coupons found.', 'mifeco-suite'); ?></td></tr>');
                                }
                                
                                loadCoupons();
                            });
                        } else {
                            alert(response.data.message);
                            button.prop('disabled', false).text('<?php _e('Delete', 'mifeco-suite'); ?>');
                        }
                    },
                    error: function() {
                        alert('<?php _e('An error occurred. Please try again.', 'mifeco-suite'); ?>');
                        button.prop('disabled', false).text('<?php _e('Delete', 'mifeco-suite'); ?>');
                    }
                });
            });
            
            // Create promotion code
            $('#create-promo-form').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var submitButton = form.find('button[type="submit"]');
                var spinner = form.find('.spinner');
                var resultDiv = $('#promo-result');
                
                // Disable button and show spinner
                submitButton.prop('disabled', true);
                spinner.addClass('is-active');
                resultDiv.html('');
                
                // Get form data
                var formData = {
                    action: 'mifeco_create_stripe_promotion_code',
                    nonce: '<?php echo wp_create_nonce('mifeco_stripe_coupons'); ?>',
                    coupon_id: form.find('select[name="coupon_id"]').val(),
                    code: form.find('input[name="code"]').val(),
                    max_redemptions: form.find('input[name="max_redemptions"]').val(),
                    expires_at: form.find('input[name="expires_at"]').val(),
                    active: form.find('input[name="active"]').is(':checked') ? 1 : 0
                };
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        submitButton.prop('disabled', false);
                        spinner.removeClass('is-active');
                        
                        if (response.success) {
                            resultDiv.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                            form.trigger('reset');
                            loadPromotionCodes();
                        } else {
                            resultDiv.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        submitButton.prop('disabled', false);
                        spinner.removeClass('is-active');
                        resultDiv.html('<div class="notice notice-error"><p><?php _e('An error occurred. Please try again.', 'mifeco-suite'); ?></p></div>');
                    }
                });
            });
            
            // Update promotion code (toggle active status)
            $(document).on('click', '.update-promo', function() {
                var button = $(this);
                var promoId = button.data('id');
                var isActive = button.data('active');
                var newStatus = isActive ? 0 : 1;
                
                button.prop('disabled', true).text('<?php _e('Updating...', 'mifeco-suite'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mifeco_update_stripe_promotion_code',
                        nonce: '<?php echo wp_create_nonce('mifeco_stripe_coupons'); ?>',
                        promotion_code_id: promoId,
                        active: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            loadPromotionCodes();
                        } else {
                            alert(response.data.message);
                            button.prop('disabled', false).text(isActive ? '<?php _e('Deactivate', 'mifeco-suite'); ?>' : '<?php _e('Activate', 'mifeco-suite'); ?>');
                        }
                    },
                    error: function() {
                        alert('<?php _e('An error occurred. Please try again.', 'mifeco-suite'); ?>');
                        button.prop('disabled', false).text(isActive ? '<?php _e('Deactivate', 'mifeco-suite'); ?>' : '<?php _e('Activate', 'mifeco-suite'); ?>');
                    }
                });
            });
            
            // Refresh button actions
            $('#refresh-coupons').on('click', loadCoupons);
            $('#refresh-promos').on('click', loadPromotionCodes);
            
            // Initial load
            loadCoupons();
            loadPromotionCodes();
        });
        </script>
        <style>
        .mifeco-admin-tabs .tab-content {
            display: none;
            margin-top: 20px;
        }
        
        .mifeco-admin-tabs .tab-content.active {
            display: block;
        }
        
        .mifeco-coupon-form,
        .mifeco-promo-form {
            background-color: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
            padding: 15px;
            margin-bottom: 20px;
        }
        
        #coupon-result,
        #promo-result {
            margin-top: 15px;
        }
        
        .spinner.is-active {
            visibility: visible;
        }
        
        .input-group {
            display: flex;
            align-items: center;
        }
        
        .input-group-addon {
            padding: 6px 12px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-right: 0;
        }
        
        .input-group input {
            margin: 0;
        }
        </style>
        <?php
    }

    /**
     * AJAX: Create Stripe coupon
     */
    public function ajax_create_stripe_coupon() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mifeco_stripe_coupons')) {
            wp_send_json_error(['message' => __('Security check failed.', 'mifeco-suite')]);
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'mifeco-suite')]);
        }
        
        // Validate required fields
        if (empty($_POST['name']) || empty($_POST['discount_type']) || empty($_POST['duration'])) {
            wp_send_json_error(['message' => __('Required fields are missing.', 'mifeco-suite')]);
        }
        
        // Get discount values
        $discount_type = sanitize_text_field($_POST['discount_type']);
        $percent_off = null;
        $amount_off = null;
        
        if ($discount_type === 'percentage') {
            $percent_off = intval($_POST['percent_off']);
            if ($percent_off < 1 || $percent_off > 100) {
                wp_send_json_error(['message' => __('Percentage discount must be between 1 and 100.', 'mifeco-suite')]);
            }
        } else {
            $amount_off = floatval($_POST['amount_off']) * 100; // Convert to cents
            if ($amount_off < 1) {
                wp_send_json_error(['message' => __('Amount discount must be greater than 0.', 'mifeco-suite')]);
            }
        }
        
        // Get duration values
        $duration = sanitize_text_field($_POST['duration']);
        $duration_in_months = null;
        
        if ($duration === 'repeating') {
            $duration_in_months = intval($_POST['duration_in_months']);
            if ($duration_in_months < 1) {
                wp_send_json_error(['message' => __('Duration in months must be at least 1.', 'mifeco-suite')]);
            }
        }
        
        // Get optional values
        $max_redemptions = !empty($_POST['max_redemptions']) ? intval($_POST['max_redemptions']) : null;
        $redeem_by = !empty($_POST['redeem_by']) ? strtotime($_POST['redeem_by']) : null;
        
        // Create coupon
        try {
            // Ensure Stripe API is initialized
            if (!function_exists('mifeco_init_stripe_api')) {
                require_once plugin_dir_path(dirname(__FILE__)) . 'stripe/stripe-api-helper.php';
            }
            
            if (!mifeco_init_stripe_api()) {
                wp_send_json_error(['message' => __('Failed to initialize Stripe API.', 'mifeco-suite')]);
            }
            
            // Prepare coupon data
            $coupon_data = [
                'name' => sanitize_text_field($_POST['name']),
                'duration' => $duration
            ];
            
            if ($percent_off !== null) {
                $coupon_data['percent_off'] = $percent_off;
            } else {
                $coupon_data['amount_off'] = $amount_off;
                $coupon_data['currency'] = 'usd';
            }
            
            if ($duration_in_months !== null) {
                $coupon_data['duration_in_months'] = $duration_in_months;
            }
            
            if ($max_redemptions !== null) {
                $coupon_data['max_redemptions'] = $max_redemptions;
            }
            
            if ($redeem_by !== null) {
                $coupon_data['redeem_by'] = $redeem_by;
            }
            
            // Create coupon
            $coupon = \Stripe\Coupon::create($coupon_data);
            
            wp_send_json_success([
                'message' => __('Coupon created successfully!', 'mifeco-suite'),
                'coupon_id' => $coupon->id
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Delete Stripe coupon
     */
    public function ajax_delete_stripe_coupon() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mifeco_stripe_coupons')) {
            wp_send_json_error(['message' => __('Security check failed.', 'mifeco-suite')]);
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'mifeco-suite')]);
        }
        
        // Validate required fields
        if (empty($_POST['coupon_id'])) {
            wp_send_json_error(['message' => __('Coupon ID is required.', 'mifeco-suite')]);
        }
        
        $coupon_id = sanitize_text_field($_POST['coupon_id']);
        
        // Delete coupon
        try {
            // Ensure Stripe API is initialized
            if (!function_exists('mifeco_init_stripe_api')) {
                require_once plugin_dir_path(dirname(__FILE__)) . 'stripe/stripe-api-helper.php';
            }
            
            if (!mifeco_init_stripe_api()) {
                wp_send_json_error(['message' => __('Failed to initialize Stripe API.', 'mifeco-suite')]);
            }
            
            $coupon = \Stripe\Coupon::retrieve($coupon_id);
            $coupon->delete();
            
            wp_send_json_success(['message' => __('Coupon deleted successfully!', 'mifeco-suite')]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Get Stripe coupons
     */
    public function ajax_get_stripe_coupons() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mifeco_stripe_coupons')) {
            wp_send_json_error(['message' => __('Security check failed.', 'mifeco-suite')]);
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'mifeco-suite')]);
        }
        
        // Get coupons
        try {
            // Ensure Stripe API is initialized
            if (!function_exists('mifeco_init_stripe_api')) {
                require_once plugin_dir_path(dirname(__FILE__)) . 'stripe/stripe-api-helper.php';
            }
            
            if (!mifeco_init_stripe_api()) {
                wp_send_json_error(['message' => __('Failed to initialize Stripe API.', 'mifeco-suite')]);
            }
            
            $coupons = \Stripe\Coupon::all(['limit' => 100]);
            
            wp_send_json_success(['coupons' => $coupons->data]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Create Stripe promotion code
     */
    public function ajax_create_stripe_promotion_code() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mifeco_stripe_coupons')) {
            wp_send_json_error(['message' => __('Security check failed.', 'mifeco-suite')]);
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'mifeco-suite')]);
        }
        
        // Validate required fields
        if (empty($_POST['coupon_id']) || empty($_POST['code'])) {
            wp_send_json_error(['message' => __('Required fields are missing.', 'mifeco-suite')]);
        }
        
        $coupon_id = sanitize_text_field($_POST['coupon_id']);
        $code = strtoupper(sanitize_text_field($_POST['code']));
        
        // Get optional values
        $max_redemptions = !empty($_POST['max_redemptions']) ? intval($_POST['max_redemptions']) : null;
        $expires_at = !empty($_POST['expires_at']) ? strtotime($_POST['expires_at']) : null;
        $active = isset($_POST['active']) ? (bool) $_POST['active'] : true;
        
        // Create promotion code
        try {
            // Ensure Stripe API is initialized
            if (!function_exists('mifeco_init_stripe_api')) {
                require_once plugin_dir_path(dirname(__FILE__)) . 'stripe/stripe-api-helper.php';
            }
            
            if (!mifeco_init_stripe_api()) {
                wp_send_json_error(['message' => __('Failed to initialize Stripe API.', 'mifeco-suite')]);
            }
            
            // Prepare promotion code data
            $promo_data = [
                'coupon' => $coupon_id,
                'code' => $code,
                'active' => $active
            ];
            
            if ($max_redemptions !== null) {
                $promo_data['max_redemptions'] = $max_redemptions;
            }
            
            if ($expires_at !== null) {
                $promo_data['expires_at'] = $expires_at;
            }
            
            // Create promotion code
            $promotion_code = \Stripe\PromotionCode::create($promo_data);
            
            wp_send_json_success([
                'message' => __('Promotion code created successfully!', 'mifeco-suite'),
                'promotion_code_id' => $promotion_code->id
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Apply coupon
     */
    public function ajax_apply_coupon() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mifeco_apply_coupon')) {
            wp_send_json_error(['message' => __('Security check failed.', 'mifeco-suite')]);
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('You must be logged in to apply a coupon.', 'mifeco-suite')]);
        }
        
        // Validate required fields
        if (empty($_POST['code'])) {
            wp_send_json_error(['message' => __('Coupon code is required.', 'mifeco-suite')]);
        }
        
        $code = sanitize_text_field($_POST['code']);
        $user_id = get_current_user_id();
        
        // Check for subscription ID
        $subscription_id = get_user_meta($user_id, 'mifeco_stripe_subscription_id', true);
        if (empty($subscription_id)) {
            wp_send_json_error(['message' => __('You must have an active subscription to apply a coupon.', 'mifeco-suite')]);
        }
        
        // Apply promotion code to subscription
        try {
            // Ensure Stripe API is initialized
            if (!function_exists('mifeco_init_stripe_api')) {
                require_once plugin_dir_path(dirname(__FILE__)) . 'stripe/stripe-api-helper.php';
            }
            
            if (!mifeco_init_stripe_api()) {
                wp_send_json_error(['message' => __('Failed to initialize Stripe API.', 'mifeco-suite')]);
            }
            
            // Get the promotion code
            $promotion_codes = \Stripe\PromotionCode::all([
                'code' => $code,
                'active' => true,
                'limit' => 1
            ]);
            
            if (empty($promotion_codes->data)) {
                wp_send_json_error(['message' => __('Invalid or expired coupon code.', 'mifeco-suite')]);
            }
            
            $promotion_code = $promotion_codes->data[0];
            
            // Apply to subscription
            \Stripe\Subscription::update($subscription_id, [
                'promotion_code' => $promotion_code->id,
            ]);
            
            // Get settings
            $settings = get_option('mifeco_coupon_settings', []);
            $success_message = !empty($settings['coupon_success_message']) ? $settings['coupon_success_message'] : __('Coupon applied successfully!', 'mifeco-suite');
            
            // Get discount details
            $coupon = $promotion_code->coupon;
            $discount_html = '';
            
            if ($coupon->percent_off) {
                $discount_html = sprintf(__('You received a %d%% discount.', 'mifeco-suite'), $coupon->percent_off);
            } elseif ($coupon->amount_off) {
                $discount_html = sprintf(__('You received a $%s discount.', 'mifeco-suite'), number_format($coupon->amount_off / 100, 2));
            }
            
            wp_send_json_success([
                'message' => $success_message,
                'discount' => $discount_html
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Coupon form shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function coupon_form_shortcode($atts) {
        // Get settings
        $settings = get_option('mifeco_coupon_settings', []);
        
        // Check if coupons are enabled
        if (empty($settings['enable_coupons'])) {
            return '';
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '';
        }
        
        // Check if user has a subscription
        $user_id = get_current_user_id();
        $subscription_id = get_user_meta($user_id, 'mifeco_stripe_subscription_id', true);
        
        if (empty($subscription_id)) {
            return '';
        }
        
        // Get text strings
        $title = !empty($settings['coupon_form_title']) ? $settings['coupon_form_title'] : __('Have a coupon?', 'mifeco-suite');
        $description = !empty($settings['coupon_form_description']) ? $settings['coupon_form_description'] : __('Enter your coupon code to get a discount', 'mifeco-suite');
        $button_text = !empty($settings['coupon_button_text']) ? $settings['coupon_button_text'] : __('Apply Coupon', 'mifeco-suite');
        
        // Build form HTML
        ob_start();
        ?>
        <div class="mifeco-coupon-form">
            <h3><?php echo esc_html($title); ?></h3>
            <p><?php echo esc_html($description); ?></p>
            
            <form id="mifeco-apply-coupon-form">
                <div class="mifeco-form-row">
                    <input type="text" name="coupon_code" id="mifeco-coupon-code" placeholder="<?php esc_attr_e('Coupon code', 'mifeco-suite'); ?>" required />
                    <button type="submit" class="mifeco-btn"><?php echo esc_html($button_text); ?></button>
                </div>
                <div class="mifeco-coupon-message"></div>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#mifeco-apply-coupon-form').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var submitButton = form.find('button[type="submit"]');
                var messageDiv = form.find('.mifeco-coupon-message');
                var couponCode = $('#mifeco-coupon-code').val();
                
                // Disable button and clear message
                submitButton.prop('disabled', true).text('<?php esc_attr_e('Applying...', 'mifeco-suite'); ?>');
                messageDiv.html('');
                
                // Send AJAX request
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'mifeco_apply_coupon',
                        nonce: '<?php echo wp_create_nonce('mifeco_apply_coupon'); ?>',
                        code: couponCode
                    },
                    success: function(response) {
                        submitButton.prop('disabled', false).text('<?php echo esc_js($button_text); ?>');
                        
                        if (response.success) {
                            messageDiv.html('<div class="mifeco-coupon-success">' + response.data.message + ' ' + response.data.discount + '</div>');
                            form.find('input').val('');
                        } else {
                            messageDiv.html('<div class="mifeco-coupon-error">' + response.data.message + '</div>');
                        }
                    },
                    error: function() {
                        submitButton.prop('disabled', false).text('<?php echo esc_js($button_text); ?>');
                        messageDiv.html('<div class="mifeco-coupon-error"><?php esc_attr_e('An error occurred. Please try again.', 'mifeco-suite'); ?></div>');
                    }
                });
            });
        });
        </script>
        
        <style>
        .mifeco-coupon-form {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        .mifeco-coupon-form h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .mifeco-form-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .mifeco-form-row input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .mifeco-coupon-message {
            margin-top: 10px;
        }
        
        .mifeco-coupon-success {
            color: #0f5132;
            background-color: #d1e7dd;
            padding: 10px;
            border-radius: 4px;
        }
        
        .mifeco-coupon-error {
            color: #842029;
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 4px;
        }
        </style>
        <?php
        
        return ob_get_clean();
    }
}