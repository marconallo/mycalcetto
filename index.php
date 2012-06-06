<?php

define('INCLUDE_CHECK',true);
// Those files can be included only if INCLUDE_CHECK is defined
require_once 'user_access/connect.php';
require_once 'user_access/functions.php';
require_once 'functions_partite.php';
require_once 'functions_user.php';

// Starting the session
session_name('tzLogin');
session_set_cookie_params(2*7*24*60*60);
session_start();

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

if (isset($_POST['submit']) && $_POST['submit']=='Login'){ // Checking whether the Login form has been submitted
	
	// Will hold our errors
	$err = array();
			
	if(!$_POST['username'] || !$_POST['password'])
		$err[] = 'Tutti i campi devono essere riempiti!';
	
	if(!count($err)){
		// Escaping all input data
		$_POST['username'] = mysql_real_escape_string(strip_tags($_POST['username']));
		$_POST['password'] = mysql_real_escape_string(strip_tags($_POST['password']));
		$_POST['rememberMe'] = (int)$_POST['rememberMe'];
		
		$row = getUser($_POST['username'],$_POST['password']);

		if($row['USR'])	{// If everything is OK login
		
			// Store some data in the session
			$_SESSION['usr']=$row['USR'];
			$_SESSION['id'] = $row['ID'];
			$_SESSION['level'] = $row['LEVEL'];
			$_SESSION['name'] = $row['NAME'];
			$_SESSION['rememberMe'] = $_POST['rememberMe'];
			
			$TempoDiValidita = 2592000;
			setcookie('myCalcettoID',$_SESSION['id'],time()+$TempoDiValidita);
			setcookie('myCalcettoUSR',md5($_SESSION['usr']),time()+$TempoDiValidita);
		} else $err[]='Username o password errati!';
	}
	
	if($err){
	// Save the error messages in the session
	$_SESSION['msg']['login-err'] = implode('<br />',$err);
	}

	header("Location: index.php");
	exit;
}else if(isset($_POST['submit']) && $_POST['submit']=='Invia'){	// If the Register form has been submitted
	
	$err = array();
	
	if(strlen($_POST['username'])<4 || strlen($_POST['username'])>32){
		$err[]='Lo username deve avere almeno 4 caratteri!';
	}
	
	if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['username'])){
		$err[]='Hai inserito caratteri non validi!';
	}
	
	if(!checkEmail($_POST['email'])){
		$err[]='Indirizzo e-mail non valido!';
	}
	
	if(!count($err)){ // If there are no errors

		// Generate a random password		
		$pass = substr(md5($_SERVER['REMOTE_ADDR'].microtime().rand(1,100000)),0,6);
		
		// Escape the input data
		$_POST['email'] = mysql_real_escape_string(strip_tags($_POST['email']));
		$_POST['username'] = mysql_real_escape_string(strip_tags($_POST['username']));
		$_POST['name'] = mysql_real_escape_string(strip_tags($_POST['name']));
		$_POST['surname'] = mysql_real_escape_string(strip_tags($_POST['surname']));
		
		$result = newUser($_POST['username'],$pass,$_POST['email'],$_POST['name'],$_POST['surname'],$_SERVER['REMOTE_ADDR']);
		if($result==0){
			$_SESSION['msg']['reg-success']='A breve riceverai una mail con la tua password!';
		} else {
			$err[]='Username o e-mail gi&agrave; presenti!';
		}
	}

	if(count($err)) {
		$_SESSION['msg']['reg-err'] = implode('<br />',$err);
	}
	
	header("Location: index.php");
	exit;
}

$script = '';
if($_SESSION['msg']){
	// The script below shows the sliding panel on page load
	$script = '
	<script type="text/javascript">
		$(function(){
			$("div#panel").show();
			$("#toggle a").toggle();
		});
	</script>';
}

