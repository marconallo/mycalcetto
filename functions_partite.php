<?php

if (!defined('INCLUDE_CHECK'))
    die('You are not allowed to execute this file directly');

require_once 'user_access/connect.php';
require_once 'user_access/functions.php';
require_once 'functions_campi.php';
require_once 'functions_user.php';

require_once 'script/log.php';
$logDir = "log";
$logFileName = "debug";
$headerTitle = "MyCalcetto";
$logMode = "oneFile"; //oneFile: each log instance goes to the same file ([logFileName].log) | oneFilePerLog: each log instance goes to a new file ([logFileName][logNumber].log
$counterFile = "debug.counter";
$logger = new Log($logDir, $logFileName, $headerTitle, $logMode, $counterFile);

function accettaPartita($user, $id_partita) {

    $numero_giocatori_query = "SELECT NUM_GIOCATORI AS NUMERO FROM PARTITA WHERE ID = $id_partita";
    $res_num_giocatori = mysql_query($numero_giocatori_query);
    $riga_num_giocatori = mysql_fetch_array($res_num_giocatori);
    $numero_giocatori = $riga_num_giocatori['NUMERO'];
    
    $conto2 = "SELECT USER_ID FROM PARTECIPAZIONE WHERE USER_ID = '$user' AND ID_PARTITA = $id_partita AND PARTECIPA = 1";
    $res2 = mysql_query($conto2);
    $riga2 = mysql_fetch_array($res2);
    if ($riga2) { /* Gia' iscritto */
        echo "<div class='warning'>Sei gi&agrave; iscritto a questa partita!</div>";
        echo "<p>Torna alla <a href='visualizzaPartite.php'>pagina precedente</a>.</p>";
    } else { /* Da iscrivere */
        $conto = "SELECT count(USER_ID) as NUMERO FROM PARTECIPAZIONE WHERE ID_PARTITA = $id_partita AND PARTECIPA = 1";
        $res = mysql_query($conto);
        $riga = mysql_fetch_array($res);
        if ($riga) {
            if ($riga['NUMERO'] < $numero_giocatori) { /* C'e' ancora posto */

                $query = "UPDATE PARTECIPAZIONE SET PARTECIPA = 1 WHERE USER_ID = '$user' AND ID_PARTITA = $id_partita";
                $result = mysql_query($query);

                echo "<div class='success'>La tua iscrizione alla partita &egrave; stata registrata!<br />Ti verr&agrave; inviata un'e-mail quando la partita sar&agrave; confermata.</div>";
                echo "</br><p>Torna alla <a href='visualizzaPartite.php'>pagina precedente</a>.</p>";

                $conto = "SELECT count(USER_ID) as NUMERO FROM PARTECIPAZIONE WHERE ID_PARTITA = $id_partita AND PARTECIPA = 1";
                $res = mysql_query($conto);
		$riga = mysql_fetch_array($res);
                if ($riga) {
                    if ($riga['NUMERO'] == $numero_giocatori) { /* Iscritti tutti! */
                        chiudiPartita($id_partita);
                    }
                } else { /* Errore query */
                    echo "<div class='error'>Si &egrave; verificato un errore!</div>";
                    echo "</br><p>Torna alla <a href='visualizzaPartite.php'>pagina precedente</a>.</p>";
                }
            } else { /* Non c'e' piu' posto */
                echo "<div class='warning'>Mi dispiace, non ci sono pi&ugrave; posti disponibili! Max: $numero_giocatori.</div>";
                echo "</br><p>Torna alla <a href='visualizzaPartite.php'>pagina precedente</a>.</p>";
            }
        } else { /* Errore query */
            echo "<div class='error'>Si &egrave; verificato un errore!</div>";
            echo "</br><p>Torna alla <a href='visualizzaPartite.php'>pagina precedente</a>.</p>";
        }
    }
}

function chiudiPartita($idPartita) {

    global $logger;
    $rs1 = getPartitaById($idPartita);
    $row1 = mysql_fetch_array($rs1);
    $data = $row1["DATA"];
    $ora = $row1["ORA"];

    $rs2 = getCampoById($row1["ID_CAMPO"]);
    $row2 = mysql_fetch_array($rs2);
    $nomeCampo = $row2["NOME"];
    $indCampo = $row2["INDIRIZZO"];

    $rs = getPartecipanti($idPartita);
    while ($row = mysql_fetch_array($rs)) {
        $nome = $row["NAME"];
        $email = $row["EMAIL"];

        $message = '
				<html>
				<head>
				  <title>Partita Confermata</title>
				</head>
				<body style="font-family: verdana, sans-serif; color: #272727;">
				  <p>Ciao ' . $nome . ',<br />la partita &egrave; stata <strong>confermata</strong>!</p>
				  <p>
				  Data: <strong>' . $data . '</strong><br />
				  Ora: <strong>' . $ora . '</strong><br />
				  Dove: ' . $nomeCampo . ' - ' . $indCampo . '<br />
				  </p>
				  <p>Trovi i dettagli sul <a href="http://mycalcetto.altervista.org/visualizzaPartite.php" target="_blank">sito</a>.</p>
				  <p>Saluti,<br/>il team di MyCalcetto</p>
				</body>
				</html>';

        if (send_mail('MyCalcetto <mycalcetto@altervista.org>', $email, 'Partita Confermata', $message, 'mycalcetto@altervista.org')) {
            $logger->logThis($logger->get_formatted_date() . '[INFO] Conferma inviata a: ' . $email);
            sleep(1);
        } else {
            $logger->logThis($logger->get_formatted_date() . '[ERROR] Impossibile inviare conferma a: ' . $email);
        }
    }
    $query_elimina = "DELETE FROM PARTECIPAZIONE WHERE ID_PARTITA = $idPartita AND PARTECIPA <> 1;";
    if (mysql_query($query_elimina)) {
        $logger->logThis($logger->get_formatted_date() . '[INFO] Eliminati i non-partecipanti');
    } else {
        $logger->logThis($logger->get_formatted_date() . '[ERROR] Impossibile eliminare i non-partecipanti');
    }
    $query_close = "UPDATE PARTITA SET STATO = 'C' WHERE ID = $idPartita;";
    if (mysql_query($query_close)) {
        $logger->logThis($logger->get_formatted_date() . '[INFO] Partita chiusa correttamente!');
    } else {
        $logger->logThis($logger->get_formatted_date() . '[ERROR] Impossibile chiudere la partita');
    }
}

function rifiutaPartita($user, $id_partita) {

    $query = "UPDATE PARTECIPAZIONE SET PARTECIPA = -1 WHERE USER_ID = '$user' AND ID_PARTITA = $id_partita";
    $result = mysql_query($query);
    echo "<div class='info'>Partita rifiutata.</div>";
    echo "</br><p>Torna alla <a href='visualizzaPartite.php'>pagina precedente</a>.</p>";
}

