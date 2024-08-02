<?php
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
add_action('admin_menu', 'my_plugin_menu');

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

add_action('admin_init', 'fake_store_sync_settings_init');
// Handle form submission
add_action('admin_post_fake_store_sync', 'fake_store_sync_handle_submit');
function fake_store_sync_settings_init() {
    add_settings_section(
        'fake_store_sync_settings_section',
        'Настройки на Fake Store Sync',
        'fake_store_sync_settings_section_callback',
        'fake_store_sync'
    );

    add_settings_field(
        'fake_store_api_url',
        'URL на Fake Store API',
        'fake_store_api_url_render',
        'fake_store_sync',
        'fake_store_sync_settings_section'
    );
}