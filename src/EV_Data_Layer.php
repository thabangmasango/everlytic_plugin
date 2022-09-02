<?php
class EV_Data_Layer
{
    private $wpdb;
    private $table_name;
    private $charset_collate;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'everlytic_details';
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    //create plugin table
    function create_ev_table()
    {
        if ($this->wpdb->get_var("show tables like '$this->table_name'") != $this->table_name) {

            $sql = "CREATE TABLE $this->table_name (
                id INT NOT NULL AUTO_INCREMENT,
                application_url VARCHAR(150) NOT NULL,
                list_id INT NOT NULL,
                username VARCHAR(100) not null,
                api_key VARCHAR(128) not null,
                time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id)
                ) $this->charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }


    function delete_ev_table()
    {

    }

    //Save contact that purchased items on woocommerce
    function save_contact_to_ev($order_data)
    {
        $result = $this->get_ev_details();
        $customer_data = [
            "name" => $order_data['billing']['first_name'],
            "lastname" => $order_data['billing']['last_name'],
            "email" => $order_data['billing']['email'],
            "list_id" => $result[0]->list_id
        ];
    
        $fields = json_encode($customer_data);

        $username = $result[0]->username;
        $api_key = $result[0]->api_key;
        $url = $result[0]->application_url . "/api/2.0/contacts";
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

    //Save everlytic api details and list ID
    function save_ev_details($everlytic_data)
    {
        $results  = $this->get_ev_details();
        if (count($results) > 0) {
            $sql = $this->wpdb->update($this->table_name, array('application_url' => $everlytic_data['app_url'], 'list_id' => $everlytic_data['list_id'], 'username' => $everlytic_data['username'], 'api_key' => $everlytic_data['api_key']), array('id' => $results[0]->id));
            if ($sql === 0 || $sql > 0) {
                return "Everlytic data updated";
            } else {
                return "Something when wrong, please refresh your page";
            }
        } else {
            $sql = $this->wpdb->insert($this->table_name, array('application_url' => $everlytic_data['app_url'], 'list_id' => $everlytic_data['list_id'], 'username' => $everlytic_data['username'], 'api_key' => $everlytic_data['api_key']));
            if ($sql) {
                return "Everlytic data saved";
            } else {
                return "Something when wrong, please refresh your page";
            }
        }
    }

    function get_ev_details()
    {
        $result = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM $this->table_name LIMIT 1"));
        return $result;
    }


    //return everlytic connection status
    function check_connection()
    {
        $results = $this->get_ev_details();
        $response = json_decode($this->send_request($results[0]->application_url, $results[0]->list_id, $results[0]->username, $results[0]->api_key));
        if (count($results) > 0) {
            if (isset($response->item->id)) {
                return 1;
            } else {
                return 0;
            }
        } else {
            echo 0;
        }
    }

    //send request to everlytic to check connection
    function send_request($url, $list_id, $username, $api_key)
    {
        $url = $url . "/api/2.0//lists/" . $list_id;
        $method = 'GET';
        $cSession = curl_init();
        $headers = array();
        $auth = base64_encode($username . ':' . $api_key);
        $headers[] = 'Authorization: Basic ' . $auth;
        curl_setopt($cSession, CURLOPT_URL, $url);
        curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cSession, CURLOPT_HEADER, false);
        curl_setopt(
            $cSession,
            CURLOPT_CUSTOMREQUEST,
            strtoupper($method)
        );
        curl_setopt($cSession, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($cSession);
        $file = get_stylesheet_directory() . '/log.txt';
        $log_file = fopen($file, 'a');
        fwrite($log_file, $result . "\n");
        curl_close($cSession);
        return $result;
    }
}
