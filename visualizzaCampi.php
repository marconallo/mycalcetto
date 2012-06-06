<?php

define('INCLUDE_CHECK',true);
// Those files can be included only if INCLUDE_CHECK is defined
require_once 'user_access/connect.php';
require_once 'user_access/functions.php';
require_once 'functions_campi.php';


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
<title>Campi | MyCalcetto</title>
    
    <!-- Files START-->
	<?php include 'script/files.php'; ?>
	<!-- Files END-->
    <?php echo $script; ?>
    <script type="text/javascript" src="js/jquery.validate.js"></script>    
    <script type="text/javascript">
	    $(document).ready(function() {
			var campoForm = $("#campoForm").validate({
				rules: {
					nome: {required: true},
					indirizzo: {required: true}
				},
				messages: {
					nome: "Campo obbligatorio",
					indirizzo: "Campo obbligatorio"
				}
			});
	    });
	    
	    function nuovoCampo(){
			document.body.style.cursor = 'wait';
			var options = { beforeSubmit: checkCampoForm, target:'#output', success:showResponse}; 
	       	$("#campoForm").ajaxSubmit(options);
	       	document.body.style.cursor = 'default';
		}
    	
	    function eliminaCampo(id){
	    	if (confirm('Vuoi cancellare questo campo?')) {
	    		var options = {target:'#output',success:showResponse};
	    		var idForm = "#cancella_form_"+id;
	    		$(idForm).ajaxSubmit(options);
	    		}
	    	}
		// post-submit callback 
	    function showResponse(responseText, statusText, xhr, $form)  {
	        $.colorbox({
	            inline:true, 
	            href:"#output",
	            onCleanup:function(){ location.reload(); }
	        });
	    }

    	// pre-submit  
        function checkCampoForm()  {
        	return $('#campoForm').validate().form();
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
	        <h1>I campi</h1>
        </div>
		<div class="container">
	        <h3>Campi presenti</h3>
			<?php visualizzaCampi();?>
          	<div class="clear"></div>
        </div>
        <?php if ($_SESSION['level']==9) { ?>
        <div class="container">
	        <h3>Crea Nuovo</h3>
	        <p>&nbsp;</p>
			<form class="form" id="campoForm" action="campi.php" method="post">
				<input type="hidden" name="action" value="creaNuovo" />
				<p class="nome">
					<input type="text" name="nome" id="nome" />
					<label for="nome">Nome</label>
				</p>
				<p class="indirizzo">
					<input type="text" name="indirizzo" id="indirizzo" />
					<label for="indirizzo">Indirizzo</label>
				</p>
				<p class="telefono">
					<input type="text" name="telefono" id="telefono" />
					<label for="telefono">Telefono</label>
				</p>
				<p class="sito">
					<input type="text" name="sito" id="sito" />
					<label for="sito">Sito</label>
				</p>
				<p class="prezzo">
					<input type="text" name="prezzo" id="prezzo" value="00,00" style="width: 40px;" />
					<label for="prezzo"> € </label>
					<input type="checkbox" name="coperto" id="coperto" value="1" class="checkbox" />
					<label for="coperto">Coperto</label>
				</p>
				<p>
					<a onclick='nuovoCampo();' class="awesome medium blue">Invia</a>
				</p>
			</form>
      	<div class="clear"></div>
        </div>
        <?php }?>
        
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