//Check if is set the RememberMe Cookie
if(isset($_COOKIE['myCalcettoID']) && isset($_COOKIE['myCalcettoUSR'])) {
	$myCalcettoID = $_COOKIE['myCalcettoID'];
	$myCalcettoUSR = $_COOKIE['myCalcettoUSR'];
	$rowUser = getUserById($myCalcettoID);
	$userName = $rowUser["USR"];
	
	if (md5($userName) == $myCalcettoUSR){
		$_SESSION['id'] = $myCalcettoID;
		$_SESSION['usr'] = $rowUser['USR'];
		$_SESSION['level'] = $rowUser['LEVEL'];
		$_SESSION['name'] = $rowUser['NAME'];
	}
	else{
		setcookie('myCalcettoID','',-1);
		setcookie('myCalcettoUSR','',-1);
		header("Location: index.php");
	}
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
    $(document).ready(function() {
		$('a.click').click(function() {
			$("div#panel").slideDown("slow");
			$("#toggle a").toggle();
    	});
    });
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
		<div class="container beta">
	        <?php 
			if(isset($_SESSION['id'])) {
				include 'script/menu.php';		
				$userId = $_SESSION['id'];
				$name = $_SESSION['name'];
			}
			?>
			<h1 class="logo">MyCalcetto</h1>
        </div>
		<?php if(isset($_SESSION['id'])) {
			$numPartite=countPartiteGiocate($userId);
			$numPartiteProssime=countPartiteProssime($userId);
			$numVoti=countPartiteConVoti($userId);
			$mediaVoti= round(getMediaVotiTotale($userId),2);
			
			if ($numVoti>0) {
				$totaleVoti=getTotaleVoti($userId);
				$commentoMedia='';
				if ($mediaVoti>=8) {
					$commentoMedia='complimenti!';
				}elseif ($mediaVoti<8 && $mediaVoti>=6){
					$commentoMedia='continua cos&igrave;';
				}else {
					$commentoMedia='puoi fare di pi&ugrave;...';
				}
				$arrayVoti=getVotiArray($userId);
			}else {
				$mediaVoti='n.d.';
			}
		?>
        <div class="container home">
			<h3>Statistiche</h3>
			<p></p>
	        <p>Ciao <?php echo $name; ?>,<br /> qui puoi trovare alcuni dati che saranno aggiornati dopo ogni partita.</p>
	        <table class='table2'>
		        <tbody>
		        <tr>
					<th scope='row'>Prossime partite</th>
					<td><?php echo $numPartiteProssime;?></td>
				</tr>
				<tr>
					<th scope='row'>Partite Giocate</th>
					<td><?php echo $numPartite;?></td>
				</tr>
				<tr>
					<th scope='row'>Numero voti</th>
					<td><?php echo $numVoti; ?></td>
				</tr>
				<tr>
					<th scope='row'>Media voti</th>
					<td><?php echo $mediaVoti; ?></td>
                                        <td><?php echo isset ($commentoMedia)?$commentoMedia:""; ?></td>
				</tr>
				</tbody>
			</table>
			<p></p>
			<?php if (!empty($arrayVoti)) { ?>
				<div><img src="http://chart.apis.google.com/chart?chf=c,lg,0,EFEFEF,0,BBBBBB,1&chxr=0,0,10&chxs=0,676767,11.5,0,lt,676767&chxt=y&chs=300x225&cht=lc&chco=3D7930&chds=0,10&chd=t:<?php echo $arrayVoti; ?>&chg=10,-1,1,1&chls=2,4,0&chm=B,C5D4B5BB,0,0,0&chtt=Andamento+voti&chm=B,C5D4B5BB,0,0,0|s,FF0000,0,-1,5" width="300" height="225" alt="Andamento voti" /></div>
			<?php }?>
			
			<div class="clear"></div>
        </div>
        <?php } else { ?>
		<div class="container home">
	        <h3>Accedi</h3>
			<p>Benvenuto visitatore,<br />per sfruttare le funzionalit&agrave; del sito devi effettuare il <a href="#" class="click">login</a>.</p>
			<p>Se non sei ancora registrato, fallo ora, <a href="#" class="click">qui</a>.</p>
          	<div class="clear"></div>
        </div>
		<?php } ?>
      	<div class="container tutorial-info">
     		Powered by <strong>Mako'</strong>.    
     	</div>
	</div>
</div>

</body>
</html>
