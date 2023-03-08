<?php

session_start();
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function getCashRollingView($data)
{
	$client                     = $data['client'];
	


    $config = parse_ini_file('../config.ini',true);

	$settings                   = $config[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

	$json_data['connection'] = json_encode(array(
		"user"=>$db_user,
		"password"=>$db_pass,
		"db"=>$db_name,
		"client"=>$client,
		"con_success"=>$con_1
	));

	$_SESSION["_cdate_from"] 	= empty($data['dateFrom'])?'': $data['dateFrom'];
    $_SESSION["_cdate_to"] 		= empty($data['dateTo'])?'': $data['dateTo'];

    if(empty($data['dateFrom'])){
        $data['dateFrom'] = date('Y-m-01');
        $data['dateTo'] = date('Y-m-t');
    }
    $values = array();
    $brought_forward = 0;

    $query = "select sum(cr_amount*if(cr_type='Out',-1,1)) as bf_amount from cms_cash_rolling where active_status = 1 and salesperson_id = '{$data['salespersonId']}' and date(cr_date) < '{$data['dateFrom']}'";

    // $values[] = $query;

    $db->query($query);
    if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
            $brought_forward = floatval($result['bf_amount']);
            
            $brought_forward_color = 'rgb(255,85,45)';
            if($brought_forward >= 0){
                $brought_forward_color = 'green';
            }
            $values[] = '<table style="width:100%;padding:5px;border:1px solid grey;border-radius:5px;margin:auto;width:90%">
                            <colgroup>
                                <col width="50%">
                                <col width="50%">
                            </colgroup>
                            <tr>
                                <td style="font-weight:bold;font-size:12px;color:grey">BROUGHT FORWARD</td>
                                <td style="font-weight:bold;font-size:17px;text-align:right;color:'.$brought_forward_color.'">
                                    <label style="font-weight:normal;font-size:12px;">RM</label>'.number_format($brought_forward,2).'</td>
                            </tr>
                        </table>';
        }
    }

    $query = "select cr_type, cr_amount, cr_remark, date_format(cr_date,'%d/%m/%Y') as cr_date from cms_cash_rolling where salesperson_id = '{$data['salespersonId']}' and active_status = 1 and date(cr_date) between '{$data['dateFrom']}' and '{$data['dateTo']}' order by cr_date;";

    // $values[] = $query;

	$db->query($query);

    $balance = 0;
	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
            $cr_amount = floatval($result['cr_amount']);
			$cr_type = $result['cr_type'];
            if($cr_type == 'In'){
                $balance += $cr_amount;
            }
            if($cr_type == 'Out'){
                $balance += $cr_amount;
            }

            $color = '#147efb';
            if($cr_type == 'Out'){
                $color = 'rgb(255,85,45)';
            }

            $balance_color = $balance > 0 ?"green":"rgb(255,85,45)";

            $values[] = '<div style="cursor: pointer;padding-left:5px">
                            <p class="title" style="color:grey;">'.$result['cr_date'].' <label style="color:black;">'.$result['cr_remark'].'</label></p>
                            <table style="width:100%">
                                <colgroup>
                                    <col width="50%">
                                    <col width="50%">
                                </colgroup>
                                <tr>
                                    <td style="font-weight:bold;font-size:17px;color:'.$color.'">
                                        <label style="font-weight:normal;font-size:12px;color:'.$color.'">RM</label>'.number_format($result['cr_amount'],2).'</td>
                                    <td style="font-weight:bold;font-size:17px;text-align:right;color:'.$balance_color.'">
                                        <label style="font-weight:normal;font-size:12px;">RM</label>'.number_format($balance,2).'</td>
                                </tr>
                            </table>
                        </div><hr>';
		}
	}
    $values[] = '<div class="bottom-bar">
        BALANCE: <label style="font-size:20px;margin:auto"><strong>RM'.number_format($balance,2).'</strong></label>
    </div>';

    $values[] = '<div style="height:100px;"/>';

    $values = implode('',$values);
	return $values;
}
?>
