<?php
if(!defined('INCLUDE_CHECK')) die('You are not allowed to execute this file directly');

require_once 'user_access/connect.php';

require_once 'script/log.php';
$logDir="log";
$logFileName="debug";
$headerTitle="MyCalcetto";
$logMode="oneFile"; //oneFile: each log instance goes to the same file ([logFileName].log) | oneFilePerLog: each log instance goes to a new file ([logFileName][logNumber].log
$counterFile="debug.counter";
$logger=new Log($logDir,$logFileName,$headerTitle, $logMode, $counterFile);


function getCampoById($id){

	$res = mysql_query("SELECT ID, NOME, INDIRIZZO from CAMPO where ID='".$id."'") or die(mysql_error());
	return $res;
}

function getCampiList($letters){

	$res = mysql_query("SELECT ID, NOME, INDIRIZZO from CAMPO where NOME like '%".$letters."%' OR INDIRIZZO like '%".$letters."%'") or die(mysql_error());
	return $res;
}

function visualizzaCampi(){
	$query = "SELECT ID, NOME, INDIRIZZO, COPERTO, TELEFONO, LINK from CAMPO ORDER BY NOME ASC;";
	$rs = mysql_query($query);

	if ($row = mysql_fetch_array($rs)){
		echo "<br />";
		echo "<table class='table2'>";
		echo "<thead><tr>";
		echo "<th scope='col'>Campo</th>";
		echo "<th scope='col'>Indirizzo</th>";
		echo "<th scope='col'>Coperto</th>";
		echo "<th scope='col'>Telefono</th>";
		echo "<th scope='col'>Sito</th>";
		if ($_SESSION['level']==9){
			echo "<th scope='col'>Elimina <img alt='Help Elimina' src='img/Help_16x16.png' title=\"Puoi cancellare un campo.\"></th>";
		}
		echo "</tr></thead>";
			
		do
		{
			$campoId=$row["ID"];
			$nome=$row["NOME"];
			$coperto=$row["COPERTO"];
			$indirizzo=$row["INDIRIZZO"];
			$telefono=$row["TELEFONO"];
			$link=$row["LINK"];

			echo "<tbody>";
			echo "<tr>";
			echo "<td>$nome</td>";
			echo "<td>$indirizzo</td>";
			if($coperto == 0) { echo "<td>NO</td>"; } else { echo "<td>SI</td>"; }
			echo "<td>$telefono</td>";
			echo "<td><a href='$link' target='_blank'>link</a></td>";
			if ($_SESSION['level']==9) {
				echo "<td>";
				echo "<form id='cancella_form_$campoId' method='post' action='campi.php'>
			 		<input type='hidden' name='id_campo' value='$campoId' />
			 		<input type='hidden' name='action' value='elimina' />
			 		<img class='cancella_img' src='img/Cancel_24x24.png' alt='Cancella' title='Elimina il campo' onclick='eliminaCampo($campoId);'>
			 		</form>";
				echo "</td>";
			}

			echo "</tr>";

		} while ($row = mysql_fetch_array($rs));
		echo "</tbody>";
		echo "</table>";

	} else {
		echo "<div class='info'>Non ci sono campi!</div>";
	}
}

function eliminaCampo($campoId){

	global $logger;
	$query="DELETE FROM CAMPO WHERE ID = $campoId;";
	if(mysql_query($query)){
		$logger->logThis($logger->get_formatted_date().'[INFO] Eliminato campo: '.$campoId);
		echo "<div class='info'>Il campo &egrave; stato eliminato correttamente.<br /></div>";
		echo "</br><p>Torna alla <a href='visualizzaCampi.php'>pagina precedente</a>.</p>";
	}else{
		$logger->logThis($logger->get_formatted_date().'[ERROR] Campo NON eliminato: '.$campoId);
		echo "<div class='error'>Si &egrave; verificato un errore!</div>";
		echo "</br><p>Torna alla <a href='visualizzaCampi.php'>pagina precedente</a>.</p>";
	}
}

function nuovoCampo($nome, $indirizzo, $prezzo, $coperto, $telefono, $link){

	global $logger;
	$query1 = '';
	$query1 = "INSERT INTO CAMPO (NOME, INDIRIZZO, PREZZO, COPERTO,  TELEFONO, LINK) VALUES ('$nome', '$indirizzo', '$prezzo', $coperto, '$telefono', '$link');";
	echo "<script type='text/javascript'>alert('$query1');</script>";
	if (mysql_query($query1)) {
		$logger->logThis($logger->get_formatted_date().'[INFO] Creato campo: '.$nome);
		echo "<div class='success'>Il campo &egrave; stato creato!";
		echo "</br><p>Torna alla <a href='visualizzaCampi.php'>pagina precedente</a>.</p>";
	} else {
		$logger->logThis($logger->get_formatted_date().'[ERROR] Campo NON creato: '.$nome);
		echo "<div class='error'>Si &egrave, verificato un errore. Il campo non &egrave; stato creato.</div>";
		echo "</br><p>Torna alla <a href='visualizzaCampi.php'>pagina precedente</a>.</p>";

	}
}

?>