function cancellaPartita($user, $id_partita) {

    global $logger;
    $query_partecipanti = "SELECT u.ID AS USER_ID, u.EMAIL AS EMAIL, u.NAME AS NOME, x.ID_ORGANIZZATORE, u2.NAME AS NOME_ORG, u2.SURNAME AS COGNOME_ORG, u2.EMAIL AS EMAIL_ORG, DATE_FORMAT(x.ORA_INIZIO,'%H.%i') AS ORA, DATE_FORMAT(x.ORA_INIZIO,'%d-%m-%Y') AS DATA, c.NOME AS NOME_CAMPO FROM PARTECIPAZIONE p, TZ_MEMBERS u, TZ_MEMBERS u2, CAMPO c, PARTITA x WHERE p.ID_PARTITA = $id_partita AND x.ID = p.ID_PARTITA AND x.ID_CAMPO = c.ID AND u.ID = p.USER_ID AND x.ID_ORGANIZZATORE = u2.ID AND p.PARTECIPA = 1 AND u.ID <> '$user';";
    $result_partecipanti = mysql_query($query_partecipanti);

    $c = 0;
    while ($row = mysql_fetch_array($result_partecipanti)) {
        $data = $row["DATA"];
        $ora = $row["ORA"];
        $nome_campo = $row["NOME_CAMPO"];
        $nome = $row["NOME"];
        $mail = $row["EMAIL"];
        $email_org = $row["EMAIL_ORG"];
        $nome_org = $row["NOME_ORG"] . " " . $row["COGNOME_ORG"];

        $message = '
				<html>
				<head>
				  <title>Partita Annullata</title>
				</head>
				<body style="font-family: verdana, sans-serif; color: #272727;">
				  <p>Ciao ' . $nome . ',<br />la seguente partita &egrave; stata <span style="font-weight: bold; color: red;">ANNULLATA</span> dall\'organizzatore.</p>
				  <p>
				  Data: <strong>' . $data . '</strong><br />
				  Ora: <strong>' . $ora . '</strong><br />
				  Dove: ' . $nome_campo . '<br />
				  Organizzatore: ' . $nome_org . '
				  </p>
				  <p>Saluti,<br/>lo staff di MyCalcetto</p>
				</body>
				</html>';
        if (send_mail('MyCalcetto <mycalcetto@altervista.org>', $mail, 'MyCalcetto - Partita ANNULLATA', $message)) {
            $c = $c + 1;
            $logger->logThis($logger->get_formatted_date() . '[INFO] Cancellazione inviata a: ' . $mail);
            sleep(1);
        } else {
            $logger->logThis($logger->get_formatted_date() . '[ERROR] Impossibile inviare cancellazione a: ' . $mail);
        }
    }

    //$query = "DELETE FROM PARTITA WHERE ID = $id_partita";
    $query = "UPDATE PARTITA SET STATO = 'A' WHERE ID = $id_partita";
    $result = mysql_query($query);

    //$query2 = "DELETE FROM PARTECIPAZIONE WHERE ID_PARTITA = $id_partita";
    //$result2 = mysql_query($query2);

    echo "<div class='info'>La partita &egrave; stata annullata. &Egrave; stata inviata una e-mail a chi era iscritto ($c persone).<br /></div>";
    echo "</br><p>Torna alla <a href='visualizzaPartite.php'>pagina precedente</a>.</p>";
}

function contaPartecipanti($id_partita) {
    $query0 = "SELECT COUNT(USER_ID) AS NUMERO FROM PARTECIPAZIONE WHERE ID_PARTITA = '$id_partita' AND PARTECIPA = 1";
    $result0 = mysql_query($query0);
    $row0 = mysql_fetch_array($result0);
    if ($row0) {
        return $row0["NUMERO"];
    } else {
        return -1;
    }
}

function getPartecipanti($id_partita) {
    $query = "SELECT u.ID, u.NAME, u.SURNAME, u.EMAIL FROM PARTECIPAZIONE p, TZ_MEMBERS u WHERE ID_PARTITA = $id_partita AND u.ID = p.USER_ID AND p.PARTECIPA = 1 ORDER BY u.NAME;";
    return mysql_query($query);
}

function getRifiutanti($id_partita) {
    $query = "SELECT u.NAME, u.SURNAME FROM PARTECIPAZIONE p, TZ_MEMBERS u WHERE ID_PARTITA = $id_partita AND u.ID = p.USER_ID AND p.PARTECIPA = -1 AND p.USER_ID NOT LIKE 'guest%';";
    return mysql_query($query);
}

function getIndecisi($id_partita) {
    $query = "SELECT u.NAME, u.SURNAME FROM PARTECIPAZIONE p, TZ_MEMBERS u WHERE ID_PARTITA = $id_partita AND u.ID = p.USER_ID AND p.PARTECIPA = 0 AND p.USER_ID NOT LIKE 'guest%';";
    return mysql_query($query);
}

function visualizzaPartecipanti($id_partita, $isAdmin) {

    //$query="SELECT u.NAME, u.SURNAME FROM PARTECIPAZIONE p, TZ_MEMBERS u WHERE ID_PARTITA = $id_partita AND u.ID = p.USER_ID AND p.PARTECIPA = 1 ORDER BY u.NAME";
    $result = getPartecipanti($id_partita);
    $row = mysql_fetch_array($result);
    if ($row) {
        echo "<ol>";
        do {
            $partecipante = $row["NAME"] . " " . $row["SURNAME"];
            $userId = $row["ID"];
            if ($isAdmin == 1) {
                echo "<li style='line-height:25px;'>$partecipante <a href='#' onclick='cancellaUserPartita($userId,$id_partita)'><img style='vertical-align:text-bottom;' src='img/Remove_16x16.png' alt='Cancella partecipante' title='Cancella partecipante' /></a></li>";
            } else {
                echo "<li>$partecipante</li>";
            }
        } while ($row = mysql_fetch_array($result));
        echo "</ol>";
    } else {
        echo "<div class='info'>Non ci sono ancora partecipanti!</div>";
    }
}

function visualizzaPartecipantiXML() {

    $query = "SELECT u.NAME, u.SURNAME FROM PARTECIPAZIONE p, TZ_MEMBERS u WHERE p.USER_ID = u.ID  AND p.PARTECIPA = 1 AND ID_PARTITA IN  (SELECT MAX(ID) FROM PARTITA WHERE STATO != 'C' AND ORA_INIZIO > CURRENT_TIMESTAMP);";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="utf-8" ?>
        <rsp stat="ok">';

    if ($row) {
        echo '<partecipanti>';
        do {
            echo '<partecipante name="';
            $partecipante = $row["NAME"] . " " . $row["SURNAME"];
            echo $partecipante;
            echo '"/>';
        } while ($row = mysql_fetch_array($result));
        echo '</partecipanti></rsp>';
    }
}

function visualizzaRifiutanti($id_partita) {

    //$query1="SELECT u.NAME, u.SURNAME FROM PARTECIPAZIONE p, TZ_MEMBERS u WHERE ID_PARTITA = $id_partita AND u.ID = p.USER_ID AND p.PARTECIPA = -1 AND p.USER_ID NOT LIKE 'guest%'";
    $result1 = getRifiutanti($id_partita);
    $row1 = mysql_fetch_array($result1);
    if ($row1) {
        echo "<ol>";
        do {
            $partecipante = $row1["NAME"] . " " . $row1["SURNAME"];
            echo "<li>$partecipante</li>";
        } while ($row1 = mysql_fetch_array($result1));
        echo "</ol>";
    } else {
        echo "<div class='info'>Non ci sono ancora rifiutanti!</div>";
    }
}

