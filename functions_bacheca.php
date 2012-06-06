<?php
if(!defined('INCLUDE_CHECK')) die('You are not allowed to execute this file directly');

require_once 'user_access/connect.php';

function getContent($num){
	$res = @mysql_query("SELECT DATE, USER, MESSAGE FROM SHOUTBOX ORDER BY DATE DESC LIMIT ".$num);
	if(!$res)
		die("Error: ".mysql_error());
	else
		return $res;
}
function insertMessage($user, $message){
	$query = sprintf("INSERT INTO SHOUTBOX (USER, MESSAGE) VALUES('%s', '%s');", mysql_real_escape_string(strip_tags($user)), mysql_real_escape_string(strip_tags($message)));
	$res = @mysql_query($query);
	if(!$res)
		die("Error: ".mysql_error());
	else
		return $res;
}

?>