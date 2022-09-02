<?php

 /**
 * Plugin Name:       Everlytic WooCommere
 * Description:       Stores user details on the Everlytic list, you need to provide the list ID and API details.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Thabang Masango
 */

 
require "src/EV_Data_Layer.php";
add_action('admin_menu', 'wporg_options_page');

wp_register_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
wp_enqueue_style('prefix_bootstrap');

add_action('wp_enqueue_scripts', 'register_my_scripts');
function register_my_scripts()
{
    wp_enqueue_style('style', plugins_url('css/style.css', __FILE__));
}

function wporg_options_page()
{
    add_menu_page(
        'Everlytic Details', //Title
        'Evelytic Contact',
        'manage_options',
        'evdetails',
        'everlytic_woocommerce_contact_page_html',
         plugin_dir_url(__FILE__) . 'public/images/icon.svg',
         20
    );
}



function everlytic_woocommerce_contact_page_html()
{
?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1><?php echo esc_html(get_admin_page_title())."\n";
                    require("form.php");
                    $ev_data = new EV_Data_Layer();
                    $result = $ev_data->get_ev_details();
                    ?>
                    <br>
                </h1>

                <form action="" method="post">
                    <div class="form-group">
                        <input type="text" name="application_url" id="application_url" value="<?php echo $result ? $result[0]->application_url : ""; ?>" placeholder="Application URL" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="number" name="list_id" id="list_id" value="<?php echo $result ? $result[0]->list_id : "" ?>" placeholder="List Id" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="text" name="username" id="username" value="<?php echo $result ? $result[0]->username : "" ?>" placeholder="Everlytic username" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="text" name="api_key" id="api_key" value="<?php echo $result ? $result[0]->api_key : "" ?>" placeholder="API key" class="form-control">
                    </div>
                    <?php
                    // output security fields for the registered setting "wporg_options"
                    settings_fields('wporg_options');
                    // output setting sections and their fields
                    // (sections are registered for "wporg", each field is registered to a specific section)
                    do_settings_sections('evdetails');
                    // output save settings button
                    submit_button(__('Save Settings', 'textdomain'));

                    if ($ev_data->check_connection() == 1) {
                        echo '<span class="badge" style="background: #28a745">Connected</span>';
                    } else {
                        echo '<span class="badge" style="background:#dc3545;">Not Connected</span>';
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>
<?php
}


register_activation_hook(__FILE__, 'create_plugin_database_table');
function create_plugin_database_table()
{
    $ev_data = new EV_Data_Layer();
    $ev_data->create_ev_table();
}

add_action('woocommerce_order_status_processing', 'wc_data_to_everlyticv1', 10, 1);
function wc_data_to_everlyticv1($order_id)
{
    $ev_data = new EV_Data_Layer();

    $order = wc_get_order($order_id);
    $order_data = $order->get_data();

    $ev_data->save_contact_to_ev($order_data);
}

wp_register_script('prefix_jquery', '//cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js');
wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
wp_enqueue_script('prefix_jquery');
wp_enqueue_script('prefix_bootstrap');