function visualizzaIndecisi($id_partita) {

    //$query2="SELECT u.NAME, u.SURNAME FROM PARTECIPAZIONE p, TZ_MEMBERS u WHERE ID_PARTITA = $id_partita AND u.ID = p.USER_ID AND p.PARTECIPA = 0 AND p.USER_ID NOT LIKE 'guest%'";
    $result2 = getIndecisi($id_partita);
    $row2 = mysql_fetch_array($result2);
    if ($row2) {
        echo "<ol>";
        do {
            $partecipante = $row2["NAME"] . " " . $row2["SURNAME"];
            echo "<li>$partecipante</li>";
        } while ($row2 = mysql_fetch_array($result2));
        echo "</ol>";
    }
}

function visualizzaPartecipantiPagelle($id_partita, $userId) {
    $result = getPartecipanti($id_partita);
    $row = mysql_fetch_array($result);
    if ($row) {
        echo "<br />";
        echo "<form id='voti_$id_partita' method='post' action='partite.php'>
		 		<input type='hidden' name='id_partita' value='$id_partita' />
		 		<input type='hidden' name='action' value='inviaVoti' />";
        echo "<table class='table2'>";
        echo "<thead><tr>";
        echo "<th scope='col'>Nome</th>";
        echo "<th scope='col'>Voto</th>";
        echo "<tbody>";
        do {
            $partecipante = $row["NAME"] . " " . $row["SURNAME"];
            $partecipanteId = $row["ID"];
            if ($partecipanteId != $userId) {
                echo "<tr>";
                echo "<td>$partecipante</td>";
                echo "<td><select id='voto_$partecipanteId' name='voto_$partecipanteId'>";
                echo "<option value=''>s.v.</option>";
                echo "<option value='3.0'>3</option>";
                echo "<option value='4.0'>4</option>";
                echo "<option value='5.0'>5</option>";
                echo "<option value='6.0' selected='selected'>6</option>";
                echo "<option value='7.0'>7</option>";
                echo "<option value='8.0'>8</option>";
                echo "<option value='9.0'>9</option>";
                echo "<option value='10.0'>10</option></select></td>";
                echo "</tr>";
                ;
            }
        } while ($row = mysql_fetch_array($result));
        echo "</tbody>";
        echo "</table>";
        echo '<p></p>';
        echo '<p><a onclick="inviaVoti(' . $id_partita . ');" class="awesome medium blue">Invia</a></p>';
        echo "</form>";
    } else {
        echo "<div class='info'>Non ci sono partecipanti!</div>";
    }
}

function printTeam($squadraA, $squadraB) {
    echo("<b>Squadra A - Maglia Chiara:</b><br><br><ol>");
    for ($i = 0; $i <= 4; $i++) {
        list($a, $n, $c, $b) = split(";", $squadraA[$i]);
        if ("" == $n) {
            echo("<li>Ospite<br>");
        } else {
            echo("<li> " . $n . " " . $c . " <br>");
        }
    }
    echo("</ol><br>");
    echo("<b>Squadra B - Maglia Scura:</b><br><br><ol>");
    for ($i = 0; $i <= 4; $i++) {
        list($a, $n, $c, $b) = split(";", $squadraB[$i]);
        if ("" == $n) {
            echo("<li>Ospite<br>");
        } else {
            echo("<li> " . $n . " " . $c . " <br>");
        }
    }
    echo "</ol>";
}

function creaSquadre($id_partita) {

    //Query che estrae gli id dei partecipanti
    $query = "SELECT USER_ID FROM PARTECIPAZIONE WHERE ID_PARTITA = $id_partita AND PARTECIPA = 1";
    $rs = mysql_query($query);
    //mettere a posto il numrow
    //$numRow = mysql_num_rows($rs);
    for ($i = 0; $i <= 9; $i++) {
        if ($row = mysql_fetch_array($rs)) {
            $idUser[$i] = $row["USER_ID"];
        }
    }



    for ($i = 0; $i <= 9; $i++) {
        //Query che calcola la media voto per tutti i giocatori -- aggiungi tu nella where la condizione USER_ID = quello che hai tirato fuori prima, in modo che lo faccia per un utente solo
        $query = "SELECT U.NAME AS NOME, U.SURNAME AS COGNOME, SUM(SOMMAVOTI)/SUM(NUMVOTANTI) AS MEDIA_VOTI, COUNT(P.PARTECIPA) AS NUM_PARTITE FROM TZ_MEMBERS U, PARTECIPAZIONE P WHERE U.ID = P.USER_ID AND U.ID = '$idUser[$i]' AND P.PARTECIPA = 1 AND P.SOMMAVOTI > 0 GROUP BY U.NAME, U.SURNAME ORDER BY MEDIA_VOTI DESC";
        $rs = mysql_query($query);
        if ($row = mysql_fetch_array($rs)) {
            $voto_ = $row["MEDIA_VOTI"];
            $cognome_ = $row["COGNOME"];
            $nome_ = $row["NOME"];
            $numPartite_ = $row["NUM_PARTITE"];
            $totale[$i] = $voto_ . ";" . $nome_ . ";" . $cognome_ . ";" . $numPartite_;
        }
    }

    //ordina array dal pi� piccolo al pi� grande
    sort($totale);


    /*     * *******Metodo di creazione squadra, NUMERO 1************ */
    $squadraA1 = array($totale[0], $totale[2], $totale[4], $totale[6], $totale[8]);
    $squadraB1 = array($totale[1], $totale[3], $totale[5], $totale[7], $totale[9]);
    //calcolo per squadra A1
    for ($i = 0; $i <= 4; $i++) {
        list($voto[$i], $nome[$i], $cognome[$i], $numPartite[$i]) = split(";", $squadraA1[$i]);
    }
    $totPartite = 0;
    for ($i = 0; $i <= 4; $i++) {
        $totPartite = $totPartite + $numPartite[$i];
    }
    $valoreSquadraA1 = 0;
    for ($i = 0; $i <= 4; $i++) {
        $valoreSquadraA1 = $valoreSquadraA1 + ($voto[$i] * ($numPartite[$i] / $totPartite));
    }

    //calcolo per squadra B1
    for ($i = 0; $i <= 4; $i++) {
        list($voto[$i], $nome[$i], $cognome[$i], $numPartite[$i]) = split(";", $squadraB1[$i]);
    }
    $totPartite = 0;
    for ($i = 0; $i <= 4; $i++) {
        $totPartite = $totPartite + $numPartite[$i];
    }
    $valoreSquadraB1 = 0;
    for ($i = 0; $i <= 4; $i++) {
        $valoreSquadraB1 = $valoreSquadraB1 + ($voto[$i] * ($numPartite[$i] / $totPartite));
    }

    $metodo1 = $valoreSquadraA1 - $valoreSquadraB1;



    /*     * *******Metodo di creazione squadra, NUMERO 2************ */
    $squadraA2 = array($totale[0], $totale[2], $totale[4], $totale[7], $totale[9]);
    $squadraB2 = array($totale[1], $totale[3], $totale[5], $totale[6], $totale[8]);
    //calcolo per squadra A2
    for ($i = 0; $i <= 4; $i++) {
        list($voto[$i], $nome[$i], $cognome[$i], $numPartite[$i]) = split(";", $squadraA2[$i]);
    }
    $totPartite = 0;
    for ($i = 0; $i <= 4; $i++) {
        $totPartite = $totPartite + $numPartite[$i];
    }
    $valoreSquadraA2 = 0;
    for ($i = 0; $i <= 4; $i++) {
        $valoreSquadraA2 = $valoreSquadraA2 + ($voto[$i] * ($numPartite[$i] / $totPartite));
    }

    //calcolo per squadra B2
    for ($i = 0; $i <= 4; $i++) {
        list($voto[$i], $nome[$i], $cognome[$i], $numPartite[$i]) = split(";", $squadraB2[$i], 4);
    }
    $totPartite = 0;
    for ($i = 0; $i <= 4; $i++) {
        $totPartite = $totPartite + $numPartite[$i];
    }
    $valoreSquadraB2 = 0;
    for ($i = 0; $i <= 4; $i++) {
        $valoreSquadraB2 = $valoreSquadraB2 + ($voto[$i] * ($numPartite[$i] / $totPartite));
    }

    $metodo2 = $valoreSquadraA2 - $valoreSquadraB2;


    /*     * *******Metodo di creazione squadra, NUMERO 3************ */
    $squadraA3 = array($totale[0], $totale[2], $totale[5], $totale[7], $totale[9]);
    $squadraB3 = array($totale[1], $totale[3], $totale[4], $totale[6], $totale[8]);
    //calcolo per squadra A3
    for ($i = 0; $i <= 4; $i++) {
        list($voto[$i], $nome[$i], $cognome[$i], $numPartite[$i]) = split(";", $squadraA3[$i]);
    }
    $totPartite = 0;
    for ($i = 0; $i <= 4; $i++) {
        $totPartite = $totPartite + $numPartite[$i];
    }
    $valoreSquadraA2 = 0;
    for ($i = 0; $i <= 4; $i++) {
        $valoreSquadraA3 = $valoreSquadraA3 + ($voto[$i] * ($numPartite[$i] / $totPartite));
    }

    //calcolo per squadra B3
    for ($i = 0; $i <= 4; $i++) {
        list($voto[$i], $nome[$i], $cognome[$i], $numPartite[$i]) = split(";", $squadraB3[$i]);
    }
    $totPartite = 0;
    for ($i = 0; $i <= 4; $i++) {
        $totPartite = $totPartite + $numPartite[$i];
    }
    $valoreSquadraB3 = 0;
    for ($i = 0; $i <= 4; $i++) {
        $valoreSquadraB3 = $valoreSquadraB3 + ($voto[$i] * ($numPartite[$i] / $totPartite));
    }

    $metodo3 = $valoreSquadraA3 - $valoreSquadraB3;

    //stampa delle squadre...
    if ($metodo1 < $metodo2) {
        if ($metodo1 < $metodo3) {
            printTeam($squadraA1, $squadraB1);
        } else {
            printTeam($squadraA3, $squadraB3);
        }
    } else {
        if ($metodo2 < $metodo3) {
            printTeam($squadraA2, $squadraB2);
        } else {
            printTeam($squadraA3, $squadraB3);
        }
    }
}

