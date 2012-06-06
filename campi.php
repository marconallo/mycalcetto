<?php

define('INCLUDE_CHECK',true);
require 'functions_campi.php';

session_name('tzLogin');
session_set_cookie_params(2*7*24*60*60);
session_start();

if(isset($_SESSION['id'])){
	//Recupero i dati...
	$user = $_SESSION['id'];

	if(isset($_GET['action']) && $_GET['action']=='list'){ ?>

	<?php
	$letters = strtolower($_GET["q"]);
	if (!$letters) return;

	$letters = preg_replace("/[^a-z0-9 ]/si","",$letters);
	$res = getCampiList($letters);
	while($inf = mysql_fetch_array($res)){
		echo $inf["ID"]."|".$inf["NOME"]."|".$inf["INDIRIZZO"]."\n";
	}
	?>
	
	<?php } else if(isset($_POST['action']) && $_POST['action']=='elimina'){ $id_campo = $_POST["id_campo"]; ?>

		<div class="container" style="width: 500px;">
		<h3>Cancellazione</h3>
		<?php eliminaCampo($id_campo); ?></div>
	
	<?php } else if(isset($_POST['action']) && $_POST['action']=='creaNuovo'){ ?>
	
	<?php
		$nome = mysql_real_escape_string(strip_tags($_POST['nome']));
		$indirizzo = mysql_real_escape_string(strip_tags($_POST['indirizzo']));
		$coperto = ($_POST['coperto'])==1 ? 1 : 0;
		$telefono = mysql_real_escape_string(strip_tags($_POST['telefono']));
		$link = mysql_real_escape_string(strip_tags($_POST['sito']));
		$prezzo = mysql_real_escape_string(strip_tags($_POST['prezzo']));
	?>
	<div class="container" style="width: 500px;">
		<h3>Nuovo Campo</h3>
	    <?php nuovoCampo($nome, $indirizzo, $coperto, $telefono, $link); ?>    
	</div>
		
	<?php } } else { /* Out of session*/
		header("Location: index.php");
	}
	?>
