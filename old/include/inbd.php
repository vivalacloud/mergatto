<?php 

include_once("bd.php");
if(!isset($manejador)) {
	$manejador = db_connect();
}


function isInBD($table,$filter=array()){
	global $manejador;
	global $conf;
	
	$query = "select * from ".$conf["bdprefix"].$table." where ";
	$and="";
	foreach($filter as $key => $value) {
		if($key=="complex"){
			$query.=$and." (".$value.") ";
		}else{
			$query.=$and." ".$key." ".$value["operation"]." '".$value["value"]."' ";		
		}
		$and="and";
	}
	$query.=$and." 1";
	$r = db_query($query,$manejador);
	if(db_count($r) > 0) {
		return true;
	}
	return false;
}

function getInBD($table,$filter=array(),$fields = array(),$order="",$limit=""){
	global $manejador;
	global $conf;
	
	$query = "select ";
	$selected_fields = "*";
	if(!empty($fields)){
		$selected_fields = "";
		$coma = "";
		foreach ($fields as $key => $field){
			$selected_fields.= $coma." ".$field;
			$coma=",";
		}
	}
	$query .= $selected_fields;
	$query .= " from ".$conf["bdprefix"].$table." where ";
	$and="";
	foreach($filter as $key => $value) {
		if($key=="complex"){
			$query.=$and." (".$value.") ";
		}else{
			$query.=$and." ".$key." ".$value["operation"]." '".$value["value"]."' ";		
		}
		$and="and";
	}
	$query.=$and." 1";
	if($order!=""){
		$query.=" order by ".$order;
	}
	if($limit!=""){
		$query.=" limit ".$limit;
	}
	$r = db_query($query,$manejador);
	if(db_count($r) > 0) {
		return db_fetch($r);
	}
	return false;
}

function listInBD($table,$filter=array(),$fields = array(),$order="",$group_by="",$limit=""){
	global $manejador;
	global $conf;
	
	$query = "select ";
	$selected_fields = "*";
	if(!empty($fields)){
		$selected_fields = "";
		$coma = "";
		foreach ($fields as $key => $field){
			$selected_fields.= $coma." ".$field;
			$coma=",";
		}
	}
	$query .= $selected_fields;
	$query .= " from ".$conf["bdprefix"].$table." where ";
	$and="";
	foreach($filter as $key => $value) {
		if($key=="complex"){
			$query.=$and." (".$value.") ";
		}else{
			$query.=$and." ".$key." ".$value["operation"]." '".$value["value"]."' ";		
		}
		$and="and";
	}
	$query.=$and." 1";
	if($order!=""){
		$query.=" order by ".$order;
	}
	if($group_by!=""){
		$query.=" group by ".$group_by;
	}
	if($limit!=""){
		$query.=" limit ".$limit;
	}
	//error_log($query);
	$r = db_query($query,$manejador);
	$i=0;
	$data_array=array();
	while($data=db_fetch($r)) {
		$data_array[$i]=$data;
		$i++;
	}
	return $data_array;
}

function countInBD($table,$filter=array(),$group_by=""){
	global $manejador;
	global $conf;
	
	$query = "select count(*) as c from ".$conf["bdprefix"].$table." where ";
	$and="";
	foreach($filter as $key => $value) {
		if($key=="complex"){
			$query.=$and." (".$value.") ";
		}else{
			$query.=$and." ".$key." ".$value["operation"]." '".$value["value"]."' ";		
		}
		$and="and";
	}
	$query.=$and." 1";
	if($group_by!=""){
		$query.=" group by ".$group_by;
	}
	$r = db_query($query,$manejador);
	$r = db_fetch($r);
	return $r["c"];
}

function sumInBD($table,$filter=array(),$sum_field){
	
	global $manejador;
	global $conf;
	
	$query = "select sum(".$sum_field.") as s from ".$conf["bdprefix"].$table." where ";
	$and="";
	foreach($filter as $key => $value) {
		if($key=="complex"){
			$query.=$and." (".$value.") ";
		}else{
			$query.=$and." ".$key." ".$value["operation"]." '".$value["value"]."' ";		
		}
		$and="and";
	}
	$query.=$and." 1";
	$r = db_query($query,$manejador);
	$r = db_fetch($r);
	return $r["s"];
}



function updateInBD($table,$filter=array(),$update_data = array()){
	global $manejador;
	global $conf;
	
	$query = "update ".$conf["bdprefix"].$table." set ";
	$coma = "";
	foreach($update_data as $key=>$value){
		$query .= $coma.$key." = '".$value."'";
		$coma=",";
	}
	
	$query .= " where ";
	$and="";
	foreach($filter as $key => $value) {
		if($key=="complex"){
			$query.=$and." (".$value.") ";
		}else{
			$query.=$and." ".$key." ".$value["operation"]." '".$value["value"]."' ";		
		}
		$and="and";
	}
	$query.=$and." 1";
	$r = db_query($query,$manejador);
	return true;
}

function addInBD($table,$data){
	global $manejador;
	global $conf;
	
	$query = "insert into ".$conf["bdprefix"].$table."  (";
	$coma = "";
	$values = "";
	foreach($data as $key => $value) {
		$query .= $coma.$key;
		$values .= $coma."'".db_secure_field($value,$manejador)."'";
		$coma = ",";
	}
	$query .= ") VALUES (".$values.")";
	$r = db_query($query,$manejador);
	return db_last_id();
}

function deleteInBD($table,$filter=array()) {
	global $manejador;
	global $conf;

	$query = "delete from ".$conf["bdprefix"].$table." where";
	$and="";
	foreach($filter as $key => $value) {
		if($key=="complex"){
			$query.=$and." (".$value.") ";
		}else{
			$query.=$and." ".$key." ".$value["operation"]." '".$value["value"]."' ";		
		}
		$and="and";
	}
	$query.=$and." 1";
	$r = db_query($query,$manejador);
}

?>