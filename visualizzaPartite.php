<?php

define('INCLUDE_CHECK',true);
// Those files can be included only if INCLUDE_CHECK is defined
require_once 'user_access/connect.php';
require_once 'user_access/functions.php';
require_once 'functions_partite.php';
require_once 'functions_user.php';

// Starting the session
session_name('tzLogin');
// Making the cookie live for 2 weeks
session_set_cookie_params(2*7*24*60*60);
session_start();

if(!isset($_SESSION['id'])){
	header("Location: index.php");
	exit;
}

if(isset($_SESSION['id']) && !isset($_COOKIE['myCalcettoID']) && !isset($_SESSION['rememberMe'])){
	// If you are logged in, but you don't have the tzRemember cookie (browser restart)
	// and you have not checked the rememberMe checkbox:

	// Destroy the session
	$_SESSION = array();
	session_destroy();
}

if(isset($_GET['logoff'])){
	$_SESSION = array();
	session_destroy();
	setcookie('myCalcettoID','',-1);
	setcookie('myCalcettoUSR','',-1);
	
	header("Location: index.php");
	exit;
}
$script = '';

if($_SESSION['msg'])
{
	// The script below shows the sliding panel on page load
	$script = '
	<script type="text/javascript">
		$(function(){
			$("div#panel").show();
			$("#toggle a").toggle();
		});
	</script>';
}
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MyCalcetto</title>
    
    <!-- Files START-->
	<?php include 'script/files.php'; ?>
	<!-- Files END-->
    <?php echo $script; ?>
    <script type="text/javascript">
    function accettaPartita(id){
    	var options = {target:'#output',success:showResponse};
        var idForm = "#accetta_form_"+id;
    	$(idForm).ajaxSubmit(options);
    	}
    function rifiutaPartita(id){
        if (confirm('Non giochi questa partita?')) {
    		var options = {target:'#output',success:showResponse};
    		var idForm = "#rifiuta_form_"+id;
	    	$(idForm).ajaxSubmit(options);
            }
    	}
    	
    function cancellaUserPartita(idUser, idPartita){
    	if (confirm('Cancellare l\'utente da questa partita?')) {
    		document.location = "./partite.php?action=cancellaUser&idPartita="+idPartita+"&idUser="+idUser;
            }
    	}
    function cancellaPartita(id){
    	if (confirm('Proseguendo la partita verr&agrave; annullata?')) {
    		var options = {target:'#output',success:showResponse};
    		var idForm = "#cancella_form_"+id;
    		$(idForm).ajaxSubmit(options);
    		}
    	}
    function invitaUtenti(id){
    	if (confirm('Vuoi invitari altri utenti a questa partita?')) {
    		var options = {target:'#output',success:showResponse};
    		var idForm = "#invita_form_"+id;
    		$(idForm).ajaxSubmit(options);
    		}
    	}
    function aggiungiOspiti(id){
    	if (confirm('Vuoi invitari altri utenti a questa partita?')) {
    		var options = {target:'#output',success:showResponse};
    		var idForm = "#invita_form_"+id;
    		$(idForm).ajaxSubmit(options);
    		}
    	}
    function chiudiPartita(id){
    	if (confirm('Proseguendo la partita verra\' confermata!')) {
    		var options = {target:'#output',success:showResponse};
    		var idForm = "#chiudi_form_"+id;
    		$(idForm).ajaxSubmit(options);
    		}
    	}	
    
	$(document).ready(function() {
    	$("a.visualizzaPartecipanti").colorbox();
    });
	// post-submit callback 
    function showResponse(responseText, statusText, xhr, $form)  {
        $.colorbox({
            inline:true, 
            href:"#output",
            onCleanup:function(){ location.reload(); }
        });
    } 
    </script>
</head>

<body>
<div style="display: none;">
<div id="output"></div> 
</div>
<!-- Panel START-->
<?php include 'script/panel.php'; ?>
<!-- Panel END-->

<div class="pageContent">
	<div id="main">
		<div class="container">
			<?php 
			if($_SESSION['id']) {
				include 'script/menu.php';		
				$user = $_SESSION['id'];
			?>
	        <h1>Le tue partite</h1>
        </div>
		<div class="container">
	        <h3>Partite che hai accettato</h3>
			<?php visualizzaPartiteAccettate($user);?>
          	<div class="clear"></div>
        </div>
		<div class="container">
	        <h3>Inviti per le prossime partite</h3>
			<?php visualizzaProssimePartite($user);?>
      	<div class="clear"></div>
        </div>
        <?php if (isset($_SESSION['level']) && $_SESSION['level']==9) { ?>
            <div class="container">
	        <h3>Tutte le partite</h3>
			<?php visualizzaPartiteAdmin($user);?>
      	<div class="clear"></div>
        </div>
        <?php } ?>        
        
<?php } else { ?>
	header("Location: index.php");
<?php }?>
      	<div class="container tutorial-info">
     		Powered by <strong>Mako'</strong>.    
     	</div>
	</div>
</div>

</body>
</html>