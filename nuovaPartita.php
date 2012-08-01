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
<title>Nuova Partita | MyCalcetto</title>
    <!-- Files START-->
	<?php include 'script/files.php'; ?>
	<!-- Files END-->
	<link type="text/css" href="css/jquery.autocomplete.css" rel="stylesheet" />
	<link type="text/css" href="css/jquery.ui/jquery.ui.css" rel="stylesheet" />
	<script type="text/javascript" src="js/jquery.autocomplete.js"></script>
	<script type="text/javascript" src="js/jquery.validate.js"></script>
	<script type="text/javascript" src="js/jquery.metadata.js"></script>
	<script type="text/javascript" src="js/ui/ui.core.js"></script>
	<script type="text/javascript" src="js/ui/ui.datepicker.js"></script>
	<script type="text/javascript" src="js/ui/i18n/ui.datepicker-it.js"></script>
    <script type="text/javascript">
    	function nuovaPartita(){
    		var options = {beforeSubmit:checkPartitaForm,target:'#output',success:showResponse}; 
	       	$("#partitaForm").ajaxSubmit(options);
    	}
    	// pre-submit  
        function checkPartitaForm()  {
        	$("#formButton").attr("disabled", "disabled");
            $("#formWait").show();
        	return $('#partitaForm').validate().form();
        }
		$(document).ready(function() {

			$("#formWait").hide();

			$("#partitaForm").validate({
				rules: {
					data: {required: true, date: true},
					ora: {required: true},
					campo: {required: true}
				},
				messages: {
					campo: "Campo obbligatorio",
					ora: "Campo obbligatorio",
					data: {
						required: "Campo obbligatorio",
						date: "Formato non corretto"
					}
				}
			});
			
	    	//autocomplete
	    	$("#campo").autocomplete("campi.php?action=list", {
	    		width: 200,
	    		matchContains: true,
	    		autoFill: false,
	    		formatItem: function(data) {
	    			return data[1] + " [" + data[2] + "]";
	    		},
	    		formatResult: function(data) {
	    			return data[1];
	    		}
	    	});
	    	$("#campo").result(function(event, data, formatted) {
	    		if (data)
	    			$("#idCampo").val(data[0]);
	    	});
    	});
		$(function(){
			$.datepicker.setDefaults($.extend({showMonthAfterYear: false}, $.datepicker.regional['it']));
			$("#data").datepicker({
				dateFormat: 'yy-mm-dd', 
				firstDay: 1, 
				changeMonth: true, 
				changeYear: true,
				minDate: +2
			});
		});
		 // post-submit callback 
	    function showResponse(responseText, statusText, xhr, $form)  {
	    	$("#formWait").hide();
	    	$("#formButton").removeAttr("disabled");
	        $.colorbox({
	            inline:true, 
	            href:"#output",
	            onCleanup:function(){ window.location.href='visualizzaPartite.php'; }
	        });
	    }		

    </script>

    <?php echo $script; ?>    
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
	        <h1>Nuova partita</h1>
        </div>
		<div class="container">
	        <h3>Partite in programma</h3>
			<?php visualizzaPartiteEsistenti($user);?>
          	<div class="clear"></div>
        </div>
		<div class="container">
	        <h3>Crea Nuova</h3>
	        <p>&nbsp;</p>
			<form class="form" id="partitaForm" action="partite.php" method="post">
				<input type="hidden" name="action" value="creaNuova" />
				<input type="hidden" name="invitaTutti" id="invitaTutti" value="1"/>
				<p class="data">
					<label for="data" style="margin-right: 25px;">Data</label>
					<input type="text" name="data" id="data" readonly="readonly" />
					<span>&nbsp;</span>				
					<label for="ora">Ora</label>
					<input type="text" name="ora" id="ora" value="20:00" />
				</p>
				<p class="campo">
					<input type="hidden" name="idCampo" id="idCampo" />
					<label for="campo">Campo</label>
					<input type="text" name="campo" id="campo" />
				</p>
				<p class="note">
					<label for="note">Note</label><br/>
					<textarea name="note" id="note"></textarea>
				</p>
            	<p class="giocatori">
					<label for="giocatori">Numero giocatori</label><br/>
					<select name="giocatori" id="giocatori">
                        <option value="10">10</option>
                        <option value="12">12</option>
                        <option value="14">14</option>
                    </select>
				</p>
				<p class="invita">
					<input type="checkbox" value="1" checked="checked" />
					<label for="invitaTutti">Invita tutti</label>
				</p>
				<p>
					<a onclick='nuovaPartita();' class="awesome medium blue" id="formButton">Invia</a> &nbsp; <img id="formWait" alt="wait" src="img/indicator.gif" style="vertical-align: bottom;">
				</p>
			</form>
      	<div class="clear"></div>
        </div>
        
<?php } else { 
header("Location: index.php");
}?>
      	<div class="container tutorial-info">
     		Powered by <strong>Mako'</strong>.    
     	</div>
	</div>
</div>

</body>
</html>
