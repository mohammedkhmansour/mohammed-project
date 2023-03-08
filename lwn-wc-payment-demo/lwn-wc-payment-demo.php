<?php
/*
Plugin Name: MS WooCommerce Payment GW Demo
Author: Mohammed Abu mansour
Version: 1.0
*/
 
function mswc_load_payment_class() {
    if (!class_exists('WC_Payment_Gateway')) {
        wp_die('You should install or activate WooCommerce plugin.');
    }
    include __DIR__ . '/wc-payment-class.php';
}
add_action( 'plugins_loaded', 'mswc_load_payment_class' );

function mswc_register_gateway_method( $methods ) { 
    $methods[] = 'WC_Gateway_Demo'; 
    return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'mswc_register_gateway_method' );