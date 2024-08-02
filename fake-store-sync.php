<?php
/*
Plugin Name: Fake Store Sync
Description: Синхронизира продукти от FakeStore API.
Version: 1.0
Author: име
*/

// Include necessary files
include_once(plugin_dir_path(__FILE__) . 'includes/functions.php');
include_once(plugin_dir_path(__FILE__) . 'includes/admin-menu.php');
include_once(plugin_dir_path(__FILE__) . 'includes/fetch-products.php');
include_once(plugin_dir_path(__FILE__) . 'includes/deactivate.php');
include_once(plugin_dir_path(__FILE__) . 'includes/activate.php');


// Register hooks for activation and deactivation
register_activation_hook(__FILE__, 'fake_store_sync_activate');
register_deactivation_hook(__FILE__, 'fake_store_sync_deactivate');

// Initialize the plugin
add_action('init', 'fake_store_sync_init');


