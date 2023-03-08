<!DOCTYPE html>
<html lang="en">
<?php
session_start();
$client = '';
$userId = '';
$job_no = '';

require_once('./model/DB_class.php');

$settings                       = parse_ini_file('../config.ini',true);

if(isset($_GET['userId']) && isset($_GET['client']) && isset($_GET['job_no']) && isset($_GET['do_no'])){

    $client         = $_GET['client'];
    $job_no         = $_GET['job_no'];
    $userId         = $_GET['userId'];
    $do_code        = $_GET['do_no'];

    $db                         = new DB();
    $settings                   = $settings[$client];
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];

    $url = 'http://13.229.126.174/Delivery/api.php?action=getTransaction_DeliveryStatus';
    $data = '{"data":{ "client":"'.$client.'", "so_no":["'.$do_code.'"]}}';

    $data = json_decode($data,true);

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );

    // echo json_encode($options);
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) { var_dump($result); }

    $result = json_decode($result,true);
    $data   = $result['data'];
    
    /* {"job_id":"299","so_no":"KDO2002\\0528, KDO2002\\0530, KDO2002\\0529, KDO2002\\0531, CDO2002\\0520","status":"Pending","start_point":"GAIDO","end_point":"FOOK SOON AUTO SUPPLY SDN BHD","delivery_cost":"1","remark_to_rider":"","rider_notes":"","rider_start_latitude":"","rider_start_longitude":"","rider_end_latitude":"","rider_end_longitude":"","img_shop":"","rider_name":"rider 1","delivery_completed_date":"0000-00-00 00:00:00","delivery_date":"2020-02-14","created_datetime":"2020-02-14 15:14:06"} */
    foreach($data as $details){
        foreach($details as $detail){
            if($job_no == $detail['job_id']){

                $job_id = $detail['job_id'];
                $so_no = $detail['so_no'];
                $cost = $detail['delivery_cost'];
                $start_point = $detail['start_point'];
                $end_point = $detail['end_point'];
                $remark_to_rider = $detail['remark_to_rider'];
                $rider_notes = $detail['rider_notes'];

                $rider_start_latitude = $detail['rider_start_latitude'];
                $rider_start_longitude = $detail['rider_start_longitude'];

                $rider_end_latitude = $detail['rider_end_latitude'];
                $rider_end_longitude = $detail['rider_end_longitude'];


                $img_shop = $detail['img_shop'];
                $rider_name = $detail['rider_name'];
                $delivery_completed_date = $detail['delivery_completed_date'];
                $delivery_date = $detail['delivery_date'];
                $created_datetime = $detail['created_datetime'];

                $delivery_completed_date = DateTime::createFromFormat('Y-m-d H:i:s', $delivery_completed_date);
                $delivery_completed_date = $delivery_completed_date->format('d M Y h:i A');

                $delivery_date = DateTime::createFromFormat('Y-m-d', $delivery_date);
                $delivery_date = $delivery_date->format('d M Y');

                $created_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $created_datetime);
                $created_datetime = $created_datetime->format('d M Y h:i A');

                $view = '<div style="padding-left:2%;padding-right:2%;padding-top:5%;padding-bottom:5%;width:100%;height:100%;"><b>Delivery Job ID </b> - '.$job_id.' <font style="float:right;color:green;">Job Completed</font> <hr>This delivery job is tied to DO/INV: <font style="float:right;">Cost: </font><br>';
                    
                $do_no = (explode(",",$so_no));
    
                for($i = 0;$i < count($do_no); $i++){
                    if($do_no[$i]){
                        $do_no[$i] = trim($do_no[$i],' ');
                        if($i == 0){
                            $view .='<font style="float:right;color:blue;font-weight:bold;">RM'.number_format($cost,2,'.',',').'</font>';
                        }
                        $view .= '<a style="color:blue;" href="delivery_details.php?client='.$client.'&userId=0&do_code='.$do_no[$i].'">'.$do_no[$i].'</a><br>';
                    }
                }
                 
                $view .='
                <br><br>
                <b>Rider : </b> '.$rider_name.'
                <br>
                <b>Start Point : </b> '.$start_point.'
                <br>
                <b>Destination : </b> '.$end_point.'
                <br><br>
                <table style="width:100%;height:100%;">
                    <tr style="font-weight:bold;"><td style="width:33%">Created Date: </td><td style="width:33%">Delivery Date: </td><td style="width:33%">Completed Date: </td></tr>
                    <tr><td>'.$created_datetime.'</td><td>'.$delivery_date.'</td><td>'.$delivery_completed_date.'</td></tr>
                </table>
                <br>
                <b> Remark to Rider/Driver </b>
                <br>
                <input style="border:1px solid black;color:black;background-color:#ccc;width:99%;height:20%;padding:5%;" value="'.$remark_to_rider.'"></input>
                <br>
                <b> Rider/Drive Notes </b>
                <br>
                <input style="border:1px solid black;color:green;background-color:yellow;width:99%;height:20%;padding:5%;" value="'.$rider_notes.'"></input>
                <input id="start_longitude" style="display:none" value="'.$rider_start_longitude.'"></input>
                <input id="start_latitude" style="display:none" value="'.$rider_start_latitude.'"></input>
                <input id="end_longitude" style="display:none" value="'.$rider_end_longitude.'"></input>
                <input id="end_latitude" style="display:none" value="'.$rider_end_latitude.'"></input>

                <div style="background-color:#f6f6f6;width:99%;height:50%;margin-left:0.5%;text-align:center;margin-top:2%;">
                <div id="map" style="width:100%;height:350px;"></div></div>
                <div style="background-color:#f6f6f6;width:99%;margin-left:0.5%;text-align:center;margin-top:2%;">
                <img src="'.$img_shop.'">
                </div>
                </div>';
            }
        }

    }


}else{
    header('location:Errorpage.php');
}

?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3pro.css">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>EasyTech</title>
</head>
<body>
    <?php echo $view; ?>
    <script>

    function initMap() {

        var start_lat = document.getElementById('start_latitude').value;
        var start_long = document.getElementById('start_longitude').value;
        var end_lat = document.getElementById('end_latitude').value;
        var end_long = document.getElementById('end_longitude').value;

        var center_lat = (parseFloat(start_lat)+parseFloat(end_lat))/2;
        var center_long = (parseFloat(end_lat)+parseFloat(end_long))/2;
        debugger

        var directionsService = new google.maps.DirectionsService();
        var directionsRenderer = new google.maps.DirectionsRenderer();
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 5,
          center: {lat: center_lat, lng: center_long}
        });
        directionsRenderer.setMap(map);
        calculateAndDisplayRoute(directionsService, directionsRenderer);
        
      }

    function calculateAndDisplayRoute(directionsService, directionsRenderer) {

        var start_lat = document.getElementById('start_latitude').value;
        var start_long = document.getElementById('start_longitude').value;
        var end_lat = document.getElementById('end_latitude').value;
        var end_long = document.getElementById('end_longitude').value;

        start_lat = parseFloat(start_lat);
        start_long = parseFloat(start_long);
        end_lat = parseFloat(end_lat);
        end_long = parseFloat(end_long);

        directionsService.route(
            {
              origin: {lat: start_lat, lng: start_long},
              destination: {lat: end_lat, lng: end_long},
              travelMode: 'DRIVING',
            },
            function(response, status) {
              if (status === 'OK') {
                directionsRenderer.setDirections(response);
              } else {
                window.alert('Directions request failed due to ' + status);
              }
            });
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA8nIbpxFWOtxjvUSTqcRv4UTSKUEy28Sk&&callback=initMap">
    </script>

</body>
</html>
