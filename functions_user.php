<?php
if(!defined('INCLUDE_CHECK')) die('You are not allowed to execute this file directly');

require_once 'user_access/connect.php';
require_once 'user_access/functions.php';

require_once 'script/log.php';
$logDir="log";
$logFileName="debug";
$headerTitle="MyCalcetto";
$logMode="oneFile"; //oneFile: each log instance goes to the same file ([logFileName].log) | oneFilePerLog: each log instance goes to a new file ([logFileName][logNumber].log
$counterFile="debug.counter";
$logger=new Log($logDir,$logFileName,$headerTitle, $logMode, $counterFile);

function cambiaPassword($user, $oldPass, $newPass){

	$conto="SELECT PASS FROM TZ_MEMBERS WHERE ID = '$user';";
	$res = mysql_query($conto);
	if($riga = mysql_fetch_array($res)){
		if($riga['PASS'] == md5($oldPass)) { /*Vecchia Password confermata!*/
			$query="UPDATE TZ_MEMBERS SET PASS = '".md5($newPass)."' WHERE ID = '$user';";
			if(mysql_query($query)){
				echo "<div class='success'>La tua password &egrave; stata aggiornata.</div>";
				echo "</br><p>Torna alla <a href='userData.php'>pagina precedente</a>.</p>";
			}else {
				echo "<div class='error'>Si &egrave; verificato un errore, riprova.</div>";
				echo "</br><p>Torna alla <a href='userData.php'>pagina precedente</a>.</p>";
			}
		}
	}
}

function getUser($username, $password){
	global $logger;
	$res = mysql_query("SELECT ID,USR,LEVEL,NAME FROM TZ_MEMBERS WHERE USR='$username' AND PASS='".md5($password)."'");
	if ($res){
		$logger->logThis($logger->get_formatted_date()."[LOGIN] Utente: ".$username);
		return mysql_fetch_assoc($res);
	}

}

function getUserById($userid){
	return mysql_fetch_assoc(mysql_query("SELECT * FROM TZ_MEMBERS WHERE ID='$userid';"));
}

function getUserByIdAjax($userid){
	$row = mysql_fetch_assoc(mysql_query("SELECT * FROM TZ_MEMBERS WHERE ID='$userid';"));
	echo $row['ID']."|".$row['USR']."|".$row['NAME']."|".$row['SURNAME']."|".$row['EMAIL']."|".$row['MOBILE']."|".$row['LEVEL'];
}

function newUser($username, $password, $email, $name, $surname, $remoteAddress){
	global $logger;
	$query="INSERT INTO TZ_MEMBERS(usr,pass,name,surname,email,regIP,dt,level) VALUES('$username','".md5($password)."','$name','$surname','$email','$remoteAddress',NOW(),1);";
	if (mysql_query($query)) {
		$message = '
				<html>
				<head>
				  <title>Conferma registrazione</title>
				</head>
				<body>
				  <p>Ciao '.$name.',<br />la registrazione si e\' conclusa con successo.</p>
				  <p>La password che ti &egrave; stata assegnata &egrave;: <strong>'.$password.'</strong>; potrai cambiarla accedendo ai tuoi dati.</p>
				  <p><a href="http://mycalcetto.altervista.org" target="_blank">Collegati ora.</a></p>
				  <p>&nbsp;</p>
				  <p>Grazie per averci scelto!</p>
				  <p>Lo Staff di MyCalcetto</p>
				</body>
				</html>';

		send_mail('MyCalcetto <mycalcetto@altervista.org>',$email,'MyCalcetto - Registrazione', $message);
		$logger->logThis($logger->get_formatted_date()."[INFO] Nuovo utente registrato: ".$username);
		return 0;
	} else {
		return -1;
	}
}

