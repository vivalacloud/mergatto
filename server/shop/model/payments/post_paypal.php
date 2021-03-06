<?php

	/************************************************************
  * Mergatto
  * Author: Pablo Gutierrez Alfaro <enrealidadeshotmail@gmail.com.com>
  * Creation Modification:
  * Last Modification:
  * licensed through Copyright 2015
  *
  ************************************************************/


  /*********************************************************
  * ACTIONS
  *
  *
  *********************************************************/

	error_reporting(0);

  /*********************************************************
  * COMMON AJAX CALL DECLARATIONS, DATA CHECK AND INCLUDES
  *********************************************************/

	define('PATH', '../../');
	$timestamp=strtotime(date("Y-m-d H:i:s"));
	include(PATH."include/inbd.php");
	$page_path="WS::PostPaypal";
	debug_log("START");

	/*********************************************************
  * AJAX OPERATIONS
  *********************************************************/

	$_POST['custom']=2671;

	$table='order_request';
	$filter=array();
	$filter["id_order"]=array("operation"=>"=","value"=>$_POST["custom"]);
	$data=array();
	$data["payed"]=1;
	updateInBD($table,$filter,$data);

	/*********************************************************
  * AJAX CALL RETURN
  *********************************************************/

  debug_log("END");
  die();

?>