function visualizzaPartiteAccettate($user) {
    $query = "SELECT p.ID AS ID_PARTITA, DATE_FORMAT(p.ORA_INIZIO,'%H.%i') AS ORA, DATE_FORMAT(p.ORA_INIZIO,'%d-%m-%Y') AS DATA, p.NOTE NOTE, p.ID_ORGANIZZATORE AS ID_ORGANIZZATORE, p.STATO AS STATO, c.NOME AS CAMPO, c.COPERTO AS COPERTO, c.INDIRIZZO AS INDIRIZZO, u.NAME AS NOME, u.SURNAME AS COGNOME FROM PARTITA p, CAMPO c, TZ_MEMBERS u, PARTECIPAZIONE pa WHERE p.ID_ORGANIZZATORE = u.ID and p.ID_CAMPO = c.ID and pa.ID_PARTITA = p.ID and pa.USER_ID = '$user' and pa.PARTECIPA = 1 and p.ORA_INIZIO > now() ORDER BY p.ORA_INIZIO ASC;";
    $rs = mysql_query($query);

    $query_org = "SELECT * FROM PARTITA WHERE ID_ORGANIZZATORE = '$user' AND ORA_INIZIO > now()";
    $rs_org = mysql_query($query_org);
    $row = mysql_fetch_array($rs);

    if ($row) {
        echo "<br />";
        echo "<table class='table2'>";
        echo "<thead><tr>";
        echo "<th scope='col'>Data</th>";
        echo "<th scope='col'>Ora</th>";
        echo "<th scope='col'>Campo</th>";
        echo "<th scope='col'>Coperto</th>";
        echo "<th scope='col'>Indirizzo</th>";
        echo "<th scope='col'>Organizzatore</th>";
        echo "<th scope='col'>Note</th>";
        echo "<th scope='col'>Partecipanti</th>";
        echo "</tr></thead>";

        do {
            $partita = $row["ID_PARTITA"];
            $data = $row["DATA"];
            $ora = $row["ORA"];
            $campo = $row["CAMPO"];
            $coperto = $row["COPERTO"];
            $indirizzo = $row["INDIRIZZO"];
            $organizzatore = $row["NOME"] . " " . $row["COGNOME"];
            $note = $row["NOTE"];
            $id_organizzatore = $row['ID_ORGANIZZATORE'];
            $stato = $row['STATO'];

            echo "<tbody>";
            echo "<tr>";
            echo "<td>$data</td>";
            echo "<td>$ora</td>";
            echo "<td>$campo</td>";
            if ($coperto == 0) {
                echo "<td>NO</td>";
            } else {
                echo "<td>SI</td>";
            }
            echo "<td>$indirizzo</td>";
            echo "<td>$organizzatore</td>";
            echo "<td width='15%' style='white-space:normal;'>$note</td>";
            echo "<td><a class='visualizzaPartecipanti' title='Chi partecipa a questa partita' href='./partite.php?action=partecipanti&id_partita=$partita'>Visualizza</a></center></td>";

            echo "</tr>";
        } while ($row = mysql_fetch_array($rs));
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<div class='info'>Non sei iscritto a nessuna partita!</div>";
    }
}

