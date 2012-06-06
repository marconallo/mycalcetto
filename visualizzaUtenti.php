<?php

define('INCLUDE_CHECK',true);
// Those files can be included only if INCLUDE_CHECK is defined
require_once 'user_access/connect.php';
require_once 'user_access/functions.php';
require_once 'functions_user.php';


// Starting the session
session_name('tzLogin');
// Making the cookie live for 2 weeks
session_set_cookie_params(2*7*24*60*60);
session_start();

if(!isset($_SESSION['id']) || (isset($_SESSION['level']) && $_SESSION['level']!=9)){
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
<title>Utenti | MyCalcetto</title>
    
    <!-- Files START-->
	<?php include 'script/files.php'; ?>
	<!-- Files END-->
    <?php echo $script; ?>
    <script type="text/javascript" src="js/jquery.validate.js"></script>    
    <script type="text/javascript">
	    $(document).ready(function() {
	    	var userForm = $("#userForm").validate({
				rules: {
					nome: {required: true},
					cognome: {required: true},
					email: {required: true, email: true}
				},
				messages: {
					nome: "Campo obbligatorio",
					cognome: "Campo obbligatorio",
					email: "Inserisci un indirizzo e-mail valido"
				}
			});
	    });
    	
	    function eliminaUtente(id){
	    	if (confirm('Vuoi cancellare questo utente?')) {
	    		var options = {target:'#output',success:showResponse};
	    		var idForm = "#cancella_user_"+id;
	    		$(idForm).ajaxSubmit(options);
	    	}
	    }

	    function modificaUtente(id){
	    	$.post("user.php", $("#modifica_user_"+id).serialize(), function(data) {
	    		  $('#modUserOutput').html(data);
	    	});
	    }

		function modUserAdmin(id){
			document.body.style.cursor = 'wait';
	    	var options = { beforeSubmit: checkUserForm, target:'#output', success:showResponse}; 
		   	$("#userForm").ajaxSubmit(options);
	       	document.body.style.cursor = 'default';
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
        function checkUserForm()  {
        	return $('#userForm').validate().form();
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
		<?php 
		if($_SESSION['id']) {
			$user = $_SESSION['id'];
		?>
		<div class="container">
			<?php include 'script/menu.php'; ?>
	        <h1>Utenti del sistema</h1>
        </div>
		<div class="container">
	        <h3></h3>
			<?php visualizzaUtenti();?>
          	<div class="clear"></div>
        </div>
        
        <div class="container" id="modUserOutput">
          	
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