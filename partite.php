<?php

define('INCLUDE_CHECK',true);
require 'functions_partite.php';

session_name('tzLogin');
session_set_cookie_params(2*7*24*60*60);
session_start();

if($_SESSION['id']){
	//Recupero i dati...
	$user = $_SESSION['id'];
        $level = $_SESSION['level'];

	if(isset($_POST['action'])) {
		
		switch($_POST['action']){

			case 'accetta':
				$id_partita = $_POST["id_partita"];
				echo '<div class="container" style="width: 500px;">';
				echo '<h3>Iscrizione</h3>';
				accettaPartita($user, $id_partita);
				echo '</div>';
				exit;

			case 'rifiuta':
				$id_partita = $_POST["id_partita"];
				echo '<div class="container" style="width: 500px;">';
				echo '<h3>Rifiuto</h3>';
				rifiutaPartita($user, $id_partita);
				echo '</div>';
				exit;
					
			case 'cancella':
				$id_partita = $_POST["id_partita"];
				echo '<div class="container" style="width: 500px;">';
				echo '<h3>Cancellazione</h3>';
				cancellaPartita($user, $id_partita);
				echo '</div>';
				exit;

			case 'invita':
				$id_partita = $_POST["id_partita"];
				echo '<div class="container" style="width: 500px;">';
				echo '<h3>Inviti</h3>';
				invitaTutti($user, $id_partita, false);
				echo '</div>';
				exit;
				
			case 'chiudi':
				$id_partita = $_POST["id_partita"];
				echo '<div class="container" style="width: 500px;">';
				echo '<h3>Chiudi partita</h3>';
				chiudiPartita($id_partita);
				echo "<div class='success'>La tua partita &egrave; stata confermata!</div>";
				echo "</br><p>Torna alla <a href='visualizzaPartite.php'>pagina precedente</a>.</p>";
				echo '</div>';
				exit;

			case 'creaNuova':
				$ora_inizio = mysql_real_escape_string(strip_tags($_POST['data']))." ".mysql_real_escape_string(strip_tags($_POST['ora'])) ;
				$campo = mysql_real_escape_string(strip_tags($_POST['idCampo']));
				$note = mysql_real_escape_string(strip_tags($_POST['note']));
				$invitaTutti = mysql_real_escape_string(strip_tags($_POST['invitaTutti']));
				echo '<div class="container" style="width: 500px;">';
				echo '<h3>Nuova Partita</h3>';
				nuovaPartita($user, $ora_inizio, $campo, $note, $invitaTutti);
				echo '</div>';
				exit;
					
			case 'vota':
				echo '<h3>Partita del '.$_POST['dataPartita'].' - Vota partecipanti</h3>';
				visualizzaPartecipantiPagelle($_POST['id_partita'], $user);
				echo '<div class="clear"></div>';
				exit;

			case 'visualizzaVoti':
				echo '<h3>Partita del '.$_POST['dataPartita'].' - Voti registrati</h3>';
				visualizzaPagelle($_POST['id_partita']);
				echo '<div class="clear"></div>';
				exit;

			case 'inviaVoti':
				echo '<div class="container" style="width: 500px;">';
				echo '<h3>Tuoi voti</h3>';
				$idPartita = $_POST['id_partita'];
				$result = getPartecipanti($idPartita);
				while ($row = mysql_fetch_array($result)){
					$partecipanteId=$row["ID"];
					$voto=$_POST["voto_$partecipanteId"];
					if(!empty($voto)){
						aggiungiVoto($voto, $idPartita, $partecipanteId);
					}
				}
				if (aggiornaVotazione($user, $idPartita)){
					echo "<div class='success'>I tuoi voti sono stati registrati!</div>";
					echo "</br><p>Torna alla <a href='visualizzaPagelle.php'>pagina precedente</a>.</p>";
				} else {
					echo "<div class='error'>I tuoi voti NON sono stati registrati! Riprova.</div>";
					echo "</br><p>Torna alla <a href='visualizzaPagelle.php'>pagina precedente</a>.</p>";
				}
				echo'</div>';
				exit;
			
			default:
				exit;
				
		}

	} elseif(isset($_GET['action']) && $_GET['action']=='partecipanti'){
		$id_partita = $_GET["id_partita"];
		echo '<div class="container" style="width: 500px;">';
		echo '<h3>Partecipanti</h3>';
                $isAdmin = 0;
                if($level==9){
                    $isAdmin=1;                    
                }
                visualizzaPartecipanti($id_partita, $isAdmin);
                
		echo '</div>';
		if (contaPartecipanti($id_partita)<10){
			echo '<div class="container" style="width: 500px;">';
			echo '<h3>Chi ha rifiutato</h3>';
			visualizzaRifiutanti($id_partita);
			echo '</div>';
			echo '<div class="container" style="width: 500px;">';
			echo '<h3>Indecisi</h3>';
			visualizzaIndecisi($id_partita);
			echo '</div>';
		} else {
//			echo '<div class="container" style="width: 500px;">';
//			echo '<h3>Squadre Proposte</h3>';
//			echo '<p>To be implemented</p>';
//			//creaSquadre($id_partita);
//			echo '</div>';
		}
	} elseif(isset($_GET['action']) && $_GET['action']=='partecipantiXML'){
                visualizzaPartecipantiXML();		
        } elseif(isset($_GET['action']) && $_GET['action']=='statistiche'){
                $id_user = $_GET["id_user"];
		echo '<div class="container" style="width: 500px;">';
		echo '<h3>Statistiche</h3>';
		visualizzaStatistiche($id_user);
		echo '</div>';
        } elseif(isset($_GET['action']) && $_GET['action']=='confronto'){
                $userId1 = $_GET["id_user_1"];
                $userId2 = $_GET["id_user_2"];
                echo '<div class="container" style="width: auto;">';
		echo '<h3>Statistiche</h3>';
		visualizzaConfronto($userId1, $userId2);
		echo '</div>';
        } elseif(isset($_GET['action']) && $_GET['action']=='cancellaUser'){
                $userId = $_GET["idUser"];
                $partitaId = $_GET["idPartita"];
                if($level==9){
                    rifiutaPartita($userId, $partitaId);
                }
        }
        
} else {
	/* Out of session*/ header("Location: index.php");
}

