<?php
	/************************************************************
	* Royappty
	* Author: Pablo Gutierrez Alfaro <pablo@royappty.com>
	* Last Modification: 10-02-2014
	* Version: 1.0
	* licensed through CC BY-NC 4.0
	************************************************************/

	function explode_input_data(){
		global $page_path;
		global $_POST;
		global $_GET;

		if(@issetandnotempty($_POST["input_data"])){
			$tmp=explode("//",$_POST["input_data"]);
			foreach($tmp as $key=>$value){
				$tmp2=explode("||",$value);
				$_POST[$tmp2[0]]=$tmp2[1];
			}
		}
		if(@issetandnotempty($_GET["input_data"])){
			$tmp=explode("//",$_GET["input_data"]);
			foreach($tmp as $key=>$value){
				$tmp2=explode("||",$value);
				$_GET[$tmp2[0]]=$tmp2[1];
			}
		}
	}

	function create_app_folder($_PATH,$app_project_codename){
		global $page_path;

		debug_log("[resources/mobile-app/".$app_project_codename."] Check Folder");

		if (!file_exists($_PATH."resources/mobile-app/".$app_project_codename)) {
			debug_log("[resources/mobile-app/".$app_project_codename."] Folder not exits");
		   	mkdir($_PATH."resources/mobile-app/".$app_project_codename, 0777, true);
		   	debug_log("[resources/mobile-app/".$app_project_codename."] Folder created");
		}else{
			debug_log("[resources/mobile-app/".$app_project_codename."] Folder exits");
		}

		debug_log("[resources/mobile-app/".$app_project_codename."/app_icon.png] Check File");
		if (!file_exists($_PATH."resources/mobile-app/".$app_project_codename."/app_icon.png")) {
			debug_log("[resources/mobile-app/".$app_project_codename."/app_icon.png] File not exits");
		}else{
			unlink($_PATH."resources/mobile-app/".$app_project_codename."/app_icon.png");
			debug_log("[resources/mobile-app/".$app_project_codename."/app_icon.png] File deleted");
		}
		copy($_PATH."resources/defaults/app_icon.png",$_PATH."resources/mobile-app/".$app_project_codename."/app_icon.png");

		debug_log("[resources/mobile-app/".$app_project_codename."/app_bg.png] Check File");
		if (!file_exists($_PATH."resources/mobile-app/".$app_project_codename."/app_bg.png")) {
			debug_log("[resources/mobile-app/".$app_project_codename."/app_bg.png] File not exits");
		}else{
			unlink($_PATH."resources/mobile-app/".$app_project_codename."/app_bg.png");
			debug_log("[resources/mobile-app/".$app_project_codename."/app_bg.png] File deleted");
		}
		copy($_PATH."resources/defaults/app_bg.png",$_PATH."resources/mobile-app/".$app_project_codename."/app_bg.png");


	}

	function sendMessageToiOs($deviceToken, $collapseKey, $messageText, $messageTitle, $key_path) {
		global $page_path;

		debug_log("[sendMessageToiOs] START");
		debug_log("[sendMessageToiOs] ios_key: ".$deviceToken);
		$apnsHost = 'gateway.sandbox.push.apple.com';
		$apnsPort = 2195;
		//$apnsCert = $server_path."resources/mobile-app/".$app_project_codename."ios_certificate.pem";
		$apnsCert = $key_path;
		if(file_exists($key_path)){
			debug_log("[sendMessageToiOs] File exists");
			// Setup stream:
			$streamContext = stream_context_create();
			stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);

			// Open connection:
			$apns = stream_socket_client(
			    'ssl://' . $apnsHost . ':' . $apnsPort,
			    $error,
			    $errorString,
			    2,
			    STREAM_CLIENT_CONNECT,
			    $streamContext
			);

			if (strlen($messageText) > 125) {
			    $messageText = substr($messageText, 0, 125) . '...';
			}

			$payload['aps'] = array('alert' => $messageText, 'badge' => 1, 'sound' => 'default');
			$payload = json_encode($payload);

			// Send the message:

			$apnsMessage
			    = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $deviceToken)) . chr(0) . chr(strlen($payload))
			    . $payload;
			 fwrite($apns, $apnsMessage);

			// Close connection:
			@socket_close($apns);
			fclose($apns);
			debug_log("[sendMessageToiOs] END");
			return true;
		}else{
			debug_log("[sendMessageToiOs] File not exists");
			debug_log("[sendMessageToiOs] END");
			return false;
		}



	}



	function sendMessageToAndroid($deviceToken, $collapseKey, $messageText, $messageTitle, $yourKey) {
		global $page_path;
		global $response;

		debug_log("[sendMessageToAndroid] START");
		debug_log("[sendMessageToAndroid] ".$messageTitle);
		$headers = array('Authorization:key=' . $yourKey);
		$data = array(
				'registration_id' => $deviceToken,
				'collapse_key' => $collapseKey,
				'data.title' => $messageTitle,
				'data.message' => $messageText);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$res = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (curl_errno($ch)) {
			$response["result"]=false;
			debug_log("Send Message Error");
	  		$response["error_code"]="send_message_error";
			return false;
	 		die();
		}
		if ($httpCode != 200) {
			$response["result"]=false;
			debug_log("Send Message Error http_code<>200 (".$httpCode.")");
	  		$response["error_code"]="send_message_error";
			return false;
	 		die();
		}
		curl_close($ch);
		debug_log("[sendMessageToAndroid] END");
		return $res;

	}

	// Image resize function with php + gd2 lib
	function imageresize($source, $destination, $width = 0, $height = 0, $crop = false, $quality = 100) {
		$quality = $quality ? $quality : 80;
		$image = imagecreatefromstring($source);
		if ($image) {
				// Get dimensions
				$w = imagesx($image);
				$h = imagesy($image);
				if (($width && $w > $width) || ($height && $h > $height)) {
						$ratio = $w / $h;
						if (($ratio >= 1 || $height == 0) && $width && !$crop) {
								$new_height = $width / $ratio;
								$new_width = $width;
						} elseif ($crop && $ratio <= ($width / $height)) {
								$new_height = $width / $ratio;
								$new_width = $width;
						} else {
								$new_width = $height * $ratio;
								$new_height = $height;
						}
				} else {
						$new_width = $w;
						$new_height = $h;
				}
				$x_mid = $new_width * .5;  //horizontal middle
				$y_mid = $new_height * .5; //vertical middle
				// Resample
				$new = imagecreatetruecolor(round($new_width), round($new_height));
				imagecopyresampled($new, $image, 0, 0, 0, 0, $new_width, $new_height, $w, $h);
				// Crop
				if ($crop) {
						$crop = imagecreatetruecolor($width ? $width : $new_width, $height ? $height : $new_height);
						imagecopyresampled($crop, $new, 0, 0, ($x_mid - ($width * .5)), 0, $width, $height, $width, $height);
						//($y_mid - ($height * .5))
				}
				// Output
				// Enable interlancing [for progressive JPEG]
				imageinterlace($crop ? $crop : $new, true);

				$dext = strtolower(pathinfo($destination, PATHINFO_EXTENSION));
				if ($dext == '') {
						$dext = $ext;
						$destination .= '.' . $ext;
				}
				switch ($dext) {
						case 'jpeg':
						case 'jpg':
								imagejpeg($crop ? $crop : $new, $destination, $quality);
								break;
						case 'png':
								$pngQuality = ($quality - 100) / 11.111111;
								$pngQuality = round(abs($pngQuality));
								imagepng($crop ? $crop : $new, $destination, $pngQuality);
								break;
						case 'gif':
								imagegif($crop ? $crop : $new, $destination);
								break;
				}
				@imagedestroy($image);
				@imagedestroy($new);
				@imagedestroy($crop);
		}
	}

	function corporate_email($mail_for,$mail_subject,$content){
		global $url_server;
		global $_CONFIG;
		global $lang_email;
		global $s;
		global $page_path;

		$mail_content ="
			<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
			<html  b:version='2' class='v2' expr:dir='data:blog.languageDirection' xmlns='http://www.w3.org/1999/xhtml' dir='ltr' lang='".$lang_email."' xml:lang='".$lang_email."' xmlns:b='http://www.google.com/2005/gml/b' xmlns:b='http://www.google.com/2005/gml/b' xmlns:data='http://www.google.com/2005/gml/data' xmlns:expr='http://www.google.com/2005/gml/expr' xmlns:og='http://opengraphprotocol.org/schema/'>
			<head>
				<meta http-equiv='content-type' content='text/html; charset=utf-8' />
			</head>
			<style type='text/css'>
				a{ color:color:#666; }
				a:hover{ color:#000; }
				b{ color:#000; font-weight:300 }
				.important{ color:#000; }
				.uppercase{ text-transform: uppercase; }
				.underline{ text-decoration:underline; }
				th{}
				td{ padding:5px 10px; }
				.preview img{ height:100px; }
				.right{ text-align:right; }
				.left{ text-align:left; }
				.semifooter{ padding-top:20px; font-size:10px; }
				h3{ font-size:14px; color:#000; font-weight:300}
			</style>
			<body style='font-family: \"Open Sans\", sans-serif;margin:0;padding:0'>
				<div style='display:block;margin:auto;margin:20px 0px 30px 0px;text-align:center'>
					<img style='margin:auto;min-width:300px;max-width:300px' src='".$url_server.$_CONFIG["company_logo_path"]."'/>
				</div>
				<div class='content' style='margin:20px 20px 40px 20px;font-weight:100;font-size:14px;'>
				".$content."
				</div>

				<div style='display:block;margin:auto;padding:40px 0px;background-color:#f4f4f4;overflow:auto;color:#666 !important;font-size:10px;'>
					<div style='float:left;padding-left:20px;padding-bottom:10px;'>
						<div>
							<a href='http://www.okycoky.net/classics/'>
								<img style='min-height:30px;max-height:30px;padding-bottom:10px;' src='".$url_server.$_CONFIG["company_logo_path"]."'/>
							</a>
						</div>
						".$_CONFIG["company_street"]."<br/>
						".$_CONFIG["company_town"]." ".$_CONFIG["company_country"]."<br/>
						".$_CONFIG["company_phone"]."<br/>
						".$_CONFIG["company_info_mail"]."<br/>
					</div>
					<div style='float:right;padding-right:20px;text-align:right;width:300px;font-size:11px;padding-bottom:10px;'>
						<div style='font-weight:bold'>".htmlentities($s["follow_us"], ENT_QUOTES, "UTF-8")."</div>
						<div style='text-align:right;margin-top:5px;'>
							".htmlentities($s["follow_us_content"], ENT_QUOTES, "UTF-8")."
						</div>
					</div>
				</div>
				<div style='text-align:center;background:#fff;padding:20px;font-weight:100;font-size:12px;'>
					<p>".date('Y').htmlentities(" © ".$_CONFIG["company_name"], ENT_QUOTES, "UTF-8")."</p>
					<p>".$_CONFIG["footer_mail"]."</p>
				</div>
			</body>
			</html>";
			$mail_header="Content-type: text/html\r\nFrom: ".$_CONFIG["mail_header_email"];
			debug_log("Send corportate email (for:".$mail_for.",subject:".$mail_subject.") START");
			debug_log($mail_content);
			//mail($mail_for,$mail_subject,$mail_content,$mail_header);
			debug_log("Send corportate email END");
			return true;
	}


	function create_block_data($block_data_code,$data1="",$data2=""){

		$block_data="?";
		switch ($block_data_code){
			case "campaigns":
				$table="campaigns";
				$filter=array();
				$filter["id_brand"]=array("operation"=>"=","value"=>$data1);
				$filter["status"]=array("operation"=>"=","value"=>1);
				$block_data=countInBD($table,$filter);
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;

			case "usage_this_today":
				$month=strtotime(date("Y-m-d 00:00:00",strtotime("-1 month")));
				$table="campaigns";
				$filter=array();
				$filter["id_brand"]=array("operation"=>"=","value"=>$data1);
				$filter["created"]=array("operation"=>">","value"=>$month);
				$block_data=countInBD($table,$filter);
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;
			case "usage_this_month":
				$day=strtotime(date("Y-m-d 00:00:00"));
				$table="campaigns";
				$filter=array();
				$filter["id_brand"]=array("operation"=>"=","value"=>$data1);
				$filter["created"]=array("operation"=>">","value"=>$day);
				$block_data=countInBD($table,$filter);
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;
			case "users":
				$table="users";
				$filter=array();
				$filter["id_brand"]=array("operation"=>"=","value"=>$data1);
				$filter["active"]=array("operation"=>"=","value"=>1);
				$block_data=countInBD($table,$filter);
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;

			case "admin_validated_this_month":
				$month=strtotime(date("Y-m-d 00:00:00",strtotime("-1 month")));
				$table="validated_codes_month_summaries";
				$filter=array();
				$filter["id_admin"]=array("operation"=>"=","value"=>$data1);
				$filter["start"]=array("operation"=>"=","value"=>$month);
				$tmp=getInBD($table,$filter);
				$block_data=$tmp["validated_codes_amount"];
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;
			case "admin_validated_this_today":
				$day=strtotime(date("Y-m-d 00:00:00"));
				$table="validated_codes_day_summaries";
				$filter=array();
				$filter["id_admin"]=array("operation"=>"=","value"=>$data1);
				$filter["start"]=array("operation"=>"=","value"=>$day);
				$tmp=getInBD($table,$filter);
				$block_data=$tmp["validated_codes_amount"];
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;
			case "admin_validated":
				$table="validated_codes_month_summaries";
				$filter=array();
				$filter["id_admin"]=array("operation"=>"=","value"=>$data1);
				$sumfield="validated_codes_amount";
				$block_data=countInBD($table,$filter);
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;
			case "campaign_usage_this_month":
				$month=strtotime(date("Y-m-d 00:00:00",strtotime("-1 month")));
				$table="used_codes_day_summaries";
				$filter=array();
				$filter["id_campaign"]=array("operation"=>"=","value"=>$data1);
				$filter["start"]=array("operation"=>">","value"=>$month);
				$sumfield="used_codes_amount";
				$block_data=sumInBD($table,$filter,$sumfield);
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;
			case "campaign_usage_today":
				$day=strtotime(date("Y-m-d 00:00:00"));
				$table="used_codes_day_summaries";
				$filter=array();
				$filter["id_campaign"]=array("operation"=>"=","value"=>$data1);
				$filter["start"]=array("operation"=>"=","value"=>$day);
				$tmp=getInBD($table,$filter);
				$block_data=$tmp["used_codes_amount"];
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;
			case "campaign_usage_total":
				$table="used_codes_month_summaries";
				$filter=array();
				$filter["id_campaign"]=array("operation"=>"=","value"=>$data1);
				$sumfield="used_codes_amount";
				$block_data=sumInBD($table,$filter,$sumfield);
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;

			case "group_usage_this_month":
				$month=strtotime(date("Y-m-d 00:00:00",strtotime("-1 month")));
				$table="user_groups";
				$filter=array();
				$filter["id_group"]=array("operation"=>"=","value"=>$data1);
				$block_data=0;
				if(isInBD($table,$filter)){
						$user_groups=listInBD($table,$filter);
						$table="used_codes_user_day_summaries";
						$filter=array();
						$filter["start"]=array("operation"=>">","value"=>$month);
						$filter["complex"]="";
						$or="";
						foreach($user_groups as $key => $user_group){
								$filter["complex"].=$or."id_user=".$user_group["id_user"];
								$or=" or ";
						}
						$sumfield="used_codes_amount";
						$block_data=sumInBD($table,$filter,$sumfield);
				}
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;

			case "group_usage_today":
				$day=strtotime(date("Y-m-d 00:00:00"));
				$table="user_groups";
				$filter=array();
				$filter["id_group"]=array("operation"=>"=","value"=>$data1);
				$block_data=0;
				if(isInBD($table,$filter)){
						$user_groups=listInBD($table,$filter);
						$table="used_codes_user_day_summaries";
						$filter=array();
						$filter["start"]=array("operation"=>">","value"=>$day);
						$filter["complex"]="";
						$or="";
						foreach($user_groups as $key => $user_group){
								$filter["complex"].=$or."id_user=".$user_group["id_user"];
								$or=" or ";
						}
						$sumfield="used_codes_amount";
						$block_data=sumInBD($table,$filter,$sumfield);
				}
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;

			case "group_users":
				$table="user_groups";
				$filter=array();
				$filter["id_group"]=array("operation"=>"=","value"=>$data1);
				$block_data=countInBD($table,$filter);
				if(!@issetandnotempty($block_data)){$block_data=0;}
				break;

		}

		return $block_data;
	}

	function checkClosed(){
		global $page_path;
		global $response;
		global $_CONFIG;

		if($_CONFIG["close"]){
			$response["result"]=false;
			debug_log("ERROR System Closed");
			$response["error"]="ERROR System Closed";
			$response["error_code"]="system_closed";
			return false;
		}
		return true;
	}

	function checkBrand($brand){
		global $page_path;
		global $response;

		if(!@issetandnotempty($brand["id_brand"])){
		 	$response["result"]=false;
			debug_log("ERROR Data Missing id_brand");
	 		$response["error"]="ERROR Data Missing brand identificator";
	  		$response["error_code"]="no_brand";
	 		return false;
	 		die();
	 	}
	 	$table="brands";
	 	$filter=array();
	 	$filter["id_brand"]=array("operation"=>"=","value"=>$brand["id_brand"]);
	 	$filter["active"]=array("operation"=>"=","value"=>1);
	 	if(!isInBD($table,$filter)){
		 	$response["result"]=false;
			debug_log("ERROR Brand not exists or inactive (id_brand=".$brand["id_brand"]." | active=1)");
	 		$response["error"]="ERROR Data Missing not exists or inactive";
	 		$response["error_code"]="brand_not_valid";
	 		return false;
	 		die();
	 	}
	 	return true;
	 	die();

	}

	function checkBDConnection(){
		global $page_path;
		global $response;
		global $db_connection;

		if(!$db_connection["status"]){
			error_log("[ERROR] [".$page_path."] Can't connect with DB");
			$response["result"]=false;
			$response["error"]="[ERROR] Can't connect with DataBase";
			$response["error_code"]="db_connection_error";
			return false;
		}
		return true;
	}

	function checkControlerAction(){
		global $page_path;
		global $response;
		global $_GET;
		global $_POST;

		if(!(@issetandnotempty($_POST["action"])||@issetandnotempty($_GET["action"]))){
			error_log("[ERROR] [".$page_path."] Controler Action empty");
			$response["result"]=false;
			$response["error"]="[ERROR] Controler Action empty";
			$response["error_code"]="empty_controler_action";
			return false;
			die();
		}

		return true;
		die();
	}

	function checkInputData($data,$key=""){
		global $page_path;
		global $response;

		if(!@issetandnotempty($data)){
			$response["result"]=false;
			debug_log("ERROR Input Data Missing ".$key);
			$response["error_code"]="input_data_missing";
			return false;
			die();
		}
		return true;
	}

	function checkInputDataZeroValid($data){
		global $page_path;
		global $response;

		if(!@isset($data)){
			$response["result"]=false;
			debug_log("ERROR Input Data Missing");
			$response["error_code"]="input_data_missing";
			return false;
			die();
		}
		return true;
	}

	function getActionData(){
		global $response;
		global $_GET;
		global $_POST;
		global $action;
		global $action_data;


		$response["result"]=true;
		$action_data=array();

		if(@issetandnotempty($_GET["action"])){
			$action=$_GET["action"];
			unset($_POST["action"]);
			$action_data=$_GET;
		}
		if(@issetandnotempty($_POST["action"])){
			$action=$_POST["action"];
			unset($_POST["action"]);
			$action_data=$_POST;
		}
		debug_log("[Action] ".$action);

		foreach ($action_data as $key=>$value){
			debug_log("[Action Data] ".$key.":".substr_dots($value,25));
		}
	}

	function checkAction(){
		global $page_path;
		global $response;
		global $_GET;
		global $_POST;

		if(!(@issetandnotempty($_POST["action"])||@issetandnotempty($_GET["action"]))){
			error_log("[ERROR] [".$page_path."] Controler Action empty");
			$response["result"]=false;
			$response["error_code"]="empty_controler_action";
			return false;
		}
		return true;
	}

	function notValidAction(){
		global $page_path;
		global $response;
		global $action;

		$response["result"]=false;
		error_log("[ERROR] [".$page_path."] Controler Action not valid (".$action.")");
		$response["error_code"]="action_not_valid";
	}

	function checkAdmin($admin){
		global $page_path;
		global $response;

		debug_log("Check Admin START");

		if(!@issetandnotempty($admin["email"])){
			$response["result"]=false;
			debug_log("ERROR Data Missing id_admin");
			$response["error"]="ERROR Data Missing email identificator";
			$response["error_code"]="no_admin";
			return false;
		}

		if(!@issetandnotempty($admin["session_key"])){
			$response["result"]=false;
			debug_log("ERROR Data Missing id_admin");
			$response["error"]="ERROR Data Missing key identificator";
			$response["error_code"]="no_admin";
			return false;
		}

	 	$table="admins";
	 	$filter=array();
		$filter["email"]=array("operation"=>"=","value"=>$admin["email"]);
		$filter["session_key"]=array("operation"=>"=","value"=>$admin["session_key"]);
	 	if(!isInBD($table,$filter)){
		 	$response["result"]=false;
			debug_log("ERROR Admin not exists (id_admin=".$admin["id_admin"].")");
	 		$response["error"]="ERROR Admin not in the system";
	 		$response["error_code"]="admin_not_valid";
	 		return false;
	 	}

	 	$table="admins";
	 	$filter=array();
	 	$filter["email"]=array("operation"=>"=","value"=>$admin["email"]);
	 	$filter["active"]=array("operation"=>"=","value"=>1);
	 	if(!isInBD($table,$filter)){
		 	$response["result"]=false;
			debug_log("ERROR Admin inactive (id_admin=".$admin["id_admin"].")");
	 		$response["error"]="ERROR User not in the system";
	  		$response["error_code"]="admin_inactive";
			return false;
	 	}

		debug_log("Check Admin END");

	 	return true;
	}

	function error_handler($error_code){
		global $error_alert;
		global $error_s;

		if((isset($error_code))&&(!empty($error_code))&&($error_code!="undefined")){
			$error_alert=$error_s[$error_code];
		}
	}

	function checkUserSessionKey($session_key){
		global $page_path;
		global $response;

		$table="users";
		$filter=array();
		$filter["session_key"]=array("operation"=>"=","value"=>$session_key);
		$filter["active"]=array("operation"=>"=","value"=>1);
		if(!isInBD($table,$filter)){
			$table="users";
			$filter=array();
			$filter["session_key"]=array("operation"=>"=","value"=>$session_key);
			if(isInBD($table,$filter)){
				$response["result"]=false;
				debug_log("[".$page_path."] ERROR User not active {session_key:'".$session_key."'}");
				$response["error_code"]="user_not_active";
				return false;
			}
			$response["result"]=false;
			debug_log("[".$page_path."] ERROR Session key not valid {session_key:'".$session_key."'}");
			$response["error_code"]="session_key_not_valid";
			return false;
		}
		return true;
	}

	function checkAdminSessionKey($session_key){
		global $page_path;
		global $response;

		$table="admins";
		$filter=array();
		$filter["session_key"]=array("operation"=>"=","value"=>$session_key);
		$filter["active"]=array("operation"=>"=","value"=>1);
		if(!isInBD($table,$filter)){
			$table="admins";
			$filter=array();
			$filter["session_key"]=array("operation"=>"=","value"=>$session_key);
			if(isInBD($table,$filter)){
				$response["result"]=false;
				debug_log("[".$page_path."] ERROR Admin not active {session_key:'".$session_key."'}");
				$response["error_code"]="admin_not_active";
				return false;
			}
			$response["result"]=false;
			debug_log("[".$page_path."] ERROR Session key not valid {session_key:'".$session_key."'}");
			$response["error_code"]="session_key_not_valid";
			return false;
		}
		return true;
	}

	function checkBrandAdminSessionKey($session_key){
		global $page_path;
		global $response;

		$table="admins";
		$filter=array();
		$filter["session_key"]=array("operation"=>"=","value"=>$session_key);
		$filter["active"]=array("operation"=>"=","value"=>1);
		$filter["brand_admin"]=array("operation"=>"=","value"=>1);
		if(!isInBD($table,$filter)){
			$table="admins";
			$filter=array();
			$filter["session_key"]=array("operation"=>"=","value"=>$session_key);
			$filter["active"]=array("operation"=>"=","value"=>1);
			if(!isInBD($table,$filter)){
				$table="admins";
				$filter=array();
				$filter["session_key"]=array("operation"=>"=","value"=>$session_key);
				if(!isInBD($table,$filter)){
					$response["result"]=false;
					debug_log("[".$page_path."] ERROR Session key not valid {session_key:'".$session_key."'}");
					$response["error_code"]="session_key_not_valid";
					return false;
				}
				$response["result"]=false;
				debug_log("[".$page_path."] ERROR Admin not active {session_key:'".$session_key."'}");
				$response["error_code"]="admin_not_active";
				return false;
			}
			$response["result"]=false;
			debug_log("[".$page_path."] ERROR Brand permission denied {session_key:'".$session_key."'}");
			$response["error_code"]="brand_permission_denied";
			return false;
		}
		return true;
	}

	function checkUserConversationKey($coversation_key){
		global $page_path;
		global $response;
		global $user;

		$table="conversations";
		$filter=array();
		$filter["conversation_key"]=array("operation"=>"=","value"=>$coversation_key);
		$filter["active"]=array("operation"=>"=","value"=>1);
		$filter["id_user"]=array("operation"=>"=","value"=>$user["id_user"]);

		if(!isInBD($table,$filter)){
			$table="conversations";
			$filter=array();
			$filter["conversation_key"]=array("operation"=>"=","value"=>$coversation_key);
			$filter["id_user"]=array("operation"=>"=","value"=>$user["id_user"]);
			if(isInBD($table,$filter)){
				$table="conversations";
				$filter=array();
				$filter["conversation_key"]=array("operation"=>"=","value"=>$coversation_key);
				if(isInBD($table,$filter)){
					$response["result"]=false;
					debug_log("[".$page_path."] ERROR Conversation Permission Denied {conversation_key:'".$coversation_key."'}");
					$response["error_code"]="conversation_permission_denied";
					return false;
				}
				$response["result"]=false;
				debug_log("[".$page_path."] ERROR Conversation key not valid {conversation_key:'".$coversation_key."'}");
				$response["error_code"]="conversation_key_not_valid";
				return false;
			}
			$response["result"]=false;
			debug_log("[".$page_path."] ERROR Conversation not active {conversation_key:'".$coversation_key."'}");
			$response["error_code"]="conversation_not_active";
			return false;
		}
		return true;

	}

	function checkAdminConversationKey($coversation_key){
		global $page_path;
		global $response;
		global $admin;

		$table="conversations";
		$filter=array();
		$filter["conversation_key"]=array("operation"=>"=","value"=>$coversation_key);
		$filter["id_brand"]=array("operation"=>"=","value"=>$admin["id_brand"]);

		if(!isInBD($table,$filter)){
			$table="conversations";
			$filter=array();
			$filter["conversation_key"]=array("operation"=>"=","value"=>$coversation_key);
			if(!isInBD($table,$filter)){
				$response["result"]=false;
				debug_log("[".$page_path."] ERROR Conversation key not valid {conversation_key:'".$coversation_key."'}");
				$response["error_code"]="conversation_key_not_valid";
				return false;
			}
			$response["result"]=false;
			debug_log("[".$page_path."] ERROR Conversation permission denied {conversation_key:'".$coversation_key."'}");
			$response["error_code"]="conversation_permission_denied";
			return false;
		}
		return true;
	}

	function checkAdminActiveConversationKey($coversation_key){
		global $page_path;
		global $response;
		global $admin;

		$table="conversations";
		$filter=array();
		$filter["conversation_key"]=array("operation"=>"=","value"=>$coversation_key);
		$filter["id_brand"]=array("operation"=>"=","value"=>$admin["id_brand"]);
		$filter["active"]=array("operation"=>"=","value"=>1);
		if(!isInBD($table,$filter)){
			$table="conversations";
			$filter=array();
			$filter["conversation_key"]=array("operation"=>"=","value"=>$coversation_key);
			$filter["active"]=array("operation"=>"=","value"=>1);
			if(!isInBD($table,$filter)){
				$table="conversations";
				$filter=array();
				$filter["conversation_key"]=array("operation"=>"=","value"=>$coversation_key);
				if(!isInBD($table,$filter)){
					$response["result"]=false;
					debug_log("[".$page_path."] ERROR Conversation key not valid {conversation_key:'".$coversation_key."'}");
					$response["error_code"]="conversation_key_not_valid";
					return false;
				}
				$response["result"]=false;
				debug_log("[".$page_path."] ERROR Conversation not active {conversation_key:'".$coversation_key."'}");
				$response["error_code"]="conversation_not_active";
				return false;
			}
			$response["result"]=false;
			debug_log("[".$page_path."] ERROR Conversation permission denied {conversation_key:'".$coversation_key."'}");
			$response["error_code"]="conversation_permission_denied";
			return false;
		}
		return true;
	}
?>
