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

if(!isset($_SESSION['id'])) {
	header("Location: index.php");
	exit;
}

if(isset($_SESSION['id']) && !isset($_COOKIE['myCalcettoID']) && !$_SESSION['rememberMe']){
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

if (isset($_SESSION['level']) && $_SESSION['level']==9) {
    $isAdmin=true;
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
			$("#passForm").validate({
				rules: {
					oldPassword: {required: true},
					newPassword: {required: true},
					newPasswordConfirm: {required: true, equalTo: "#newPassword"}
				},
				messages: {
					oldPassword: "Campo obbligatorio",
					newPassword: "Campo obbligatorio",
					newPasswordConfirm: {
						required: "Campo obbligatorio",
						equalTo: "La due password devono essere uguali"
					}
				}
			});

    		$('input').click(function() {
            	this.focus();
            	this.select();
        	});

        	$('#resetUserForm').click(function(){
        		userForm.resetForm();
        	});

        });
    	function modUser(){
    		document.body.style.cursor = 'wait';
	    	var options = { beforeSubmit: checkUserForm, target:'#output', success:showResponse}; 
		   	$("#userForm").ajaxSubmit(options);
	       	document.body.style.cursor = 'default';
    	}
    	function modPass(){
    		document.body.style.cursor = 'wait';
    		var options = { beforeSubmit: checkPassForm, target:'#output', success:showResponse}; 
		   	$("#passForm").ajaxSubmit(options);
	       	document.body.style.cursor = 'default';
    	}

    	// pre-submit  
        function checkUserForm()  {
        	return $('#userForm').validate().form();
        }

        function checkPassForm()  {
        	return $('#passForm').validate().form();
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
		if(isset($_SESSION['id'])) {
			$user = $_SESSION['id'];
		?>
		<div class="container">
			<?php include 'script/menu.php'; ?>
	        <h1>Modifica i dati</h1>
        </div>
		<div class="container">
			<h3></h3>
			<?php visualizzaUtente($_SESSION['id'], $isAdmin)?>
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