<div>
	<button id="home_btn" class="large awesome">Home</button>
<?php if (isset($_SESSION['level']) && $_SESSION['level']==9) { ?>
	<button id="nuovaPartita_btn" class="large blue awesome">Nuova</button>
<?php }?>
	<button id="partite_btn" class="large red awesome">Partite</button>
	<button id="campi_btn" class="large green awesome">Campi</button>
	<button id="regolamento_btn" class="large magenta awesome">Regole</button>
	<button id="bacheca_btn" class="large orange awesome">*Bacheca*</button>
        <br><br>
        <button id="pagelle_btn" class="large yellow awesome">Pagelle</button>
        <button id="statistiche_btn" class="large yellow awesome">Statistiche<br/><span style="color: blue;">*new*</span></button>
	
</div>