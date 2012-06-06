<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>

<style>
@import url(stile.css);
</style>


<title>CALCETTO - Squadre</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type='text/javascript' src='flash.js'></script>


</head>

<body>
<?php

function printTeam($squadraA, $squadraB)
{	echo("<b>Squadra A - Maglia Chiara:</b><br><br><ol>");
for ($i = 0; $i <= 4; $i++){
	list($a, $n, $c, $b) = split(";", $squadraA[$i]);
	if("" == $n){
		echo("<li>Ospite<br>");
	}else{
		echo("<li> ".$n." ".$c." <br>");
	}
}
echo("</ol><br>");
echo("<b>Squadra B - Maglia Scura:</b><br><br><ol>");
for ($i = 0; $i <= 4; $i++){
	list($a, $n, $c, $b) = split(";", $squadraB[$i]);
	if("" == $n){
		echo("<li>Ospite<br>");
	}else{
		echo("<li> ".$n." ".$c." <br>");
	}
}
echo "</ol>";
}

session_start();
if ($_SESSION["username"] == "") {
	header("location: index.php");
}
else{


	include ("config.inc.php");

	//Recupero i dati...
	$user = $_SESSION["username"];
	$id_partita = $_GET["id_partita"];

	//Query che estrae gli id dei partecipanti
	$query = "SELECT USER_ID FROM PARTECIPAZIONE WHERE ID_PARTITA = $id_partita AND PARTECIPA = 1";
	$rs = mysql_query($query);
	//mettere a posto il numrow
	//$numRow = mysql_num_rows($rs);
	for ($i = 0; $i <= 9; $i++) {
		if ($row = mysql_fetch_array($rs)){
			$idUser[$i] = $row["USER_ID"];
		}
	}

	

	for ($i = 0; $i <= 9; $i++) {
		//Query che calcola la media voto per tutti i giocatori -- aggiungi tu nella where la condizione USER_ID = quello che hai tirato fuori prima, in modo che lo faccia per un utente solo
		$query = "SELECT U.NOME AS NOME, U.COGNOME AS COGNOME, SUM(SOMMAVOTI)/SUM(NUMVOTANTI) AS MEDIA_VOTI, COUNT(P.PARTECIPA) AS NUM_PARTITE FROM UTENTE U, PARTECIPAZIONE P WHERE U.USER_ID = P.USER_ID AND U.USER_ID = '$idUser[$i]' AND P.PARTECIPA = 1 AND P.SOMMAVOTI > 0 GROUP BY U.NOME, U.COGNOME ORDER BY MEDIA_VOTI DESC";
		$rs = mysql_query($query);
		if ($row = mysql_fetch_array($rs)){
			$voto_ = $row["MEDIA_VOTI"];
			$cognome_ = $row["COGNOME"];
			$nome_ = $row["NOME"];
			$numPartite_ = $row["NUM_PARTITE"];
			$totale[$i] = $voto_.";".$nome_.";".$cognome_.";".$numPartite_;
		}


	}

	//ordina array dal più piccolo al più grande
	sort($totale);


	/*********Metodo di creazione squadra, NUMERO 1*************/
	$squadraA1 = array( $totale[0],$totale[2],$totale[4],$totale[6],$totale[8]);
	$squadraB1 = array( $totale[1],$totale[3],$totale[5],$totale[7],$totale[9]);
	//calcolo per squadra A1
	for ($i = 0; $i <= 4; $i++){
		list($voto[$i], $nome[$i], $cognome[$i], $numPartite[$i]) = split(";", $squadraA1[$i]);
	}
	$totPartite = 0;
	for ($i = 0; $i <= 4; $i++){
		$totPartite = $totPartite + $numPartite[$i] ;
	}
	$valoreSquadraA1 = 0;
	for ($i = 0; $i <= 4; $i++){
		$valoreSquadraA1 = $valoreSquadraA1 + ($voto[$i]*($numPartite[$i]/$totPartite));
	}

	//calcolo per squadra B1
	for ($i = 0; $i <= 4; $i++){
		list($voto[$i], $nome[$i], $cognome[$i], $numPartite[$i]) = split(";", $squadraB1[$i]);
	}
	$totPartite = 0;
	for ($i = 0; $i <= 4; $i++){
		$totPartite = $totPartite + $numPartite[$i] ;
	}
	$valoreSquadraB1 = 0;
	for ($i = 0; $i <= 4; $i++){
		$valoreSquadraB1 = $valoreSquadraB1 + ($voto[$i]*($numPartite[$i]/$totPartite));
	}

	$metodo1 = $valoreSquadraA1 - $valoreSquadraB1;



	/*********Metodo di creazione squadra, NUMERO 2*************/
	$squadraA2 = array( $totale[0],$totale[2],$totale[4],$totale[7],$totale[9]);
	$squadraB2 = array( $totale[1],$totale[3],$totale[5],$totale[6],$totale[8]);
	//calcolo per squadra A2
	for ($i = 0; $i <= 4; $i++){
		list($voto[$i], $nome[$i], $cognome[$i], $numPartite[$i]) = split(";", $squadraA2[$i]);
	}
	$totPartite = 0;
	for ($i = 0; $i <= 4; $i++){
		$totPartite = $totPartite + $numPartite[$i] ;
	}
	$valoreSquadraA2 = 0;
	for ($i = 0; $i <= 4; $i++){
		$valoreSquadraA2 = $valoreSquadraA2 + ($voto[$i]*($numPartite[$i]/$totPartite));
	}

	//calcolo per squadra B2
	for ($i = 0; $i <= 4; $i++){
		list($voto[$i], $nome[$i], $cognome[$i], $numPartite[$i]) = split(";", $squadraB2[$i], 4);
	}
	$totPartite = 0;
	for ($i = 0; $i <= 4; $i++){
		$totPartite = $totPartite + $numPartite[$i] ;
	}
	$valoreSquadraB2 = 0;
	for ($i = 0; $i <= 4; $i++){
		$valoreSquadraB2 = $valoreSquadraB2 + ($voto[$i]*($numPartite[$i]/$totPartite));
	}

	$metodo2 = $valoreSquadraA2 - $valoreSquadraB2;


	/*********Metodo di creazione squadra, NUMERO 3*************/
	$squadraA3 = array( $totale[0],$totale[2],$totale[5],$totale[7],$totale[9]);
	$squadraB3 = array( $totale[1],$totale[3],$totale[4],$totale[6],$totale[8]);
	//calcolo per squadra A3
	for ($i = 0; $i <= 4; $i++){
		list($voto[$i], $nome[$i], $cognome[$i], $numPartite[$i]) = split(";", $squadraA3[$i]);
	}
	$totPartite = 0;
	for ($i = 0; $i <= 4; $i++){
		$totPartite = $totPartite + $numPartite[$i] ;
	}
	$valoreSquadraA2 = 0;
	for ($i = 0; $i <= 4; $i++){
		$valoreSquadraA3 = $valoreSquadraA3 + ($voto[$i]*($numPartite[$i]/$totPartite));
	}

	//calcolo per squadra B3
	for ($i = 0; $i <= 4; $i++){
		list($voto[$i], $nome[$i], $cognome[$i], $numPartite[$i]) = split(";", $squadraB3[$i]);
	}
	$totPartite = 0;
	for ($i = 0; $i <= 4; $i++){
		$totPartite = $totPartite + $numPartite[$i] ;
	}
	$valoreSquadraB3 = 0;
	for ($i = 0; $i <= 4; $i++){
		$valoreSquadraB3 = $valoreSquadraB3 + ($voto[$i]*($numPartite[$i]/$totPartite));
	}

	$metodo3 = $valoreSquadraA3 - $valoreSquadraB3;

echo "<br>Squadre consigliate:<br><br>";

	//stampa delle squadre...
	if ($metodo1 < $metodo2){
		if($metodo1 < $metodo3){
			printTeam($squadraA1, $squadraB1);
		}else{
			printTeam($squadraA3, $squadraB3);
		}
	}
		else{
			if($metodo2 < $metodo3){
				printTeam($squadraA2, $squadraB2);
			}else{
				printTeam($squadraA3, $squadraB3);
			}
		}

}
?>


</body>
</html>
