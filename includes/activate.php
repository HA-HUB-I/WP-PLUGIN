<?php

// Function to handle activation
function fake_store_sync_activate() {
    add_option('fake_store_api_url', 'https://fakestoreapi.com/products');
}