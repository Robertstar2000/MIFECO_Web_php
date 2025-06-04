<?php
/**
 * Provide a admin area view for managing SaaS products
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://mifeco.com
 * @since      1.0.0
 *
 * @package    Mifeco_Suite
 * @subpackage Mifeco_Suite/admin/partials
 */

// Ensure direct file access isn't allowed
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap mifeco-saas-admin-wrap">
    <div class="mifeco-saas-admin-header">
        <h1><?php _e('SaaS Products Management', 'mifeco-suite'); ?></h1>
        
        <a href="<?php echo admin_url('admin.php?page=mifeco_saas_products&action=add'); ?>" class="button button-primary">
            <?php _e('Add New Product', 'mifeco-suite'); ?>
        </a>
    </div>
    
    <?php
    // Display settings saved message if applicable
    if (isset($_GET['settings-updated'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Product settings updated.', 'mifeco-suite') . '</p></div>';
    }
    
    // Get action from URL
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
    
    // Handle different actions
    if ($action === 'add' || $action === 'edit') {
        // Get product ID if editing
        $product_id = isset($_GET['product_id']) ? sanitize_text_field($_GET['product_id']) : '';
        
        // Get product data if editing existing product
        $product_data = array();
        if ($action === 'edit' && !empty($product_id)) {
            // In a real implementation, this would fetch product data from database
            // For demonstration, we'll use placeholder data
            $product_data = array(
                'id' => $product_id,
                'name' => 'Example Product',
                'description' => 'This is an example product description.',
                'access_level' => 'basic',
                'features' => array('Feature 1', 'Feature 2', 'Feature 3'),
                'enabled' => true
            );
        }
        
        // Display add/edit form
        ?>
        <div class="mifeco-product-form-container">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="mifeco-product-form">
                <?php wp_nonce_field('mifeco_save_product', 'mifeco_product_nonce'); ?>
                <input type="hidden" name="action" value="mifeco_save_product">
                <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                
                <div class="mifeco-form-section">
                    <h2><?php _e('Product Information', 'mifeco-suite'); ?></h2>
                    
                    <div class="mifeco-form-field">
                        <label for="product_name"><?php _e('Product Name', 'mifeco-suite'); ?></label>
                        <input type="text" id="product_name" name="product_name" value="<?php echo isset($product_data['name']) ? esc_attr($product_data['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="mifeco-form-field">
                        <label for="product_description"><?php _e('Description', 'mifeco-suite'); ?></label>
                        <textarea id="product_description" name="product_description" rows="4"><?php echo isset($product_data['description']) ? esc_textarea($product_data['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mifeco-form-field">
                        <label for="product_access_level"><?php _e('Access Level', 'mifeco-suite'); ?></label>
                        <select id="product_access_level" name="product_access_level">
                            <option value="basic" <?php selected(isset($product_data['access_level']) ? $product_data['access_level'] : '', 'basic'); ?>><?php _e('Basic Plan', 'mifeco-suite'); ?></option>
                            <option value="premium" <?php selected(isset($product_data['access_level']) ? $product_data['access_level'] : '', 'premium'); ?>><?php _e('Premium Plan', 'mifeco-suite'); ?></option>
                            <option value="enterprise" <?php selected(isset($product_data['access_level']) ? $product_data['access_level'] : '', 'enterprise'); ?>><?php _e('Enterprise Plan', 'mifeco-suite'); ?></option>
                        </select>
                    </div>
                    
                    <div class="mifeco-form-field">
                        <label for="product_enabled"><?php _e('Status', 'mifeco-suite'); ?></label>
                        <select id="product_enabled" name="product_enabled">
                            <option value="1" <?php selected(isset($product_data['enabled']) ? $product_data['enabled'] : true, true); ?>><?php _e('Enabled', 'mifeco-suite'); ?></option>
                            <option value="0" <?php selected(isset($product_data['enabled']) ? $product_data['enabled'] : true, false); ?>><?php _e('Disabled', 'mifeco-suite'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="mifeco-form-section">
                    <h2><?php _e('Product Features', 'mifeco-suite'); ?></h2>
                    
                    <div class="mifeco-plan-features-list">
                        <?php 
                        $features = isset($product_data['features']) ? $product_data['features'] : array('');
                        foreach ($features as $index => $feature) : 
                        ?>
                            <div class="mifeco-plan-feature-item">
                                <input type="text" name="product_features[]" value="<?php echo esc_attr($feature); ?>" placeholder="<?php _e('Enter feature', 'mifeco-suite'); ?>">
                                <button type="button" class="button mifeco-remove-feature"><?php _e('Remove', 'mifeco-suite'); ?></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="button" class="button mifeco-add-feature-btn"><?php _e('Add Feature', 'mifeco-suite'); ?></button>
                </div>
                
                <div class="mifeco-form-actions">
                    <button type="submit" class="button button-primary"><?php _e('Save Product', 'mifeco-suite'); ?></button>
                    <a href="<?php echo admin_url('admin.php?page=mifeco_saas_products'); ?>" class="button"><?php _e('Cancel', 'mifeco-suite'); ?></a>
                </div>
            </form>
        </div>
        <?php
    } else {
        // Display products list
        ?>
        <div class="mifeco-products-list-container">
            <table class="wp-list-table widefat fixed striped mifeco-saas-products-table">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'mifeco-suite'); ?></th>
                        <th><?php _e('Access Level', 'mifeco-suite'); ?></th>
                        <th><?php _e('Status', 'mifeco-suite'); ?></th>
                        <th><?php _e('Actions', 'mifeco-suite'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // In a real implementation, this would fetch data from database
                    // For demonstration, we'll use placeholder data
                    $products = array(
                        array(
                            'id' => 'advanced-research-tool',
                            'name' => 'Advanced Research Tool',
                            'access_level' => 'basic',
                            'enabled' => true,
                        ),
                        array(
                            'id' => 'business-problem-solver',
                            'name' => 'Ultimate Business Problem Solver',
                            'access_level' => 'premium',
                            'enabled' => true,
                        ),
                        array(
                            'id' => 'proposal-evaluation-tool',
                            'name' => 'Proposal Evaluation Tool',
                            'access_level' => 'enterprise',
                            'enabled' => true,
                        ),
                    );
                    
                    if (empty($products)) {
                        echo '<tr><td colspan="4">' . __('No products found.', 'mifeco-suite') . '</td></tr>';
                    } else {
                        foreach ($products as $product) {
                            $edit_url = admin_url('admin.php?page=mifeco_saas_products&action=edit&product_id=' . $product['id']);
                            $delete_url = admin_url('admin-post.php?action=mifeco_delete_product&product_id=' . $product['id'] . '&_wpnonce=' . wp_create_nonce('mifeco_delete_product_' . $product['id']));
                            
                            // Format access level for display
                            $access_level_display = '';
                            switch ($product['access_level']) {
                                case 'basic':
                                    $access_level_display = __('Basic Plan', 'mifeco-suite');
                                    break;
                                case 'premium':
                                    $access_level_display = __('Premium Plan', 'mifeco-suite');
                                    break;
                                case 'enterprise':
                                    $access_level_display = __('Enterprise Plan', 'mifeco-suite');
                                    break;
                                default:
                                    $access_level_display = __('Unknown', 'mifeco-suite');
                            }
                            
                            // Format status for display
                            $status_display = $product['enabled'] ? 
                                '<span class="mifeco-status-active">' . __('Active', 'mifeco-suite') . '</span>' : 
                                '<span class="mifeco-status-inactive">' . __('Inactive', 'mifeco-suite') . '</span>';
                            
                            echo '<tr>';
                            echo '<td><strong>' . esc_html($product['name']) . '</strong></td>';
                            echo '<td>' . esc_html($access_level_display) . '</td>';
                            echo '<td>' . $status_display . '</td>';
                            echo '<td class="mifeco-saas-product-actions">';
                            echo '<a href="' . esc_url($edit_url) . '" class="button button-small">' . __('Edit', 'mifeco-suite') . '</a> ';
                            
                            // For demonstration purposes, disable delete for standard products
                            if (!in_array($product['id'], array('advanced-research-tool', 'business-problem-solver', 'proposal-evaluation-tool'))) {
                                echo '<a href="' . esc_url($delete_url) . '" class="button button-small" onclick="return confirm(\'' . __('Are you sure you want to delete this product?', 'mifeco-suite') . '\')">' . __('Delete', 'mifeco-suite') . '</a>';
                            } else {
                                echo '<button class="button button-small" disabled title="' . __('Core products cannot be deleted', 'mifeco-suite') . '">' . __('Delete', 'mifeco-suite') . '</button>';
                            }
                            
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Add feature
    $('.mifeco-add-feature-btn').on('click', function() {
        var featureItem = $('<div class="mifeco-plan-feature-item"></div>');
        var featureInput = $('<input type="text" name="product_features[]" placeholder="<?php _e('Enter feature', 'mifeco-suite'); ?>">');
        var removeButton = $('<button type="button" class="button mifeco-remove-feature"><?php _e('Remove', 'mifeco-suite'); ?></button>');
        
        featureItem.append(featureInput);
        featureItem.append(removeButton);
        
        $('.mifeco-plan-features-list').append(featureItem);
    });
    
    // Remove feature
    $(document).on('click', '.mifeco-remove-feature', function() {
        $(this).parent('.mifeco-plan-feature-item').remove();
    });
});
</script>