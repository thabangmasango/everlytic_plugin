<?php
// function save_everlytic_details($data)
// {
//     global $wpdb;     
//     $table_name = $wpdb->prefix . "everlytic_details";     
//     $sql = $wpdb->insert($table_name, array('application_url'=> $data->app_url,'list_id'=> $data->list_id,'username'=> $data->username,'api_key'=> $data->api_key)); 

//     if($sql){
//         echo "Saved!";
//     } else {
//         echo $sql;
//     }
// }

global $wpdb;     
$table_name = $wpdb->prefix . "everlytic_details";     


//get everlytic details id if they exist
$results = $wpdb->get_results( 
    $wpdb->prepare("SELECT * FROM $table_name LIMIT 1")
 );


if(isset($_POST['api_key'])) {
    $app_url = $_POST['application_url'];
    $list_id = $_POST['list_id'];
    $username = $_POST['username'];
    $api_key = $_POST['api_key'];


     if(count($results) > 0) {
        $sql = $wpdb->update($table_name, array('application_url'=> $app_url,'list_id'=> $list_id,'username'=> $username,'api_key'=> $api_key), array( 'id' => $results[0]->id));
        if($sql){
            echo "Everlytic details updated!";
        } else {
            echo $results[0]->id;
        }
     } else {
        $sql = $wpdb->insert($table_name, array('application_url'=> $app_url,'list_id'=> $list_id,'username'=> $username,'api_key'=> $api_key)); 
        if($sql){
            echo "Everlytic details saved!";
        } else {
            echo $sql;
        }
     }
    

    // $data = [
    //     "app_url" => $app_url,
    //     "list_id" => $list_id,
    //     "username" => $username,
    //     "api_key" => $api_key
    // ];

    // save_everlytic_details($data);
}

?>