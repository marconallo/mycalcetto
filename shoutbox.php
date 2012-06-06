<?php
define('INCLUDE_CHECK',true);
require_once 'functions_bacheca.php';

if(isset($_POST['action'])){
	switch($_POST['action']){
		case "update":
                        $result = "";
			$res = getContent(20);
			while($row = mysql_fetch_array($res)){
				$result .= "<tr><td class='nick'>".$row['USER']."<br><span class=\"date\">".$row['DATE']."</span></td><td class='img'><img src='img/Play_16x16.png' alt='-' style='vertical-align: bottom;' /></td><td class='message'>".$row['MESSAGE']."</td></tr>";
			}
			echo $result;
			break;
		case "insert":
			echo insertMessage($_POST['nick'], $_POST['message']);
			break;
	}
} else {
	header("Location: index.php");
}

?>