function visualizzaProssimePartite($user) {

    $query = "SELECT p.ID AS ID_PARTITA, DATE_FORMAT(p.ORA_INIZIO,'%H.%i') AS ORA, DATE_FORMAT(p.ORA_INIZIO,'%d-%m-%Y') AS DATA, p.NOTE NOTE, p.ID_ORGANIZZATORE AS ID_ORGANIZZATORE, c.NOME AS CAMPO, c.COPERTO AS COPERTO, c.INDIRIZZO AS INDIRIZZO, u.NAME AS NOME, u.SURNAME AS COGNOME FROM PARTITA p, CAMPO c, TZ_MEMBERS u, PARTECIPAZIONE pa WHERE p.ID_ORGANIZZATORE = u.ID and p.ID_CAMPO = c.ID and pa.ID_PARTITA = p.ID and pa.USER_ID = '$user' and pa.PARTECIPA = 0 and p.ORA_INIZIO > now() ORDER BY p.ORA_INIZIO ASC;";
    $rs = mysql_query($query);

    if ($row = mysql_fetch_array($rs)) {
        echo "<br />";
        echo "<table class='table2'>";
        echo "<thead><tr>";
        echo "<th scope='col'>Data</th>";
        echo "<th scope='col'>Ora</th>";
        echo "<th scope='col'>Campo</th>";
        echo "<th scope='col'>Coperto</th>";
        echo "<th scope='col'>Indirizzo</th>";
        echo "<th scope='col'>Organizzatore</th>";
        echo "<th scope='col'>Note</th>";
        echo "<th scope='col'>Partecipanti</th>";
        echo "<th scope='col'>Partecipa</th>";
        echo "</tr></thead>";

        do {
            $partita = $row["ID_PARTITA"];
            $data = $row["DATA"];
            $ora = $row["ORA"];
            $campo = $row["CAMPO"];
            $coperto = $row["COPERTO"];
            $indirizzo = $row["INDIRIZZO"];
            $organizzatore = $row["NOME"] . " " . $row["COGNOME"];
            $note = $row["NOTE"];

            echo "<tbody>";
            echo "<tr>";
            echo "<td>$data</td>";
            echo "<td>$ora</td>";
            echo "<td>$campo</td>";
            if ($coperto == 0) {
                echo "<td>NO</td>";
            } else {
                echo "<td>SI</td>";
            }
            echo "<td>$indirizzo</td>";
            echo "<td>$organizzatore</td>";
            echo "<td width='15%' style='white-space:normal;'>$note</td>";
            echo "<td><a class='visualizzaPartecipanti' title='Chi partecipa a questa partita' href='./partite.php?action=partecipanti&id_partita=$partita'>Visualizza</a></td>";
            echo "<td>";
            echo "<form id='accetta_form_$partita' method='post' action='partite.php' style='float:left;'>
	 		<input type='hidden' name='user_id' value='$user' />
	 		<input type='hidden' name='id_partita' value='$partita' />
	 		<input type='hidden' name='action' value='accetta' />
	 		<img class='accetta_img' src='img/Check_24x24.png' alt='Accetta' title='Accetta la partita' onclick='accettaPartita($partita);'>
	 		</form>";
            echo "<form id='rifiuta_form_$partita' method='post' action='partite.php' style='float:right;'>
	 		<input type='hidden' name='user_id' value='$user' />
	 		<input type='hidden' name='id_partita' value='$partita' />
	 		<input type='hidden' name='action' value='rifiuta' />
	 		<img class='rifiuta_img' src='img/Delete_24x24.png' alt='Rifiuta' title='Rifiuta la partita' onclick='rifiutaPartita($partita);'>
	 		</form>";
            echo "</td>";
            echo "</tr>";
        } while ($row = mysql_fetch_array($rs));

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<div class='info'>Non &egrave; presente nessuna partita in programma o non ci sono pi&ugrave; posti disponibili nelle partite alle quali eri stato invitato!</div>";
    }
}

function visualizzaPartiteAdmin($user) {

    $query = "SELECT DISTINCT(p.ID) AS ID_PARTITA, DATE_FORMAT(p.ORA_INIZIO,'%H.%i') AS ORA, DATE_FORMAT(p.ORA_INIZIO,'%d-%m-%Y') AS DATA, p.NOTE NOTE, p.ID_ORGANIZZATORE AS ID_ORGANIZZATORE, p.STATO AS STATO, c.NOME AS CAMPO, c.COPERTO AS COPERTO, c.INDIRIZZO AS INDIRIZZO, u.NAME AS NOME, u.SURNAME AS COGNOME FROM PARTITA p, CAMPO c, TZ_MEMBERS u, PARTECIPAZIONE pa WHERE p.ID_CAMPO = c.ID and p.ID_ORGANIZZATORE = u.ID ORDER BY p.ORA_INIZIO DESC;";
    $rs = mysql_query($query);

    if ($row = mysql_fetch_array($rs)) {
        echo "<br />";
        echo "<table class='table2'>";
        echo "<thead><tr>";
        echo "<th scope='col'>Data</th>";
        echo "<th scope='col'>Ora</th>";
        echo "<th scope='col'>Campo</th>";
        echo "<th scope='col'>Coperto</th>";
        echo "<th scope='col'>Indirizzo</th>";
        echo "<th scope='col'>Organizzatore</th>";
        echo "<th scope='col'>Note</th>";
        echo "<th scope='col'>Partecipanti</th>";
        //echo "<th scope='col'>Stato</th>";
        echo "<th scope='col'>Azioni <img alt='Help' src='img/Help_16x16.png' title=\"Puoi annullare una partita che hai organizzato. Chi si era iscritto verr&agrave; avvisato con una e-mail.\"></th>";
        echo "</tr></thead>";

        do {
            $partita = $row["ID_PARTITA"];
            $data = $row["DATA"];
            $ora = $row["ORA"];
            $campo = $row["CAMPO"];
            $coperto = $row["COPERTO"];
            $indirizzo = $row["INDIRIZZO"];
            $organizzatore = $row["NOME"] . " " . $row["COGNOME"];
            $note = $row["NOTE"];
            $stato = $row["STATO"];

            echo "<tbody>";
            echo "<tr>";
            echo "<td>$data</td>";
            echo "<td>$ora</td>";
            echo "<td>$campo</td>";
            if ($coperto == 0) {
                echo "<td>NO</td>";
            } else {
                echo "<td>SI</td>";
            }
            echo "<td>$indirizzo</td>";
            echo "<td>$organizzatore</td>";
            echo "<td width='15%' style='white-space:normal;'>$note</td>";
            echo "<td><a class='visualizzaPartecipanti' title='Chi partecipa a questa partita' href='./partite.php?action=partecipanti&id_partita=$partita'>Visualizza</a></td>";
            //echo "<td>$stato</td>";

            echo "<td>";
            if ($stato != 'C') {
                echo "<table><tr>";
                echo "<td style='padding: 0; margin: 0;'><form id='invita_form_$partita' method='post' action='partite.php'>
                                <input type='hidden' name='id_partita' value='$partita' />
                                <input type='hidden' name='action' value='invita' />
                                <img class='invita_img' src='img/Forward_24x24.png' alt='Invita' title='Invita ulteriori utenti' onclick='invitaUtenti($partita);'>
                                </form></td>";
//                                echo "<td style='padding: 0; margin: 0;'><form id='ospiti_form_$partita' method='post' action='partite.php'>
//                                <input type='hidden' name='id_partita' value='$partita' />
//                                <input type='hidden' name='action' value='ospiti' />
//                                <img class='ospiti_img' src='img/User_24x24.png' alt='Aggiungi ospiti' title='Aggiungi ospiti' onclick='aggiungiOspiti($partita);'>
//                                </form></td>";
                echo "<td style='padding: 0; margin: 0;'><form id='chiudi_form_$partita' method='post' action='partite.php' >
                                <input type='hidden' name='id_partita' value='$partita' />
                                <input type='hidden' name='action' value='chiudi' />
                                <img class='invita_img' src='img/Check_24x24.png' alt='Chiudi' title='Chiudi e conferma la partita' onclick='chiudiPartita($partita);'>
                                </form></td>";
                echo "<td style='padding: 0; margin: 0;'><form id='cancella_form_$partita' method='post' action='partite.php' >
                                <input type='hidden' name='id_partita' value='$partita' />
                                <input type='hidden' name='action' value='cancella' />
                                <img class='cancella_img' src='img/Cancel_24x24.png' alt='Annulla' title='Annulla la partita' onclick='cancellaPartita($partita);'>
                                </form></td>";
                echo "</table></tr>";
            } elseif ($stato == 'C')  {
                echo "CHIUSA!";
            } elseif ($stato == 'A')  {
                echo "ANNULLATA!";
            } else {
                echo "";
            }
            echo "</td>";

            echo "</tr>";
        } while ($row = mysql_fetch_array($rs));

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<div class='info'>Non &egrave; presente nessuna partita in programma o non ci sono pi&ugrave; posti disponibili nelle partite alle quali eri stato invitato!</div>";
    }
}

