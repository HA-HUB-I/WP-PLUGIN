<?php
// Function to handle deactivation
function fake_store_sync_deactivate() {
    delete_option('fake_store_api_url');
}