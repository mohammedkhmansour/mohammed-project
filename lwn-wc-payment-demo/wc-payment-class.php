<?php

// https://woocommerce.com/document/payment-gateway-api/

class WC_Gateway_Demo extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->id = 'ms_wc_gateway_demo';
        $this->has_fields = false; 

        $this->description = 'This is just a demo payment gateway.';

        $this->init_settings(); 
        $this->init_form_fields(); 

       
        $this->title = $this->get_option('title', 'Demo Gateway');
        
        $this->icon = $this->get_option('icon');

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options'] );
    
        add_action( 'woocommerce_api_' . strtolower(__CLASS__), [$this, 'check_response']);
        
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title' => __( 'Enable/Disable', 'woocommerce' ),
                'type' => 'checkbox',
                'label' => __( 'Enable Demo Payment', 'woocommerce' ),
                'default' => 'yes'
            ],
            'title' => [
                'title' => __( 'Title', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                'default' => __( 'Demo Payment', 'woocommerce' ),
                'desc_tip'      => true,
            ],
            'description' => [
                'title' => __( 'Customer Message', 'woocommerce' ),
                'type' => 'textarea',
                'default' => ''
            ],
            'sandbox' => [
                'title' => 'Sandbox Enabled',
                'type' => 'select',
                'options' => [
                    'no' => 'No',
                    'yes' => 'Yes',
                ],
            ],
            'api_key' => [
                'title' => __( 'API Key', 'woocommerce' ),
                'type' => 'text',
            ],
            'api_secret' => [
                'title' => __( 'API Secret', 'woocommerce' ),
                'type' => 'text',
            ],
        ];
    }

    public function process_payment( $order_id ) {
        global $woocommerce;
        $order = new WC_Order( $order_id );

        $api_key = $this->get_option('api_key');
        $api_secret = $this->get_option('api_secret');
    
        // Mark as on-hold (we're awaiting the cheque)
        $order->update_status('on-hold', __( 'Awaiting payment', 'woocommerce' ));
    
        // Remove cart
        $woocommerce->cart->empty_cart();
    
        // Return thankyou redirect
        $return_url = add_query_arg([
                'wc-api' => __CLASS__,
                'order_id' => $order_id,
            ], home_url('/')
        );

        return array(
            'result' => 'success',
            'redirect' => 'https://www.paypal.com/ps/home?return_url=' . urlencode($return_url),
        );
    }

    public function check_response()
    {
        global $woocommerce;

        $order_id = $_REQUEST['order_id'];
        try {
            $order = new WC_Order($order_id);
        } catch (Exception $e) {
            wp_die($e->getMessage());
        }
        if (strlen($_REQUEST['trans_id']) == 6) {
            $order->payment_complete($_REQUEST['trans_id']);
            wp_redirect($this->get_return_url($order));
            exit;
        } 
        
        $order->update_status('failed', 'Payment failed');
        // $order->add_order_note();

        wp_redirect( $woocommerce->cart->get_cart_url() );
        exit;
        
    }
}