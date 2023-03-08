<!DOCTYPE html>
<html lang="en">
   <?php

      session_start();
      require_once('getApprovalDetails.php');
      $client = '';
      $orderId = '';
      if(isset($_GET['orderId']) && isset($_GET['client'])){

          $client = $_GET['client'];
          $orderId = $_GET['orderId'];

          $param = array(
              "client"  => $client,
              "orderId" => $orderId,
          );

          $order_details = getApprovalDetails($param);
          $order_len=count($order_details);

        //   var_dump($order_details['query']);
      
      }else{
          header('location:Errorpage.php');
      }
      ?>


   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>EasyTech</title>


      <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
      <link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
      <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


   </head>


<body id="loading">

    <script>         
            
    $(document).ready(function(){
            
            $(document).on('click', '.approve-button', function() {
                let button            = $(this);
                let button_parent     = button.parent();

                let client   = "<?php echo $client; ?>";
                let order_id = "<?php echo $orderId; ?>";

                let item_id  = $(this).parent().data('id');

                var data = encodeURIComponent('{ "item_id":"' + item_id + '","order_id":"' + order_id + '","remarks":"' + 'remarks' + '","client":"' + client + '","action":"' + 'approve'+ '"}');

                jQuery.ajax({
                    type: "POST",
                    url: "./route.php",
                    data: "data=" + data + "&action=updateOrderApproval",

                    success: function (msg) {
                        console.log(msg);
                        msg = isJSONparsable(msg);

                        button.parent().empty();
                        button_parent.append('<button class="radius non_important_text delivery-buttons undo-button" style="color:gray;background-color:lightgray; border: 1px solid lightgray; width:25%;font-width: 10vw;"> Undo </button>');

                        if(order_id){
                            $('#order-table tr:last').find("td").attr("data-id",order_id);
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert('Technical Error! Please Contact Support.')
                    }
                });


            });


            $(document).on('click', '.reject-button', function() {
                let button            = $(this);
                let button_parent     = button.parent();

                let client   = "<?php echo $client; ?>";
                let order_id = "<?php echo $orderId; ?>";

                let item_id  = $(this).parent().data('id');

                var data = encodeURIComponent('{ "item_id":"' + item_id + '","order_id":"' + order_id + '","remarks":"' + 'remarks' + '","client":"' + client + '","action":"' + 'reject'+ '"}');

                jQuery.ajax({
                    type: "POST",
                    url: "./route.php",
                    data: "data=" + data + "&action=updateOrderApproval",

                    success: function (msg) {
                        console.log(msg);
                        msg = isJSONparsable(msg);

                        button.parent().empty();
                        button_parent.append('<button class="radius non_important_text delivery-buttons undo-button" style="color:gray;background-color:lightgray; border: 1px solid lightgray; width:25%;font-width: 10vw;"> Undo </button>');
        
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert('Technical Error! Please Contact Support.')
                    }
                });


            });


            $(document).on('click', '.undo-button', function() {
                let button            = $(this);
                let button_parent     = button.parent();

                let client   = "<?php echo $client; ?>";
                let order_id = "<?php echo $orderId; ?>";

                let item_id  = $(this).parent().data('id');

                var data = encodeURIComponent('{ "item_id":"' + item_id + '","order_id":"' + order_id + '","remarks":"' + 'remarks' + '","client":"' + client + '","action":"' + 'undo'+ '"}');

                jQuery.ajax({
                    type: "POST",
                    url: "./route.php",
                    data: "data=" + data + "&action=updateOrderApproval",

                    success: function (msg) {
                        console.log(msg);
                        msg = isJSONparsable(msg);
                        button.parent().empty();
                        button_parent.append('<button class="radius non_important_text delivery-buttons approve-button" style="width:25%;font-width: 10vw;"> Approve </button><button class="radius non_important_text delivery-buttons reject-button" style="background-color:rgb(255,85,45); border:1px solid rgb(255,85,45); width:25%;font-width: 10vw;"> Reject </button>');
        
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert('Technical Error! Please Contact Support.')
                    }
                });


            });


    });


    function isJSONparsable(value){
        if (typeof(value) !== "string"){
            return false;
        }
        try{
            return JSON.parse(value);
        }catch (error){
            return false;
        }
    }


    </script>



      <div class="site-wrapper">
         <main class="site-content">


            <?php
               if($order_details['views']){
                   echo($order_details['views']);
               }
            ?>
      </div>

      <div style="height:150px"></div>

        <div href="#" onclick="window.history.go(-1); return false;" style="padding:5px">
            <p style="cursor: pointer; text-align: center; padding:8px;
                                    position: fixed;bottom: 2%;width:95%"
            class="dropdown-div radius text_white back-button shadow"> Go Back </p>
        </div>


   </body>
</html>

















<style>
   *{
   cursor:pointer;
   }
   .column {
   float: left;
   width: 38%;
   padding-top: 5px;
   padding-bottom: 5px;
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
   /* min-height: 100vh; */
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
   
   .delivery-buttons {
    float: right;
    margin-inline-end: 5px;
    color:#e8ebef;
    border: 1px solid #147efb;
    background: #147efb;
    width: 32%;
    display: inline;
    text-align:center;
    padding: 5px;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
    transition: 0.3s;
    }
   .no_orders {
   font-size:13;
   color:grey;
   position: absolute;
   top: 50%;
   left: 50%;
   -moz-transform: translateX(-50%) translateY(-50%);
   -webkit-transform: translateX(-50%) translateY(-50%);
   transform: translateX(-50%) translateY(-50%);
   }
   .text {
   color:#147efb;
   text-align:left;
   }
   .text_white {
   color:#fff;
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
   color:white;
   font-weight: bold;
   background-color: rgba(128,128,128,0.7);
   padding: 0px;
   font-size:14px;
   box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
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
   input.button_add {
   line-height: 35px;
   height: 35px;
   padding-left: 25px;
   border: 1px solid #147efb;
   background: #fff url('./images/calendar.png') no-repeat 5px center;
   cursor: pointer;
   width:100%;
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
   .title-box {
   padding-top: 20px 0px 5px;
   text-align: center;
   }
   .btn-nueva {
   margin: 2px 0 14px 0;
   float:right;
   }
   .input-box {
   border-width: 0.5px;
   border-radius: 3px;
   padding: 3px 8px;
   height:25px;
   width:25%;
   border-color: #00000026;
   }
   input {
   border: 2px solid whitesmoke;
   border-radius: 5px;
   padding: 5px;
   text-align: left;
   height:35px;
   background-color:white;
   }
</style>