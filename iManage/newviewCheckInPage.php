<!DOCTYPE html>
<html lang="en">
<?php
require_once('getCheckInViewDetails.php');
session_start();
$client = '';
$userId = '';
if(isset($_GET['checkInId']) && isset($_GET['client'])){

	$client = $_GET['client'];
	$checkInId = $_GET['checkInId'];
	
	$object = new stdClass();
	$object->client = $client;
	$object->checkInId = $checkInId;

	$param = array(
		"client"		=>$client,
		"checkInId" =>$checkInId
	);
	
	$check_in_details = getCheckInViewDetails($param);
	$userId = $_GET['userId'];
	$goback_link = "https://easysales.asia/esv2/webview/iManage/newCheckInPage.php?userId={$userId}&roleId=1&client={$client}";
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
	<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
	<link href='css/magnific-popup.css' rel='stylesheet'>
	<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="js/jquery.magnific-popup.min.js"></script>
	
	<style>
	.column {
		float: left;
		width: 38%;
		padding-top: 5px;
		padding-bottom: 0px;
		height: 25px;
		border-style: solid;
		border-width: 1px 1px 0px 1px;
		border-color:lightgrey;
		border-radius:1px;
	}
	
	.row:after {
		display: table;
		clear: both;
	}
	header, footer{
		 height: 60px;
	}
	
	.site-wrapper{
		 display: flex;
		 flex-direction: column;
		 min-height: 100vh;
	}
	
	.site-content{
		 flex: 1
	}
	body {
		font-family: 'Open Sans';
		font-size: 15px;
		user-select: none;
		-webkit-user-select: none;
		-ms-user-select: none;
		-webkit-touch-callout: none;
		-o-user-select: none;
		-moz-user-select: none;
		line-height: 1;
		padding: 0;
		margin: 0;
	}
	
	.text {
		color:#147efb;
		text-align:left;
	}
	.text_white {
		color:#fff;
		text-align:left;
	}
	.text_grey {
		color:grey;
		text-align:left;
	}
	.non_important_text {
		font-size: 14px;
	}
	.radius {
		border-radius:3px;
	}
	.divider {
		height:5px;
	}
	
	.company-title{
		box-sizing: border-box;
		-moz-box-sizing: border-box;
		-webkit-box-sizing: border-box;
		height: 25px;
		color:grey;
		font-weight: bold;
		background-color: #f4f5f7;
		padding: 0px;
		font-size:14px;
	}
	
	
	.title {
		color:#147efb;
		text-align:left;
		margin-block-start: 0.1em;
		margin-block-end: 0.1em;
		font-weight: bold;
	}
	.dates {
		text-align:left;
	}
	.message {
		color: grey;
		text-align:left;
		font-style: italic;
	}
	p {
		display: block;
		margin-block-start: 0.25em;
		margin-block-end: 0.25em;
		margin-inline-start: 0.5px;
		margin-inline-end: 0.5px;
		margin: 3px;
		padding: 2px;
	}
	
	.delivery_status {
		display: inline;
	}
	
	
	.dropdown-div{
		padding-left: 5px;
		padding-right: 5px;
		box-sizing: border-box;
		-moz-box-sizing: border-box;
		-webkit-box-sizing: border-box;
		background-color: #147efb;
		height: 35px;
		border: 1px solid #147efb;
	}
	
	.center-text{
		position: relative;
		float: left;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
	}
	
	.limit-text-length {
		overflow: hidden;
		text-overflow: ellipsis;
		max-height: 25px;
		line-height: 25px;
		vertical-align: middle;
		text-align: center;
		height: 25px;
	}
	
	table {
		border-radius: .5em;
		overflow: hidden;
		width: 100%;
		padding:5px;
	}
	
	td {
		padding: 0px;
		border: 1px solid #ddd;
		width: 100%;
		box-shadow: 0 4px 8px 0 rgba(0,0,0,0.1);
		border-radius:3px;
	}
	.shadow{
		box-shadow: 0 4px 8px 0 rgba(0,0,0,0.5);
		transition: 0.3s;
	}
	.packing-status {
		font-size: 14px;
		text-color:grey;
	}
	.attachment {
		text-align: center;
		padding: 0px 5px;
		border: 1px solid #ddd;
		width: 20vw;
		display: inline-block;
		margin: 2vw;
		border-radius: 3px;
	}
	.attachment img {
		max-width: 100%;
		height: 20vh;
	}
	
	</style>
	<script type="text/javascript">
	$(document).ready(function() {
		$('.popup-gallery').magnificPopup({
			delegate: 'a',
			type: 'image',
			tLoading: 'Loading image #%curr%...',
			mainClass: 'mfp-img-mobile',
			gallery: {
				enabled: true,
				navigateByImgClick: true,
				preload: [0,1] // Will preload 0 - before current, and 1 after the current image
			},
			image: {
				tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
				titleSrc: function(item) {
				  return item.el.attr('title') + '<small></small>';
				}
			}
		});
	});
	</script>
</head>
<body id="loading">
<div class="site-wrapper">
	 <main class="site-content">
		<?php echo($check_in_details['views']); ?>
	 </main>
	 <footer>
		<div href="##" onClick="
                <?php            
                echo "location.replace('" . $goback_link . "')";
                ?>;" style="padding:5px">
			<p style="cursor: pointer; text-align: center; padding:8px" class="dropdown-div radius text_white back-button shadow"> Go Back </p>
		</div>
	 </footer>
</div>

</body>
<script>
	function initMap() {
		const start_lat = document.getElementById('start_latitude').value;
		const start_long = document.getElementById('start_longitude').value;
		const end_lat = document.getElementById('end_latitude').value;
		const end_long = document.getElementById('end_longitude').value;
		const center_lat = (parseFloat(start_lat)+parseFloat(end_lat))/2;
		const center_long = (parseFloat(end_lat)+parseFloat(end_long))/2;
		// const directionsService = new google.maps.DirectionsService();
		// const directionsRenderer = new google.maps.DirectionsRenderer();
		const map = new google.maps.Map(document.getElementById('map'), {
			zoom: 15,
			center: {lat: parseFloat(start_lat), lng: parseFloat(start_long)}
		});

		const checkInLoc = {lat: parseFloat(start_lat), lng: parseFloat(start_long)};
		const checkOutLoc = {lat: parseFloat(end_lat), lng: parseFloat(end_long)};
		const image =
    	"https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png";

		var checkInMarker = new google.maps.Marker({
			position: checkInLoc,
			map,
			title: "In!",
			label: "In",
		});

		var checkOutMarker = new google.maps.Marker({
			position: checkOutLoc,
			map,
			title: "Out!",
			label: "Out",
		});

		checkInMarker.setMap(map);
		checkOutMarker.setMap(map);
	}

</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB4NMvhFYOEFnIMjK9UqVoUzSJylTfJDEg&callback=initMap">
</script>
<!-- <script AIzaSyB0z_ElV7Y3s-3SBcnIzZCkDzVQNNBwm3M
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB41DRUbKWJHPxaFjMAwdrzWzbVKartNGg&callback=initMap&v=weekly"
      defer
    ></script> -->
</html>
