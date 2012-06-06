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
<title>Pagelle | MyCalcetto</title>
    
    <!-- Files START-->
	<?php include 'script/files.php'; ?>
	<!-- Files END-->
    <?php echo $script; ?>
    <script type="text/javascript">
    function inviaVoti(idPartita){
    	var options = {target:'#output',success:showResponse};
        var idForm = "#voti_"+idPartita;
    	$(idForm).ajaxSubmit(options);
    }
    function votaPartita(idPartita){
    	$.post("partite.php", $("#vota_partita_"+idPartita).serialize(), function(data) {
    		  $('#pagelleOutput').html(data);
    	});
    }
    function visualizzaVoti(idPartita){
    	$.post("partite.php", $("#visualizza_voti_"+idPartita).serialize(), function(data) {
    		  $('#pagelleOutput').html(data);
    	});
    }
    
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
            <h1>Le Pagelle</h1>
        </div>
        <div class="container"  style="background-image: url('img/oliver.jpg'); background-repeat: no-repeat; background-position: right; float: left; width: 65%;">
            <h3>Partite giocate</h3>
            <?php visualizzaPartitePagelle($user);?>
        </div>
        <div class="container" id="pagelleOutput" style="float: right;">
        </div>
        <div class="clear"></div>
        
<?php } else { ?>
	header("Location: index.php");
<?php }?>
        <div class="container tutorial-info" style="clear: both; margin-top: 15px;">
            Powered by <strong>Mako'</strong>.    
     	</div>
    </div>
</div>

</body>
</html>