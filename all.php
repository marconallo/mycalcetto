<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>

<style>
@import url(stile.css);
</style>


<title>CALCETTO - Storico</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript">
function newWindow(a_str_windowURL, a_str_windowName, a_int_windowWidth, a_int_windowHeight, a_bool_scrollbars, a_bool_resizable, a_bool_menubar, a_bool_toolbar, a_bool_addressbar, a_bool_statusbar, a_bool_fullscreen) {
  var int_windowLeft = (screen.width - a_int_windowWidth) / 2;
  var int_windowTop = (screen.height - a_int_windowHeight) / 2;
  var str_windowProperties = 'height=' + a_int_windowHeight + ',width=' + a_int_windowWidth + ',top=' + int_windowTop + ',left=' + int_windowLeft + ',scrollbars=' + a_bool_scrollbars + ',resizable=' + a_bool_resizable + ',menubar=' + a_bool_menubar + ',toolbar=' + a_bool_toolbar + ',location=' + a_bool_addressbar + ',statusbar=' + a_bool_statusbar + ',fullscreen=' + a_bool_fullscreen + '';
  var obj_window = window.open(a_str_windowURL, a_str_windowName, str_windowProperties)
    if (parseInt(navigator.appVersion) >= 4) {
      obj_window.window.focus();
    }
}
</script>
<script type='text/javascript' src='flash.js'></script>


</head>

<body>
<?php
session_start();
if ($_SESSION["username"] == "") {
header("location: index.php");
}
else{


include ("config.inc.php");

//Recupero i dati...
$user = $_SESSION["username"];



echo"<center><script type='text/javascript'>animazione_flash();</script></center>";

//echo "<table border=0 width='75%' align='center'><tr><td><a href='main_page.php'>Visualizza Partite</a></td><td><a href='organizza.php'>Organizza nuova partita</td><td><a href='regole.php'>Regolamento</a></td><td><a href='logout.php'>Logout</a></td></tr></table><br><br>";



echo "<br><br><h3>Tutte le partite giocate:</h3>";

$query = "SELECT p.ID AS ID_PARTITA, p.ORA_INIZIO AS ORA_INIZIO, c.NOME AS CAMPO, c.COPERTO AS COPERTO FROM PARTITA p, CAMPO c WHERE p.ID_CAMPO = c.ID and p.ORA_INIZIO < now() ORDER BY p.ORA_INIZIO DESC";
$rs = mysql_query($query);


if ($row = mysql_fetch_array($rs)){
  echo "<center><table border = 1><tr>";
  echo "<td><b>Data</b></td>";
  echo "<td><b>Ora</b></td>";
  echo "<td><b>Campo</b></td>";
  echo "<td><b>Coperto</b></td>";
  echo "<td><b>Pagelle</b></td></tr>";


 do
	{
	 $partita=$row["ID_PARTITA"];
	 $data=substr($row["ORA_INIZIO"],4,2)."-".substr($row["ORA_INIZIO"],2,2)."-20".substr($row["ORA_INIZIO"],0,2);
	 $ora=substr($row["ORA_INIZIO"],6,2).":".substr($row["ORA_INIZIO"],8,2);
	 $campo=$row["CAMPO"];
	 $coperto=$row["COPERTO"];
	 	 	 	 

	 echo "<td width='100'>$data</td>";
	 echo "<td>$ora</td>";
	 echo "<td>$campo</td>";
	 if($coperto == 0) { echo "<td><center>NO</center></td>"; }
	 else { echo "<td><center>SI</center></td>"; }
	 echo "<td><center><a href='voti.php?id_partita=$partita' onclick=\"newWindow(this.href, 'popup', 400, 500, 1, 1, 0, 0, 0, 1, 0); return false\"; target=\"_blank\">Voti</a></center></td></tr>";


	} while ($row = mysql_fetch_array($rs));
	 	
    
	echo "</table></center><br>";
	

	
	
	} else {

	echo "Non &egrave; stata giocata nessuna partita!!<br>";	
 

}


}
?>


</body>
</html>
