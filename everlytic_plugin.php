<html>
<head>
    <title>Everlytic</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
</head>
<body>
</html>
<?php
/**
 * Plugin Name: Everlytic Woocommere Contacts
 */

add_action( 'admin_menu', 'wporg_options_page' );
function wporg_options_page() {
    add_menu_page(
        'Everlytic WooCommerce Sync Purchase Only Contact',
        'Evelytic WooCommerce List',
        'manage_options',
        'wporg',
        'everlytic_woocommerce_contact_page_html',
        plugin_dir_url(__FILE__) . 'images/icon_wporg.png',
        20
    );
}

function everlytic_woocommerce_contact_page_html() {
    ?>
    <div class="wrap" style="width:40%; height:auto;">
    <?php require("data.php"); ?>
      <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
      <form action="" method="post">
        <div class="form-group">
            <input type="text" name="application_url" id="application_url" value="<?php echo $results ? $results[0]->application_url : ""; ?>" placeholder="Application URL" class="form-control">
        </div>
        <div class="form-group">
            <input type="number" name="list_id" id="list_id" value="<?php echo $results ? $results[0]->list_id : "" ?>" placeholder="List Id" class="form-control">
        </div>
        <div class="form-group">
            <input type="text" name="username" id="username" value="<?php echo $results ? $results[0]->username : "" ?>" placeholder="Everlytic username"class="form-control">
        </div>
        <div class="form-group">
            <input type="text" name="api_key" id="api_key" value="<?php echo $results ? $results[0]->api_key : "" ?>" placeholder="API key" class="form-control">
        </div>
        <?php
        // output security fields for the registered setting "wporg_options"
        settings_fields( 'wporg_options' );
        // output setting sections and their fields
        // (sections are registered for "wporg", each field is registered to a specific section)
        do_settings_sections( 'wporg' );
        // output save settings button
        submit_button( __( 'Save Settings', 'textdomain' ) );
        ?>
      </form>
    </div>
    <?php
}


register_activation_hook( __FILE__, 'create_plugin_database_table' );
function create_plugin_database_table()
{

    global $wpdb;
    $table_name = $wpdb->prefix . "everlytic_details"; 
    #Check to see if the table exists already, if not, then create it

    if($wpdb->get_var( "show tables like '$table_name'" ) != $table_name) 
    {

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        application_url VARCHAR(150) NOT NULL,
        list_id INT NOT NULL,
        username VARCHAR(100) not null,
        api_key VARCHAR(128) not null,
        time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
       ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

function wc_data_to_everlytic( $order_id ) {
    $order = wc_get_order( $order_id );
   $customer_data = [
               "name" => $order->billing_first_name,
               "lastname" => $order->billing_last_name,
               "email" => $order->billing_email,
               "list_id" => $results[0]->list_id 
           ];
   
           $fields = json_encode($customer_data);
   
           // $file = get_stylesheet_directory(). '/log.txt';
           // $log_file = fopen($file, 'a');
           // // fwrite($log_file, $order_id."\n");
           // fwrite($log_file, $fields."\n");
           // // fwrite($log_file, $order."\n");
   
           $username = $results[0]->username ;
           $api_key = $results[0]->api_key ; 
           $url = $results[0]->application_url."/api/2.0/contacts";
           $method = 'POST';
           $cSession = curl_init();
           $headers = array();
           $auth = base64_encode($username . ':' . $api_key);
           $headers[] = 'Authorization: Basic ' . $auth;
           curl_setopt($cSession, CURLOPT_URL, $url);
           curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
           curl_setopt($cSession, CURLOPT_HEADER, false);
           curl_setopt($cSession, CURLOPT_CUSTOMREQUEST, strtoupper($method));
           curl_setopt($cSession, CURLOPT_POSTFIELDS, $fields);
           $headers[] = 'Content-Type: application/json';
           curl_setopt($cSession, CURLOPT_HTTPHEADER, $headers);
           $result = curl_exec($cSession);
           curl_close($cSession);
   }

   if ( ! function_exists( 'is_woocommerce_activated' ) && count($results) > 0) {
	function is_woocommerce_activated() {
		if ( class_exists( 'woocommerce' ) ) {
            add_action( 'woocommerce_order_status_processing', 'wc_data_to_everlytic', 10, 1);
        } 
	}
}



