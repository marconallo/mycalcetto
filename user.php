<?php

define('INCLUDE_CHECK',true);
require 'functions_user.php';

session_name('tzLogin');
session_set_cookie_params(2*7*24*60*60);
session_start();

if($_SESSION['id']){

	if(isset($_POST['action'])){

		switch($_POST['action']){

			case 'updateUser':
				$userId = $_POST['id'];
				$isAdmin = $_POST['isAdmin'];
				$email = mysql_real_escape_string(strip_tags($_POST['email']));
				$name = mysql_real_escape_string(strip_tags($_POST['nome']));
				$surname = mysql_real_escape_string(strip_tags($_POST['cognome']));
				$mobile = mysql_real_escape_string(strip_tags($_POST['cellulare']));
				$accetta = isset ($_POST['accetta'])?$_POST['accetta']:0;
				$level = '';
				if ($isAdmin == "1"){
					$level = mysql_real_escape_string(strip_tags($_POST['level']));
				}

				echo '<div class="container" style="width: 500px;">';
				echo '<h3>Modifica dati</h3>';
				updateUser($userId, $email, $name, $surname, $mobile, $level, $accetta);
				echo '</div>';
				exit;

			case 'updatePass':
				$userId = $_POST['id'];
				$userName = mysql_real_escape_string(strip_tags($_POST['username']));
				$oldPassword = md5(mysql_real_escape_string(strip_tags($_POST['oldPassword'])));
				$newPassword = md5(mysql_real_escape_string(strip_tags($_POST['newPassword'])));

				echo '<div class="container" style="width: 500px;">';
				echo '<h3>Modifica Password</h3>';
				updatePassword($userId, $userName, $oldPassword, $newPassword);
				echo '</div>';
				exit;
					
			case 'modifica':
				echo '<h3>Modifica Utente</h3>';
				visualizzaUtente($_POST['id_user'], true);
				echo '<div class="clear"></div>';
				exit;

			case 'elimina':
				echo '<div class="container" style="width: 500px;">';
				echo '<h3>Elimina Utente</h3>';
				eliminaUtente($_POST['id_user']);
				echo '</div>';
				exit;
		}
			
	}
} else { /* Out of session*/
	header("Location: index.php");
}
?>
