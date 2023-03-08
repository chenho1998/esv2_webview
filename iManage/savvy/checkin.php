<?php

if ($_POST['action'] == 'searchCheckIns')
{
	$json_object = json_decode($_POST['data'],true);
	
	$data = $json_object["searchCheckIns_data"];
	
	$_SESSION['dateFrom'] = $data['dateFrom'];
	$_SESSION['dateTo']   = $data['dateTo'];
	$_SESSION['customer-select'] = $data['customer_status'];
	$_SESSION['check-in-select'] = $data['check_in_status'];
	
	if(check_data_type_Iscorrect($data, "searchCheckIns"))
	{
		echo searchCheckIns($data);
	}
}


if ($_POST['action'] == 'getCheckInStatusAndCheckInDetailInOneTime')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["getCheckInStatusAndCheckInDetailInOneTime_data"];

    if(check_data_type_Iscorrect($data, "getCheckInStatusAndCheckInDetailInOneTime_data"))
    {
        echo getCheckInStatusAndCheckInDetailInOneTime_data($data);
    }
}
?>