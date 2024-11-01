<?php

/*
Plugin Name: Customer Order History for WooCommerce
Plugin URI: https://www.wpslash.com/plugin/woohistory-woocommerce-order-history/
description: View Registered and Guest Customers Previous Order History.
Version: 2.4
Author: WPSlash
Text Domain: woohistory
Domain Path: /languages
Author URI: https://www.wpslash.com
License: GPL2
*/

if ( !function_exists( 'woo_fs' ) ) {
    // Create a helper function for easy SDK access.
    function woo_fs()
    {
        global  $woo_fs ;
        
        if ( !isset( $woo_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_11587_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_11587_MULTISITE', true );
            }
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $woo_fs = fs_dynamic_init( array(
                'id'             => '11587',
                'slug'           => 'woohistory',
                'type'           => 'plugin',
                'public_key'     => 'pk_2125861ad0a21ae048c5cc0fd99b6',
                'is_premium'     => false,
                'premium_slug'   => 'woohistory-pro',
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 3,
                'is_require_payment' => true,
            ),
                'menu'           => array(
                'slug'    => 'woohistory-settings',
                'contact' => false,
                'support' => false,
                'network' => true,
                'parent'  => array(
                'slug' => 'woocommerce',
            ),
            ),
                'is_live'        => true,
            ) );
        }
        
        return $woo_fs;
    }
    
    // Init Freemius.
    woo_fs();
    // Signal that SDK was initiated.
    do_action( 'woo_fs_loaded' );
}

