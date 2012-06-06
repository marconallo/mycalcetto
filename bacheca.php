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
<title>Bacheca | MyCalcetto</title>
    
    <!-- Files START-->
	<?php include 'script/files.php'; ?>
	<!-- Files END-->
        <?php echo $script; ?>
	<script type="text/javascript" src="js/shoutbox.js"></script>
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
	        <h1>Bacheca</h1>
        </div>
        <div class="container">
        	<h3>Invia il tuo messaggio</h3>
        	<p></p>
        	<form class="form" id="form" method="post">
        	
				<input type="hidden" name="nick" id="nick" value="<?php echo $_SESSION['usr']; ?>" />
				<p class="message">
					<label for="message" style="margin-right: 25px;">Messaggio</label>
					<input type="text" name="message" id="message" maxlength="255" style="width: 500px;" />
				</p>
				<p></p>
				<p>
					<input id="send" type="submit" value="Invia!" style="width: 150px;" />
				</p>
			</form>
        </div>
        <div class="container bacheca"  style="background-image: url('img/bacheca.jpg'); background-repeat: no-repeat; background-position: right;">
        	<h3>Ultimi messaggi</h3>
        	<div id="loading"><img src="img/indicator.gif" alt="Loading..." /></div>
	        <table id="messageList" style="width: 70%">
				
			</table>
          	<div class="clear"></div>
        </div>
        
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