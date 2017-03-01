<?php

include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//cargaLecturaAforadores.php
// [ns1] => 1212 [ns2] => 121212 [ed1] => 21212 [ed2] => 121212 [np1] => 1212 [np2] => 1212 [ud1] => 12121 [ed3] => 21212 [ed4] => 121 [ud5] => 2121 [ud6] => 2121
$fecha = explode('/', $_POST['fechaCierre']);
$sqlTanques = "INSERT INTO cierres_tanques (fechaCarga, fechaCierre, turno, tq1, tq2, tq3, tq4, tq5, tq6) vales (now(), '$fecha[2]-$fecha[1]-$fecha[0]', $_POST[tq1], $_POST[tq2], $_POST[tq3], $_POST[tq4], $_POST[tq5], $_POST[tq6])";
$result = $mysqli->query($sqlTanques);

$sql = "INSERT INTO cierres_aforadores (fechaCarga,	fechaCierre, ed1, ns1, ni1, ed2, ns2, ni2, ud3, ed4, ud5, ud6, ed7) VALUES (now(), '$fecha[2]-$fecha[1]-$fecha[0]', $_POST[ed1], $_POST[ns1], $_POST[ni1], $_POST[ed2], $_POST[ns2], $_POST[ni2], $_POST[ud3], $_POST[ed4], $_POST[ud5], $_POST[ud6], $_POST[ed7]);";
$result = $mysqli->query($sql);

if(!$result){
    echo "error";
} else {
    echo "success";
}

// $tanquePorSurtidor = array('ns1' => 3, 'ns2' => 3, 'ed1' => 4, 'ed2' => 4, 'np1' => 5, 'np2' => 5, 'ud1' => 2, 'ed3' => 1, 'ed4' => 4, 'ud5' => 6, 'ud6' => 6);
?>