function visualizzaPartiteEsistenti($user) {

    $query = "SELECT p.ID AS ID_PARTITA, DATE_FORMAT(p.ORA_INIZIO,'%H.%i') AS ORA, DATE_FORMAT(p.ORA_INIZIO,'%d-%m-%Y') AS DATA, p.NOTE NOTE,c.NOME AS CAMPO, c.COPERTO AS COPERTO, c.INDIRIZZO AS INDIRIZZO, u.NAME AS NOME, u.SURNAME AS COGNOME FROM PARTITA p, CAMPO c, TZ_MEMBERS u, PARTECIPAZIONE pa WHERE p.ID_ORGANIZZATORE = u.ID and p.ID_CAMPO = c.ID and pa.ID_PARTITA = p.ID and pa.USER_ID = '$user' and p.ORA_INIZIO > now() ORDER BY p.ORA_INIZIO ASC;";
    $rs = mysql_query($query);

    if ($row = mysql_fetch_array($rs)) {
        echo "<br />";
        echo "<table class='table2'>";
        echo "<thead><tr>";
        echo "<th scope='col'>Data</th>";
        echo "<th scope='col'>Ora</th>";
        echo "<th scope='col'>Campo</th>";
        echo "<th scope='col'>Coperto</th>";
        echo "<th scope='col'>Organizzatore</th>";
        echo "</tr></thead>";

        do {
            $partita = $row["ID_PARTITA"];
            $data = $row["DATA"];
            $ora = $row["ORA"];
            $campo = $row["CAMPO"];
            $coperto = $row["COPERTO"];
            $organizzatore = $row["NOME"] . " " . $row["COGNOME"];

            echo "<tbody>";
            echo "<tr>";
            echo "<td>$data</td>";
            echo "<td>$ora</td>";
            echo "<td>$campo</td>";
            if ($coperto == 0) {
                echo "<td>NO</td>";
            } else {
                echo "<td>SI</td>";
            }
            echo "<td>$organizzatore</td>";
            echo "</tr>";
        } while ($row = mysql_fetch_array($rs));

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<div class='info'>Non &egrave; presente nessuna partita in programma!</div>";
    }
}

function visualizzaPartitePagelle($user) {
    $query = "SELECT p.ID AS ID_PARTITA, DATE_FORMAT(p.ORA_INIZIO,'%H.%i') AS ORA, DATE_FORMAT(p.ORA_INIZIO,'%d-%m-%Y') AS DATA, c.NOME AS CAMPO, pa.VOTATO AS VOTATO FROM PARTITA p, CAMPO c, PARTECIPAZIONE pa WHERE p.ID_CAMPO = c.ID and pa.ID_PARTITA = p.ID and pa.USER_ID = '$user' and pa.PARTECIPA = 1 and p.ORA_INIZIO < TIMESTAMPADD(HOUR,-2,CURRENT_TIMESTAMP) ORDER BY p.ORA_INIZIO DESC LIMIT 10;";
    $rs = mysql_query($query);

    if ($row = mysql_fetch_array($rs)) {
        echo "<br />";
        echo "<table class='table2'>";
        echo "<thead><tr>";
        echo "<th scope='col'>Data</th>";
        echo "<th scope='col'>Ora</th>";
        echo "<th scope='col'>Campo</th>";
        echo "<th scope='col'>Voti <img alt='Help Annulla' src='img/Help_16x16.png' title=\"Puoi votare una partita o visualizzare i voti\"></th>";

        echo "</tr></thead>";

        do {
            $partita = $row["ID_PARTITA"];
            $hasVotato = $row["VOTATO"];
            $data = $row["DATA"];
            $ora = $row["ORA"];
            $campo = $row["CAMPO"];

            echo "<tbody>";
            echo "<tr>";
            echo "<td>$data</td>";
            echo "<td>$ora</td>";
            echo "<td>$campo</td>";
            echo "<td>";
            if ($hasVotato == 0) {
                echo "<form id='vota_partita_$partita' method='post' action='partite.php'>
		 		<input type='hidden' name='id_partita' value='$partita' />
		 		<input type='hidden' name='dataPartita' value='$data' />
		 		<input type='hidden' name='action' value='vota' />
	 			<img class='cancella_img' src='img/Favorites_24x24.png' alt='Vota' title='Vota i partecipanti' onclick='votaPartita($partita);'>
	 			</form>";
            } elseif ($hasVotato == 1) {
                echo "<form id='visualizza_voti_$partita' method='post' action='partite.php'>
	 			<input type='hidden' name='id_partita' value='$partita' />
	 			<input type='hidden' name='dataPartita' value='$data' />
	 			<input type='hidden' name='action' value='visualizzaVoti' />
	 			<img class='cancella_img' src='img/Preview_24x24.png' alt='Visualizza' title='Visualizza i voti' onclick='visualizzaVoti($partita);'>
	 			</form>";
            }

            echo "</td>";
            echo "</tr>";
        } while ($row = mysql_fetch_array($rs));
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<div class='info'>Non ci sono partite a cui hai partecipato!</div>";
    }
}

function nuovaPartita($user, $ora_inizio, $campo, $note, $invitaTutti, $numGiocatori) {

    $query1 = '';
    $query1 = "INSERT INTO PARTITA (ID_CAMPO, ORA_INIZIO, ID_ORGANIZZATORE, NOTE, NUM_GIOCATORI) VALUES ($campo, '$ora_inizio','$user','$note', '$numGiocatori')";
    if (mysql_query($query1)) {
        echo "<div class='success'>La partita &egrave; stata creata!";

        $query2 = '';
        $query2 = "SELECT MAX(ID) AS MASSIMO FROM PARTITA";
        $rs2 = mysql_query($query2);
        if ($row2 = mysql_fetch_array($rs2)) {
            $idPartita = $row2["MASSIMO"];
        }
        $query3 = '';
        $query3 = "INSERT INTO PARTECIPAZIONE (USER_ID, ID_PARTITA, PARTECIPA) VALUES ('$user', $idPartita, 1)";
        /* echo "</br><p>Torna alla <a href='nuovaPartita.php'>pagina principale</a>.</p>"; */
        if (mysql_query($query3))
            ;
        if ($invitaTutti == '1') {
            invitaTutti($user, $idPartita, true);
        }
    } else {
        echo "<div class='error'>Si &egrave, verificato un errore. La partita non &egrave; stata creata.</div>";
        echo "</br><p>Torna alla <a href='visualizzaPartite.php'>pagina precedente</a>.</p>";
    }
}

