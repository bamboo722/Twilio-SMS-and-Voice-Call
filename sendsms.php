<?php 
@session_start();
ob_start();

if(!isset($_SESSION['user_login']) and !isset($_COOKIE['cookie_login']))//session store admin name
{
    header("Location: index.php");//login in AdminLogin.php
}
require_once("includes/dbconnect.php");
require_once("includes/Services/Twilio.php");	

$response = array();
$response['status'] = 'Error';
$phone =$_SESSION['tw_number'];
if (is_ajax())
{
	$accountSid = $_SESSION['tw_account_sid'];
	$authToken  = $_SESSION['tw_auth_token'];
	$from = $_SESSION['tw_number'];
	
	$client = new Services_Twilio($accountSid, $authToken,'2010-04-01');
	
	$arizona_off = -7;
	
	if (isset($_POST["sms_to"]) && !empty($_POST["sms_to"])) 
 	{
 		// send sms
 		$sms_sal = trim($_POST["sms_sal"]);	 //salutations
    	$sms_to = explode(';',trim($_POST["sms_to"])); //to people
    	$sms_to_cnt = count($sms_to);
    	foreach($sms_to as $number => $name)
    	{
    		$sms_body = $_POST["sms_body"];
    		if ($sms_to_cnt>1)
				$sms_body = $sms_sal.' '.$to_name.','."\r\n".$sms_body;					    		
			$sms = $client->account->messages->sendMessage(
				$from,
				$name,
				$sms_body
			);
			$response['status'] = 'Success';								
		}
    	
		
		/* get latest sms time from sms_log_info */	
		$sql_sms = "select max(sms_utime) as max_sms_time from sms_log_info";
		$sql_res = mysql_query($sql_sms) or die(mysql_error());	
		$sql_result = mysql_fetch_assoc($sql_res);
		if (isset($sql_result) and $sql_result['max_sms_time'] != 0)
		{	
		}else
			$sql_result['max_sms_time']=0;
		
		$sms_flag = 1;		
		
		// get sms log
		
		foreach ($client->account->sms_messages as $sms) {		
		    $row = array(
		        $sms->sid, $sms->from, $sms->to, $sms->date_sent,
		        $sms->status, $sms->direction, $sms->price, $sms->body
		    );
		    $sms_utime = strtotime($sms->date_sent);
		    if ($sms_utime > $sql_result['max_sms_time']) // date_sent
		    {
				mysql_query("set time_zone='-7:00';");
				$sms_sent = date("Y-m-d H:i:s",$sms_utime);    
				$sql_sms = sprintf('insert into sms_log_info(log_time,sms_time,sms_utime,content, from_phone,  to_phone, sms_flag,sms_id,direction,status) values (sysdate(),"%s","%d","%s","%s","%s","%d","%s","%s","%s")',$sms_sent,$sms_utime, $sms->body,$sms->from,$sms->to,$sms_flag, $sms->sid, $sms->direction,$sms->status);				
				mysql_query($sql_sms) or die(mysql_error());		
				$response['sms_news']++;	    	
			}else
				break;
    	}
	}
}

echo json_encode($response);

//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
?>