function updateUser($id, $email, $name, $surname, $mobile, $level, $accept){
	if(!empty($level)){
		$levelString = ",LEVEL='$level'";
	} else{
		$levelString = "";
	}
	//if(!empty($accept)){
		$acceptString = ",ACCEPT='$accept'";
	//} else{
	//	$acceptString = "";
	//}
	$query="UPDATE TZ_MEMBERS SET NAME='$name',SURNAME='$surname',EMAIL='$email',MOBILE='$mobile' $levelString $acceptString WHERE ID = '$id';";
	if (mysql_query($query)) {
		echo "<div class='success'>I dati sono stati aggiornati.</div>";
		//echo "</br><p>Torna alla <a href='userData.php'>pagina precedente</a>.</p>";
	}else {
		echo "<div class='error'>Si &egrave; verificato un errore, riprova.</div>";
		//echo "</br><p>Torna alla <a href='userData.php'>pagina precedente</a>.</p>";
	}
}

function updatePassword($id, $username, $oldPassword, $newPassword){
	$query="UPDATE TZ_MEMBERS SET PASS='$newPassword' WHERE ID=$id AND USR='$username' AND PASS='$oldPassword';";
	if (mysql_query($query)) {
		echo "<div class='success'>La password &egrave; stata cambiata.</div>";
		echo "</br><p>Torna alla <a href='userData.php'>pagina precedente</a>.</p>";
	}else {
		echo "<div class='error'>Si &egrave; verificato un errore, riprova.</div>";
		echo "</br><p>Torna alla <a href='userData.php'>pagina precedente</a>.</p>";
	}
}


function getUserByIdPartita($idPartita) {
	$res = mysql_query("SELECT U.ID,U.NAME,U.SURNAME,U.USR,U.EMAIL from PARTITA P, TZ_MEMBERS U where U.ID=P.ID AND P.ID='".$idPartita."'") or die(mysql_error());
	return $res;
}

function visualizzaUtenti(){
	$query = "SELECT ID, USR, NAME, SURNAME, EMAIL, MOBILE, LEVEL, ACCEPT from TZ_MEMBERS ORDER BY NAME ASC;";
	$rs = mysql_query($query);

	if ($row = mysql_fetch_array($rs)){
		echo "<br />";
		echo "<table class='table2'>";
		echo "<thead><tr>";
		echo "<th scope='col'>Username</th>";
		echo "<th scope='col'>Nome</th>";
		echo "<th scope='col'>Cognome</th>";
		echo "<th scope='col'>E-Mail</th>";
		echo "<th scope='col'>Cell.</th>";
		echo "<th scope='col'>Livello</th>";
		echo "<th scope='col'>Accetta</th>";
		if (isset($_SESSION['level']) && $_SESSION['level']==9) {
			echo "<th scope='col'>Action</th>";
		}
		echo "</tr></thead>";
		do
		{
			$userId=$row["ID"];
			$username=$row["USR"];
			$nome=$row["NAME"];
			$cognome=$row["SURNAME"];
			$email=$row["EMAIL"];
			$mobile=$row["MOBILE"];
			$level=$row["LEVEL"];
			$accept=$row["ACCEPT"];

			echo "<tbody>";
			echo "<tr>";
			echo "<td>$username</td>";
			echo "<td>$nome</td>";
			echo "<td>$cognome</td>";
			echo "<td>$email</td>";
			echo "<td>$mobile</td>";
			echo "<td>$level</td>";
			echo "<td>$accept</td>";

			if (isset($_SESSION['level']) && $_SESSION['level']==9) {
				echo "<td>";
				echo "<form id='modifica_user_$userId' method='post' action='user.php' style='float:left;'>
			 		<input type='hidden' name='id_user' value='$userId' />
			 		<input type='hidden' name='action' value='modifica' />
			 		<img class='cancella_img' src='img/Edit_24x24.png' alt='Modifica' title=\"Modifica i dati dell'utente\" onclick='modificaUtente($userId);'>
			 		</form> &nbsp;";
				echo " &nbsp;<form id='cancella_user_$userId' method='post' action='user.php' style='float:right;'>
			 		<input type='hidden' name='id_user' value='$userId' />
			 		<input type='hidden' name='action' value='elimina' />
			 		<img class='cancella_img' src='img/Cancel_24x24.png' alt='Cancella' title=\"Elimina l'utente\" onclick='eliminaUtente($userId);'>
			 		</form>";
				echo "</td>";
			}
			echo "</tr>";
		} while ($row = mysql_fetch_array($rs));
		echo "</tbody>";
		echo "</table>";
	} else {
		echo "<div class='info'>Non ci sono utenti!</div>";
	}
}