function invitaTutti($idUser, $idPartita, $isStrict) {

    global $logger;
    $row1 = getUserById($idUser);
    $nomeOrg = $row1["NAME"] . " " . $row1["SURNAME"];
    $emailOrg = $row1["EMAIL"];

    $rsPartita = getPartitaById($idPartita);
    $rowPartita = mysql_fetch_array($rsPartita);
    $data = $rowPartita["DATA"];
    $ora = $rowPartita["ORA"];
    $note = $rowPartita["NOTE"];
    $idCampo = $rowPartita["ID_CAMPO"];

    $rsCampo = getCampoById($idCampo);
    $rowCampo = mysql_fetch_array($rsCampo);
    $nomeCampo = $rowCampo["NOME"];
    $indCampo = $rowCampo["INDIRIZZO"];

    if ($isStrict) {
        $query = "SELECT ID, NAME, SURNAME, EMAIL, ACCEPT FROM TZ_MEMBERS WHERE ID <> '$idUser' AND ID NOT LIKE 'guest%' AND (LEVEL = 1 OR LEVEL = 9) ORDER BY NAME;";
    } else {
        $query = "SELECT ID, NAME, SURNAME, EMAIL, ACCEPT FROM TZ_MEMBERS WHERE ID <> '$idUser' AND ID NOT LIKE 'guest%' AND LEVEL < 1 ORDER BY NAME;";
    }

    $rsUtenti = mysql_query($query);
    $c = 0;
    $p = mysql_num_rows($rsUtenti);
    while ($row = mysql_fetch_array($rsUtenti)) {

        $email = $row["EMAIL"];
        $idPartecipante = $row["ID"];
        $nome = $row["NAME"];
        $accept = $row["ACCEPT"];
        if ($accept == 1) {
            $message = '
				<html>
				<head>
				  <title>Nuova Partita</title>
				</head>
				<body style="font-family: verdana, sans-serif; color: #272727;">
				  <p>Ciao ' . $nome . ',<br />' . $nomeOrg . ' sta organizzando una <strong>nuova partita</strong>.</p>
				  <p>In base alle tue preferenze, <strong>sei stato iscritto in automatico</strong>.</p>
				  <p>
				  Data: <strong>' . $data . '</strong><br />
				  Ora: <strong>' . $ora . '</strong><br />
				  Dove: ' . $nomeCampo . ' - ' . $indCampo . '<br />
				  Note: ' . $note . '
				  </p>
				  <p>Vai sul sito (<a href="http://mycalcetto.altervista.org" target="_blank">mycalcetto.altervista.org</a>) per modificare i tuoi dati.</p>
				  <p>Saluti,<br/>il team di MyCalcetto</p>
				</body>
				</html>';
        } else {
            $message = '
				<html>
				<head>
				  <title>Nuova Partita</title>
				</head>
				<body style="font-family: verdana, sans-serif; color: #272727;">
				  <p>Ciao ' . $nome . ',<br />' . $nomeOrg . ' sta organizzando una <strong>nuova partita</strong>.</p>
				  <p>Vieni a giocare? <strong>Iscriviti</strong> sul sito (<a href="http://mycalcetto.altervista.org" target="_blank">mycalcetto.altervista.org</a>).</p>
				  <p>
				  Data: <strong>' . $data . '</strong><br />
				  Ora: <strong>' . $ora . '</strong><br />
				  Dove: ' . $nomeCampo . ' - ' . $indCampo . '<br />
				  Note: ' . $note . '
				  </p>
				  <p>Saluti,<br/>il team di MyCalcetto</p>
				</body>
				</html>';
        }
        if (send_mail('MyCalcetto <mycalcetto@altervista.org>', $email, 'Nuova Partita', $message, $emailOrg)) {
            $query0 = "";
            $rs0 = null;
            if ($accept == 1) {
                $query0 = "INSERT INTO PARTECIPAZIONE (USER_ID, ID_PARTITA, PARTECIPA) VALUES ('$idPartecipante', $idPartita, 1)";
            } else {
                $query0 = "INSERT INTO PARTECIPAZIONE (USER_ID, ID_PARTITA, PARTECIPA) VALUES ('$idPartecipante', $idPartita, 0)";
            }
            $rs0 = mysql_query($query0);
            $c = $c + 1;
            $logger->logThis($logger->get_formatted_date() . '[INFO] Invito inviato a: ' . $email);
            sleep(1);
        } else {
            $logger->logThis($logger->get_formatted_date() . '[ERROR] Impossibile inviare invito a: ' . $email);
        }
    }

    echo "<br />Sono state invitate $c persone, su $p</div>";
    echo "</br><p>Torna alla <a href='visualizzaPartite.php'>pagina principale</a>.</p>";
}

function getPartitaById($idPartita) {

    $res = mysql_query("SELECT ID, ID_CAMPO, DATE_FORMAT(ORA_INIZIO,'%H.%i') AS ORA, DATE_FORMAT(ORA_INIZIO,'%d-%m-%Y') AS DATA, ID_ORGANIZZATORE, NOTE from PARTITA where ID='" . $idPartita . "'") or die(mysql_error());
    return $res;
}

function visualizzaPagelle($idPartita) {
    $query = "SELECT u.ID AS USER_ID, u.NAME as NOME, u.SURNAME AS COGNOME, p.SOMMAVOTI AS SOMMAVOTI, p.NUMVOTANTI AS NUMVOTANTI FROM PARTECIPAZIONE p, TZ_MEMBERS u WHERE p.ID_PARTITA = $idPartita AND u.ID = p.USER_ID AND p.PARTECIPA = 1 AND u.ID NOT LIKE 'guest%' ORDER BY NOME ASC;";
    $rs = mysql_query($query);

    if ($row = mysql_fetch_array($rs)) {
        echo "<br />";
        echo "<table class='table2'>";
        echo "<thead><tr>";
        echo "<th scope='col'>Nome</th>";
        echo "<th scope='col'>Media Voti</th>";
        echo "</tr></thead>";

        do {
            $partecipante = $row["NOME"] . " " . $row["COGNOME"];
            $idPartecipante = $row["USER_ID"];
            $sommavoti = $row["SOMMAVOTI"];
            $numvotanti = $row["NUMVOTANTI"];
            if ($sommavoti == 0 | $numvotanti == 0) {
                $media = "s.v.";
            } else {
                $media = $sommavoti / $numvotanti;
                $media = number_format($media, 2, '.', ',');
            }
            $style = '';
            if ($media == "s.v.") {
                $style = '';
            } else if ($media >= 6) {
                $style = ' style="color: #00FF00;"';
            } else {
                $style = ' style="color: #FF0000;"';
            }

            echo "<tbody>";
            echo "<tr>";
            echo "<td style='text-align: left;'>$partecipante</td>";
            echo "<td$style>$media</td>";
            echo "</tr>";
        } while ($row = mysql_fetch_array($rs));
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<div class='info'>Non sono presenti voti!</div>";
    }
}

function aggiungiVoto($voto, $idPartita, $idPartecipante) {
    $query0 = "UPDATE PARTECIPAZIONE SET SOMMAVOTI = SOMMAVOTI + $voto, NUMVOTANTI = NUMVOTANTI+1 WHERE ID_PARTITA = $idPartita AND USER_ID = '$idPartecipante';";
    $result0 = mysql_query($query0);
}

function aggiornaVotazione($userId, $idPartita) {
    $query1 = "UPDATE PARTECIPAZIONE SET VOTATO = 1 WHERE USER_ID = '$userId' AND ID_PARTITA = $idPartita";
    return mysql_query($query1);
}

