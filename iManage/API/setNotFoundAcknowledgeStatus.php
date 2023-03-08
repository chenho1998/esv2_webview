<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('./model/DB_class.php');

function setNotFoundAcknowledgeStatus($data)
{
	$client = "easysale_".$data['client'];
	$db = new DB();
	$db->connect_db_with_db_config($client);

	$query = "UPDATE cms_consignment LEFT JOIN cms_consignment_item ON cms_consignment.consign_id = cms_consignment_item.consign_id
		SET cms_consignment_item.packing_status = 3
		WHERE cms_consignment_item.packing_status = 2
		AND cms_consignment.salesperson_id = '" . $data['userId']. "' AND cms_consignment.cust_code = '" . $data['custCode']. "'";

	$db->query($query);

	$query = "UPDATE cms_consignment LEFT JOIN cms_consignment_item ON cms_consignment.consign_id = cms_consignment_item.consign_id
		LEFT JOIN cms_consignment_item_message ON cms_consignment_item.consign_item_id = cms_consignment_item_message.consign_item_id
		SET  cms_consignment_item_message.read_status = 1
		WHERE cms_consignment.salesperson_id = '" . $data['userId']. "' AND cms_consignment.cust_code = '" . $data['custCode']. "'";

	$db->query($query);

	$query = "SELECT COUNT(*) AS amount FROM cms_consignment LEFT JOIN cms_consignment_item ON cms_consignment.consign_id = cms_consignment_item.consign_id
		WHERE cms_consignment_item.packing_status = 2
		AND cms_consignment.salesperson_id = '" . $data['userId']. "' AND cms_consignment.cust_code = '" . $data['custCode']. "'";

	$db->query($query);
	$result = $db->fetch_array();

	$json = array();
	$json['custCode'] = $data['custCode'];
	$json['result'] = $result['amount'];

	return json_encode($json);
}

?>
