<?php
$ev_data = new EV_Data_Layer();
if(isset($_POST['api_key'])) {
    $app_url = $_POST['application_url'];
    $list_id = $_POST['list_id'];
    $username = $_POST['username'];
    $api_key = $_POST['api_key'];
    $data = [
        'app_url' => $app_url,
        'list_id' => $list_id,
        'username' => $username,
        'api_key' => $api_key
    ];

    $response = $ev_data->save_ev_details($data);
    if($response == "Everlytic data saved" ||
     $response == "Everlytic data updated") {
        echo '<div class="alert alert-success" style="width:50%" role="alert"><p>'.$response."</p></div>";
     } else {
        echo '<div class="alert alert-danger" style="width:50%"  role="alert"><p>'.$response."</p></div>";
     }
    
}

?>