function countPartiteGiocate($userId) {
    $query0 = "SELECT PA.ID_PARTITA FROM PARTECIPAZIONE PA, PARTITA P WHERE PA.ID_PARTITA=P.ID AND P.ORA_INIZIO<TIMESTAMPADD(HOUR,-2,CURRENT_TIMESTAMP) AND PA.PARTECIPA=1 AND PA.USER_ID = '$userId' ;";
    $result0 = mysql_query($query0);
    return mysql_num_rows($result0);
}

function countPartiteProssime($userId) {
    $query0 = "SELECT PA.ID_PARTITA FROM PARTECIPAZIONE PA, PARTITA P WHERE PA.ID_PARTITA=P.ID AND P.ORA_INIZIO>CURRENT_TIMESTAMP AND PA.PARTECIPA=1 AND PA.USER_ID = '$userId' ;";
    $result0 = mysql_query($query0);
    return mysql_num_rows($result0);
}

function getTotaleVoti($userId) {
    $query0 = "SELECT SUM(SOMMAVOTI) AS TOTALEVOTI FROM PARTECIPAZIONE WHERE PARTECIPA=1 AND SOMMAVOTI <> 0 AND USER_ID='$userId' ;";
    $result0 = mysql_query($query0);
    $row = mysql_fetch_array($result0);
    $totaleVoti = $row['TOTALEVOTI'];
    return $totaleVoti;
}

function countPartiteConVoti($userId) {
    $query0 = "SELECT PA.ID_PARTITA FROM PARTECIPAZIONE PA WHERE PARTECIPA=1 AND SOMMAVOTI<>0 AND PA.USER_ID = '$userId' ;";
    $result0 = mysql_query($query0);
    return mysql_num_rows($result0);
}

function getVotiArray($userId) {
    $query0 = "SELECT SOMMAVOTI/NUMVOTANTI AS VOTO FROM PARTECIPAZIONE WHERE PARTECIPA=1 AND SOMMAVOTI <> 0 AND USER_ID='$userId';";
    $result0 = mysql_query($query0);
    $arrayVoti = '';
    while ($row = mysql_fetch_array($result0)) {
        $arrayVoti = $arrayVoti . "" . $row['VOTO'] . ",";
    }
    return substr($arrayVoti, 0, -1);
}

function getMediaVotiTotale($userId) {
    $query = "SELECT SUM(SOMMAVOTI)/SUM(NUMVOTANTI) AS MEDIA FROM PARTECIPAZIONE WHERE USER_ID = '$userId' AND PARTECIPA = 1 GROUP BY USER_ID";
    $result = mysql_query($query);
    $row1 = mysql_fetch_array($result);
    return $row1["MEDIA"];
}

function visualizzaMediaVotiUsers() {
    $query = "SELECT ID AS ID_USER, NAME AS NOME, SURNAME AS COGNOME, SUM(SOMMAVOTI)/SUM(NUMVOTANTI) AS MEDIA, COUNT(ID_PARTITA) AS NUM_PARTITE FROM PARTECIPAZIONE, TZ_MEMBERS WHERE ID=USER_ID AND PARTECIPA = 1 AND SOMMAVOTI<>0 AND NAME<>'Ospite' GROUP BY ID ORDER BY NUM_PARTITE DESC;";
    $rs = mysql_query($query);

    if ($row = mysql_fetch_array($rs)) {
        echo "<br />";
        echo "<table class='table2'>";
        echo "<thead><tr>";
        echo "<th scope='col'></th>";
        echo "<th scope='col'>Nome</th>";
        echo "<th scope='col'>Media voti</th>";
        echo "<th scope='col'>Partite giocate</th>";
        echo "</tr></thead>";

        do {
            $userId = $row["ID_USER"];
            $partecipante = $row["NOME"] . " " . $row["COGNOME"];
            $voto = $row["MEDIA"];
            $numPartite = $row["NUM_PARTITE"];
            $voto = round($voto, 2);
            $style = '';
            if ($voto == "s.v.") {
                $style = '';
            } else if ($voto >= 6) {
                $style = ' style="color: #00FF00;"';
            } else {
                $style = ' style="color: #FF0000;"';
            }

            echo "<tbody>";
            echo "<tr>";
            echo "<td style='text-align: left;'><input type='checkbox' value='$userId' /></td>";
            echo "<td style='text-align: left;'><a class='statistiche' href='./partite.php?action=statistiche&id_user=$userId'>$partecipante</a></td>";
            echo "<td$style>$voto</td>";
            echo "<td>$numPartite</td>";
            echo "</tr>";
        } while ($row = mysql_fetch_array($rs));
        echo "</tbody>";
        echo "</table>";
        echo "<p><a onclick='confronta();' class='awesome medium blue' id='formButton'>Confronta</a></p>";
    } else {
        echo "<div class='info'>Non sono presenti voti!</div>";
    }
}

function visualizzaStatistiche($userId) {
    $numPartite = countPartiteGiocate($userId);
    $numPartiteProssime = countPartiteProssime($userId);
    $numVoti = countPartiteConVoti($userId);
    $mediaVoti = round(getMediaVotiTotale($userId), 2);

    if ($numVoti > 0) {
        $totaleVoti = getTotaleVoti($userId);
        $arrayVoti = getVotiArray($userId);
    } else {
        $mediaVoti = 'n.d.';
    }
    echo"
    <table class='table2'>
    <tbody>
    <tr>
        <th scope='row'>Partite Giocate</th>
        <td>$numPartite</td>
    </tr>
    <tr>
        <th scope='row'>Media voti</th>
        <td>$mediaVoti</td>
    </tr>
    </tbody>
    </table>
    <p></p>";
    if (!empty($arrayVoti)) {
        echo'<div><img src="http://chart.apis.google.com/chart?chf=c,lg,0,EFEFEF,0,BBBBBB,1&chxr=0,0,10&chxs=0,676767,11.5,0,lt,676767&chxt=y&chs=500x225&cht=lc&chco=3D7930&chds=0,10&chd=t:' . $arrayVoti . '&chg=10,-1,1,1&chls=2,4,0&chm=B,C5D4B5BB,0,0,0&chtt=Andamento+voti&chm=B,C5D4B5BB,0,0,0|s,FF0000,0,-1,5" width="300" height="225" alt="Andamento voti" /></div>';
    }
}

function visualizzaConfronto($userId1, $userId2) {
    $arrayVoti1 = getVotiArray($userId1);
    $arrayVoti2 = getVotiArray($userId2);
    $user1 = getUserById($userId1);
    $user2 = getUserById($userId2);
    $nome1 = $user1["NAME"] . " " . $user1["SURNAME"];
    $nome2 = $user2["NAME"] . " " . $user2["SURNAME"];

    echo'<div><img src="http://chart.apis.google.com/chart?chf=c,lg,0,EFEFEF,0,BBBBBB,1&chxr=0,0,10&chxs=0,676767,11.5,0,lt,676767&chxt=y&chs=500x375&cht=lc&chco=3D7930,FF9900&chds=0,10&chd=t:' . $arrayVoti1 . '|' . $arrayVoti2 . '&chdl=' . $nome1 . '|' . $nome2 . '&chg=10,-1,1,1&chls=2,4,0&chtt=Andamento+voti&chdlp=b" width="500" height="375" alt="Andamento voti" /></div>';
}

?>
