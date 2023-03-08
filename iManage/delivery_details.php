<!DOCTYPE html>
<html lang="en">
<?php
session_start();
$client = '';
$userId = '';
$job_no = '';

require_once('./model/DB_class.php');

$settings                       = parse_ini_file('../config.ini',true);

if(isset($_GET['userId']) && isset($_GET['client']) && isset($_GET['do_code'])){

    $client         = $_GET['client'];
    $userId         = $_GET['userId'];
    $do_code        = $_GET['do_code'];
    //$do_code        = str_replace('\\','\\\\',$do_code);
    $do_code_post   = str_replace('\\','\\\\',$do_code);

    $db                         = new DB();
    $settings                   = $settings[$client];
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';
    
    $db = new DB();
    $con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $db2 = new DB();
    $con_2 = $db2->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);
    
    $query = "SELECT cms_do.*, c.billing_address1, c.billing_address2, c.billing_address3, c.billing_address4, c.billing_city, c.cust_company_name FROM cms_do LEFT JOIN cms_customer c ON c.cust_code = cms_do.cust_code WHERE do_code ='".$do_code_post."'";

    $db->query($query);

    while($result = $db->fetch_array())
	{
       $do_code = $result['do_code'];
       $cust_code = $result['cust_code'];
       $do_date = $result['do_date'];
       $do_amount = $result['do_amount'];

       $billing_address1 = $result['billing_address1'];
       $billing_address2 = $result['billing_address2'];
       $billing_address3 = $result['billing_address3'];
       $billing_address4 = $result['billing_address4'];
       $billing_city = $result['billing_city'];
       $cust_company_name = $result['cust_company_name'];

       $address = $billing_address1 ? $billing_address1 : '';
       $address.= $billing_address2 ? '<br>'.$billing_address2 : '';
       $address.= $billing_address3 ? '<br>'.$billing_address3 : '';
       $address.= $billing_address4 ? '<br>'.$billing_address4 : '';
       $address.= $billing_city ? $billing_city : '';

       //$billing_address2.$billing_address3.$billing_address4.$billing_city

        $view = '<div><div style="padding:2%;border:2px solid #CDCDCD;"><b style="font-size:12pt;">'.$cust_company_name.'</b><br><font style="font-size:10pt;">'.$address.'</font></div><table>';

        $product_query = "SELECT * FROM cms_do_details WHERE do_code = '".$do_code_post."'";
        $db2->query($product_query);
        $i++;
        while($result2 = $db2->fetch_array())
        {

            $product_code = $result2['item_code'];
            $product_name = $result2['item_name'];
            $item_price = $result2['item_price'];
            $item_price = number_format($item_price,2,'.',',');
            $quantity = $result2['quantity'];
            $total_price = $result2['total_price'];
            $total_price = number_format($total_price,2,'.',',');
            $uom = $result2['uom'];
            $discount = $result2['discount'];

            $view .='
            <tr>
                <td>
                    <p style="color:#000;font-weight:bold">('.$i.')'.$product_code.' - '.$product_name.'
                    <p class="packing-status"></p>
                    <div class="row">
                    <div class="column" height="auto">
                        <center style="color:black">RM '.$item_price.'</center>
                    </div>
                    <div class="column" style="padding-right:5px;width:24%">
                        <center style="color:#147efb;font-weight:bold;">QTY '.$quantity.'</center>
                    </div>
                    <div class="column">
                        <center style="color:black;font-weight:bold;">RM '.$total_price.'</center>
                    </div>
                </td>
            </tr>
            ';
            $i++;
        }

        $view .='<tr>
                    <td style="font-weight:bold">
                        <p style="text-align:center;color:#147efb;" onclick="goBack();">GO BACK</p>
                    </td>
                </tr>';

        $view .='</table></div>';
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
<style>
.column {
    float: left;
    width: 38%;
    /* padding-top: 5px; */
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
</style>
<body>
    <?php echo $view; ?>

    <script>
        function goBack(){
            history.back(-1);
        }
    </script>
</body>
</html>