function woohistory_load_plugin_textdomain()
{
    load_plugin_textdomain( 'woohistory', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'woohistory_load_plugin_textdomain' );
add_action( 'admin_head', 'woohistory_add_style_head' );
function woohistory_add_style_head()
{
    global  $pagenow, $typenow ;
    if ( $typenow == 'shop_order' || $typenow == 'product' ) {
        ?>
         <style type="text/css">
            .post-type-shop_order .wp-list-table .column-woohistory {
               width: 200px;
            }

            .order-status.status-completed {
               background: #3498DB;
               color: white;
            }
           
           
               .tablenav .view-switch, .tablenav.top .actions
               {
                                 display:block!important;

               }
               @media only screen and (max-width:782px)
               {
                   .wp-list-table th.column-primary~th, .wp-list-table tr:not(.inline-edit-row):not(.no-items) td.column-primary~td:not(.check-column)
               {
                  display:block;
                  float:left;

               }
               .wp-list-table th.column-primary~th
               {
                  display:none;
               }

               }
              
            
            
         </style>
         <?php 
    }
}

// create custom plugin settings menu
add_action( 'admin_menu', 'woohistory_create_menu' );
function woohistory_create_menu()
{
    add_submenu_page(
        'woocommerce',
        'Customer Order History ',
        'Customer Order History Settings',
        'manage_woocommerce',
        'woohistory-settings',
        'woohistory_settings_page'
    );
    //call register settings function
    add_action( 'admin_init', 'register_woohistory_plugin_settings' );
}

function register_woohistory_plugin_settings()
{
    //register our settings
    register_setting( 'woohistory-settings-group', 'woohistory_search_by_phone' );
    register_setting( 'woohistory-settings-group', 'woohistory_search_by_email' );
    register_setting( 'woohistory-settings-group', 'woohistory_search_by_name' );
}

function woohistory_settings_page()
{
    wp_enqueue_style(
        'woohistory-admin.css',
        plugin_dir_url( __FILE__ ) . '/css/admin.css',
        false,
        '1.0',
        'all'
    );
    $extra_class = " woohistory-premium";
    ?>
      <div class="wrap">
         <h1><?php 
    _e( 'Order Customer History Settings', 'woohistory' );
    ?></h1>

         <form method="post" action="options.php">
            <?php 
    settings_fields( 'woohistory-settings-group' );
    ?>
            <?php 
    do_settings_sections( 'woohistory-settings-group' );
    ?>
            <p><?php 
    _e( 'Please check all the criteria you want to search for orders from the same customer. We higly suggest to check by phone number only. For example if you check both phone and email WooHistory will return the orders that made with both same email and phone.', 'woohistory' );
    ?></p>
            <table class="form-table">

               <tr valign="top" class="<?php 
    echo  $extra_class ;
    ?>">
                  <th scope="row"><?php 
    _e( 'Search by Phone', 'woohistory' );
    ?></th>
                  <td><input type="checkbox" name="woohistory_search_by_phone" value="1" <?php 
    checked( 1, get_option( 'woohistory_search_by_phone' ), true );
    ?>  /></td>
               </tr>
               
               <tr valign="top" class="<?php 
    echo  $extra_class ;
    ?>">
                  <th scope="row"><?php 
    _e( 'Search by Email', 'woohistory' );
    ?></th>
                  <td><input type="checkbox" name="woohistory_search_by_email" value="1" <?php 
    checked( 1, get_option( 'woohistory_search_by_email' ), true );
    ?> /></td>
               </tr>

               <tr valign="top">
                  <th scope="row"><?php 
    _e( 'Search by Name', 'woohistory' );
    ?></th>
                  <td><input type="checkbox" name="woohistory_search_by_name" value="1" <?php 
    checked( 1, get_option( 'woohistory_search_by_name' ), true );
    ?> /></td>
               </tr>
            </table>

            <?php 
    submit_button();
    ?>

         </form>
      </div>
      <?php 
}

add_filter( 'manage_edit-shop_order_columns', 'woohistory_add_history_column', 1 );
function woohistory_add_history_column( $columns )
{
    //add columns
    $columns['woohistory'] = __( 'History', 'woohistory' );
    return $columns;
}

// Call the function
function woohistory_enqueue_css_on_shop_order()
{
    global  $typenow ;
    // Specify the conditional tag
    
    if ( 'shop_order' === $typenow ) {
        // If page matches, then load the following files
        wp_enqueue_style(
            'new-styles.css',
            plugin_dir_url( __FILE__ ) . '/css/woohistory-fonts.css',
            false,
            '1.0',
            'all'
        );
        wp_enqueue_style(
            'woohistory-admin.css',
            plugin_dir_url( __FILE__ ) . '/css/admin.css',
            false,
            '1.0',
            'all'
        );
        //wp_enqueue_script('new-scripts.js', get_template_directory_uri().'/path/to/new-scripts.js', false ,'1.0', 'all' );
        // If the condition tag does not match...
    }

}

// Hook into the WordPress Function
add_action( 'admin_enqueue_scripts', 'woohistory_enqueue_css_on_shop_order' );
// adding the data for each orders by column (example)
add_action(
    'manage_shop_order_posts_custom_column',
    'woohistory_history_column_callback',
    99,
    3
);
function woohistory_history_column_callback( $column )
{
    global  $post, $woocommerce, $the_order ;
    $order_id = $the_order->id;
    switch ( $column ) {
        case 'woohistory':
            $meta_data_woohistory = get_post_meta( $order_id, '_woohistory_meta', true );
            
            if ( !$meta_data_woohistory ) {
                $billing_first_name = $the_order->get_billing_first_name();
                $billing_last_name = $the_order->get_billing_last_name();
                $orders = null;
                $search_details = array();
                
                if ( get_option( 'woohistory_search_by_name' ) ) {
                    $search_details['billing_first_name'] = $billing_first_name;
                    $search_details['billing_last_name'] = $billing_last_name;
                }
                
                $orders = array();
                
                if ( $billing_phone != "" ) {
                    $orders = wc_get_orders( $search_details );
                    $meta_data_woohistory = array(
                        "total"    => count( $orders ),
                        "orders"   => array(),
                        "statuses" => array(),
                    );
                    foreach ( $orders as $order ) {
                        
                        if ( $order->get_id() != $order_id ) {
                            $meta_data_woohistory["orders"][] = array(
                                "order_id" => $order->get_id(),
                                "status"   => $order->get_status(),
                                "link"     => admin_url( 'post.php?post=' . absint( $order->get_id() ) . '&action=edit' ),
                            );
                            $meta_data_woohistory["statuses"][$order->get_status()] = (( $meta_data_woohistory["statuses"][$order->get_status()] > 0 ? $meta_data_woohistory["statuses"][$order->get_status()] : 0 )) + 1;
                        }
                    
                    }
                }
                
                update_post_meta( $order_id, '_woohistory_meta', $meta_data_woohistory );
            }
            
            if ( !empty($meta_data_woohistory["orders"]) ) {
                echo  _e( 'Total Orders', 'woohistory' ) . ":" . count( $meta_data_woohistory["orders"] ) ;
            }
            $orders_status_count = array();
            $buttons_export = "";
            if ( !empty($meta_data_woohistory["orders"]) ) {
                foreach ( $meta_data_woohistory["orders"] as $history_order ) {
                    $buttons_export .= '<a  target="_blank" class="woohistory-button woohistory-button-' . $history_order["status"] . '" href="' . $history_order["link"] . '" >' . __( 'View Order', 'woohistory' ) . " " . $history_order["order_id"] . ' (<i class="icon-' . $history_order["status"] . '"></i>)</a>  ';
                }
            }
            if ( !empty($meta_data_woohistory["orders"]) ) {
                foreach ( $meta_data_woohistory["statuses"] as $current_status => $orders ) {
                    echo  "<div>" . ucfirst( wc_get_order_status_name( $current_status ) ) . ": " . $orders . "</div>" ;
                }
            }
            
            if ( !empty($meta_data_woohistory["orders"]) ) {
                echo  '<div>' ;
                echo  '<h4>' . __( 'Other Customer Orders', 'woohistory' ) . '</h4>' ;
                echo  '<hr>' ;
                echo  $buttons_export ;
                echo  '</div>' ;
            }
            
            break;
    }
}

add_action(
    'woocommerce_order_status_changed',
    'woohistory_on_status_change',
    10,
    3
);
function woohistory_on_status_change( $order_id, $old_status, $new_status )
{
    $the_order = wc_get_order( $order_id );
    $billing_email = $the_order->get_billing_email();
    $billing_phone = $the_order->get_billing_phone();
    $billing_first_name = $the_order->get_billing_first_name();
    $billing_last_name = $the_order->get_billing_last_name();
    $orders = null;
    $search_details = array();
    if ( get_option( 'woohistory_search_by_email' ) ) {
        $search_details['billing_email'] = $billing_email;
    }
    if ( get_option( 'woohistory_search_by_phone' ) ) {
        $search_details['billing_phone'] = $billing_phone;
    }
    
    if ( get_option( 'woohistory_search_by_name' ) ) {
        $search_details['billing_first_name'] = $billing_first_name;
        $search_details['billing_last_name'] = $billing_last_name;
    }
    
    $orders = array();
    
    if ( $billing_phone != "" ) {
        $orders = wc_get_orders( $search_details );
        $meta_data_woohistory = array(
            "total"    => count( $orders ),
            "orders"   => array(),
            "statuses" => array(),
        );
        foreach ( $orders as $order ) {
            
            if ( $order->get_id() != $order_id ) {
                $meta_data_woohistory["orders"][] = array(
                    "order_id" => $order->get_id(),
                    "status"   => $order->get_status(),
                    "link"     => admin_url( 'post.php?post=' . absint( $order->get_id() ) . '&action=edit' ),
                );
                $meta_data_woohistory["statuses"][$order->get_status()] = (( $meta_data_woohistory["statuses"][$order->get_status()] > 0 ? $meta_data_woohistory["statuses"][$order->get_status()] : 0 )) + 1;
            }
        
        }
    }
    
    update_post_meta( $order_id, '_woohistory_meta', $meta_data_woohistory );
}

function woohistory_register_new_order_statuses()
{
    register_post_status( 'wc-receipt-denied', array(
        'label'                     => __( 'Receipt Denied', 'woohistory' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Receipt Denied <span class="count">(%s)</span>', 'Receipt Denied <span class="count">(%s)</span>', 'woohistory' ),
    ) );
    register_post_status( 'wc-receipt-ignored', array(
        'label'                     => __( 'Receipt Ignored', 'woohistory' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Receipt Ignored <span class="count">(%s)</span>', 'Receipt Ignored <span class="count">(%s)</span>', 'woohistory' ),
    ) );
}

add_action( 'init', 'woohistory_register_new_order_statuses' );
// Add to list of WC Order statuses
function woohistory_add_new_order_statuses( $order_statuses )
{
    $new_order_statuses = array();
    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {
        $new_order_statuses[$key] = $status;
        
        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-receipt-denied'] = __( 'Receipt Denied', 'woohistory' );
            $new_order_statuses['wc-receipt-ignored'] = __( 'Receipt Ignored', 'woohistory' );
        }
    
    }
    return $new_order_statuses;
}

add_filter( 'wc_order_statuses', 'woohistory_add_new_order_statuses' );