function visualizzaUtente($idUser, $isAdmin){

	if ($isAdmin) {
		$disabled = "";
		$modUser = "'modUserAdmin()'";
		$usernameLabel = "Username";
	} else{
		$disabled = "disabled='disabled'";
		$modUser = "'modUser()'";
		$usernameLabel = "Username (sola lettura)";
	}

	$row = getUserById($idUser);
	if($row['USR'])	{ // everything is OK
		$acceptCheck="";
		if($row['ACCEPT'] == 1){
			$acceptCheck="checked=checked";
		}
		echo'
				
			<p></p>
				<table>
				<tr>
					<td>
					<fieldset>
						<legend>Modifica Dati</legend>
						<form class="form" id="userForm" action="user.php" method="post">
							<input type="hidden" name="action" value="updateUser" />
							<input type="hidden" name="id" value="'.$row['ID'].'" />
							<input type="hidden" name="isAdmin" value="'.$isAdmin.'" />
							<p class="username">
								<input type="text" name="username" id="username" value="'.$row['USR'].'" '.$disabled.' />
								<label for="username">'.$usernameLabel.'</label>
							</p>
							<p class="nome">
								<input type="text" name="nome" id="nome" value="'.$row['NAME'].'" />
								<label for="campo">Nome</label>
							</p>
							<p class="cognome">
								<input type="text" name="cognome" id="cognome" value="'.$row['SURNAME'].'" />
								<label for="cognome">Cognome</label>
							</p>
							<p class="email">
								<input type="text" name="email" id="email" value="'.$row['EMAIL'].'" />
								<label for="email">E-mail</label>
							</p>
							<p class="cellulare">
								<input type="text" name="cellulare" id="cellulare" value="'.$row['MOBILE'].'" />
								<label for="username">Cellulare</label>
							</p>
							<p class="accetta">
								<input type="checkbox" value="1" name="accetta" '.$acceptCheck.' />
								<label for="accetta">Accetta Sempre</label>
							</p>
				';
		if ($isAdmin) {
			echo '
							<p class="level">
								<input type="text" name="level" id="level" value="'.$row['LEVEL'].'" />
								<label for="level">Livello</label>
							</p>
			';
		}

		echo '
							<p>
								<a onclick='.$modUser.' class="awesome medium blue">Modifica</a>
								&nbsp;
								<a id="resetUserForm" class="awesome medium blue">Reset</a>
							</p>
						</form>
					</fieldset>
					</td>
		';

		if (!$isAdmin) {
			echo'
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td style="vertical-align: top;">
					<fieldset>
						<legend>Cambia Password</legend>
						<form class="form" id="passForm" action="user.php" method="post">
							<input type="hidden" name="action" value="updatePass" />
							<input type="hidden" name="username" value="'.$row['USR'].'" />
							<input type="hidden" name="id" value="'.$row['ID'].'" />
							<p class="password">
								<input type="password" name="oldPassword" id="oldPassword" />
								<label for="username">Vecchia Password</label>
							</p>
							<p class="password">
								<input type="password" name="newPassword" id="newPassword" />
								<label for="newPassword">Nuova Password</label>
							</p>
							<p class="password">
								<input type="password" name="newPasswordConfirm" id="newPasswordConfirm" />
								<label for="newPasswordConfirm">Conferma Nuova Password</label>
							</p>
							<p>
								<a onclick=\'modPass();\' class="awesome medium blue">Modifica</a>
							</p>
						</form>
					</fieldset>
					</td>
			';
		}
		echo'
				</tr>
				</table>
		';
	}
}

function eliminaUtente($idUser) {
	$query="DELETE FROM TZ_MEMBERS WHERE ID=$idUser;";
	if (mysql_query($query)) {
		echo "<div class='success'>Utente eliminato!</div>";
		echo "</br><p>Torna alla <a href='visualizzaUtenti.php'>pagina precedente</a>.</p>";
	}else {
		echo "<div class='error'>Si &egrave; verificato un errore!</div>";
		echo "</br><p>Torna alla <a href='visualizzaUtenti.php'>pagina precedente</a>.</p>";
	}
}
?>