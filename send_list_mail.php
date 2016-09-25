<?php 
require_once("db_config.php");
require_once("lib_admin.php");

// connect to the db
$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
mysql_select_db($dbname);

send_Mail($conn);
mysql_close($conn);
exit;

// send_Mail() Function - Sends mail in Queue
function send_Mail($conn) {
  
  $to = "";
  $from = "";
  $headers = "";
  $subject = "";
  $msg = "";
  $msg_id = "";
  $list_id = "";
  $tname = "LIST";
  $code = "";
  $un_conf_url = "http://DOMAIN/mailing_list.html";
  	    
  $qMQ = "SELECT EMAIL, MSG_ID, LFROM FROM MESSAGE_QUEUE;";
  $rsMQ = mysql_query($qMQ);
		
  if ( (!$rsMQ) || (!mysql_num_rows($rsMQ)) ) { 
	mysql_close($conn);
    exit;		  
  }
  
  while ($rowMQ = mysql_fetch_array($rsMQ)) {
  
    $to = $rowMQ['EMAIL']; 
	$msg_id = intval($rowMQ['MSG_ID']);
	$from = $rowMQ['LFROM'];
	
	$q = "SELECT LIST_ID, MESSAGE, SUBJECT FROM MESSAGES WHERE ID='$msg_id' LIMIT 1;";
	$rs = mysql_query($q);
	
	if ( (!$rs) || (!mysql_num_rows($rs)) ) {
	  continue;	  
    }
	
	$row = mysql_fetch_array($rs);
	
	$subject = $row['SUBJECT'];
	$msg = $row['MESSAGE'];
	$list_id = $row['LIST_ID'];
	
	$tname .= $list_id;
	$qL = "SELECT CODE FROM $tname WHERE EMAIL='$to' LIMIT 1;";
	//$rsL = mysql_query($qL);
	
	//if ( (!$rsL) || (!mysql_num_rows($rsL)) ) {
	//  continue;	  
    //}
	
	//$rowL = mysql_fetch_array($rsL);
	
	//$code = $rowL['CODE'];
	
	$headers = "From:$from\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=ISO-8859-1\r\n";
	
	$msg = '<span style="font-family:Arial; font-size:10pt;">' . $msg;
	$msg .= "\r\n<br /><br />";
    $msg .= "To unsubscribe at any time, click <a href=\"$un_conf_url\">this link</a> or\n";
    $msg .= "cut and paste the following URL into your browser:\n\n<br />";
    $msg .= "$un_conf_url\n\n<br />Thanks!</span>";
  
	if (mail($to, $subject, $msg, $headers)) { 
	  $qD = "DELETE FROM MESSAGE_QUEUE WHERE EMAIL='$to' AND MSG_ID='$msg_id';";
	  $rsD = mysql_query($qD);
    }	 
        
  } // end while cycle
  
} // end function

?>
