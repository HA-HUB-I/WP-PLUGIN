<?php
/*
Plugin Name: Fake Store Sync
Description: Синхронизира продукти от FakeStore API.
Version: 1.0
Author: име
*/

// Register hooks for activation and deactivation
register_activation_hook(__FILE__, 'fake_store_sync_activate');
register_deactivation_hook(__FILE__, 'fake_store_sync_deactivate');

// Add menu item in the admin panel
add_action('admin_menu', 'my_plugin_menu');

// Initialize the plugin
function fake_store_sync_init() {
    // Initialization code can go here
}
add_action('init', 'fake_store_sync_init');

// Function to handle activation
function fake_store_sync_activate() {
    // Activation code can go here
}

// Function to handle deactivation
function fake_store_sync_deactivate() {
    // Deactivation code can go here
}

// Add menu item in the admin panel
function my_plugin_menu() {
    add_menu_page(
        'Синхронизация на Продукти',
        'Синхронизация',
        'manage_options',
        'fake_store_sync',
        'my_plugin_settings_page'
    );
}

// Admin page content
function my_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h1>Синхронизация на Продукти</h1>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('fake_store_sync', 'fake_store_sync_nonce'); ?>
            <input type="hidden" name="action" value="fake_store_sync">
            <p>
                <input type="submit" name="submit" class="button-primary" value="Синхронизирай Продукти">
            </p>
        </form>
    </div>
    <?php
}

// Handle form submission
add_action('admin_post_fake_store_sync', 'fake_store_sync_handle_submit');

function fake_store_sync_handle_submit() {
    // Verify nonce
    if (!isset($_POST['fake_store_sync_nonce']) || !wp_verify_nonce($_POST['fake_store_sync_nonce'], 'fake_store_sync')) {
        wp_die(__('Nonce verification failed', 'fake-store-sync'));
    }

    // Call the function to update products
    fake_store_sync_update_products();

    // Redirect back to the settings page
    wp_redirect(admin_url('admin.php?page=fake_store_sync'));
    exit;
}

// Fetch products from FakeStore API and update WooCommerce products
function fake_store_sync_update_products() {
    // HTTP request to FakeStore API
    $response = wp_remote_get('https://fakestoreapi.com/products');

    if (is_wp_error($response)) {
        // Handle error
        return;
    }

    $products = json_decode(wp_remote_retrieve_body($response));

    foreach ($products as $product) {
        // Check if the product already exists by title
        $existing_product = get_page_by_title($product->title, OBJECT, 'product');
        if ($existing_product) {
            $product_id = $existing_product->ID;
            $product_data = array(
                'ID'           => $product_id,
                'post_content' => $product->description,
            );
            wp_update_post($product_data);
        } else {
            $product_data = array(
                'post_title'   => $product->title,
                'post_content' => $product->description,
                'post_status'  => 'publish',
                'post_type'    => 'product',
            );
            $product_id = wp_insert_post($product_data);
        }

        // Add/update product meta (price, category, image, etc.)
        update_post_meta($product_id, '_price', $product->price);

        // Handle categories
        if (!empty($product->category)) {
            $category_id = fake_store_sync_get_or_create_category($product->category);
            if ($category_id) {
                wp_set_post_terms($product_id, array($category_id), 'product_cat');
            }
        }

        // Handle product image
        if (!empty($product->image)) {
            $image_id = fake_store_sync_set_product_image($product->image, $product_id);
            if ($image_id) {
                set_post_thumbnail($product_id, $image_id);
            }
        }

        // Add other product meta updates here
    }
}

// Function to get or create category
function fake_store_sync_get_or_create_category($category_name) {
    $term = get_term_by('name', $category_name, 'product_cat');
    if ($term) {
        return $term->term_id;
    } else {
        $new_term = wp_insert_term($category_name, 'product_cat');
        if (!is_wp_error($new_term)) {
            return $new_term['term_id'];
        }
    }
    return null;
}

// Function to set product image
function fake_store_sync_set_product_image($image_url, $product_id) {
    // Download the image
    $image = wp_remote_get($image_url);

    if (is_wp_error($image)) {
        return false;
    }

    // Upload the image to the media library
    $upload = wp_upload_bits(basename($image_url), null, wp_remote_retrieve_body($image));

    if ($upload['error']) {
        return false;
    }

    // Check the type of the uploaded file
    $wp_filetype = wp_check_filetype($upload['file'], null);

    // Prepare an array of post data for the attachment
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name(basename($upload['file'])),
        'post_content'   => '',
        'post_status'    => 'inherit',
    );

    // Insert the attachment
    $attach_id = wp_insert_attachment($attachment, $upload['file'], $product_id);

    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Generate the metadata for the attachment, and update the database record
    $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}
?>
