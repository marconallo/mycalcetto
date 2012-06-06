<link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
<link rel="stylesheet" type="text/css" href="login_panel/css/slide.css" media="screen" />
<link rel="stylesheet" type="text/css" href="css/colorbox.css" media="screen" />
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Neucha&subset=latin">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="js/menu.js"></script>
<!-- PNG FIX for IE6 -->
<!-- http://24ways.org/2007/supersleight-transparent-png-in-ie6 -->
<!--[if lte IE 6]>
	<script type="text/javascript" src="login_panel/js/pngfix/supersleight-min.js"></script>
<![endif]-->
<script type="text/javascript" src="login_panel/js/slide.js"></script>
<script type="text/javascript" src="js/jquery.colorbox-min.js"></script>
<script type="text/javascript" src="js/jquery.form.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$('#home_btn').click(function() {window.location.href='index.php'; return false;});
	$('#partite_btn').click(function() {window.location.href='visualizzaPartite.php'; return false;});
	$('#nuovaPartita_btn').click(function() {window.location.href='nuovaPartita.php'; return false;});
	$('#campi_btn').click(function() {window.location.href='visualizzaCampi.php'; return false;});
	$('#pagelle_btn').click(function() {window.location.href='visualizzaPagelle.php'; return false;});
	$('#regolamento_btn').click(function() {window.location.href='visualizzaRegole.php'; return false;});
	$('#bacheca_btn').click(function() {window.location.href='bacheca.php'; return false;});
        $('#statistiche_btn').click(function() {window.location.href='visualizzaStatistiche.php'; return false;});
});
</script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-19186149-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>