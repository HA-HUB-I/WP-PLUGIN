<?php
add_action('admin_post_fake_store_sync', 'fake_store_sync_handle_submit');
function fake_store_sync_init() {
    // Initialization code can go here
}


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


