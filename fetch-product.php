<?php

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