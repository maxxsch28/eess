<?php

include('../include/inicia.php');
//cargaLecturaAforadores2.php
print_r($_POST);
$fecha = explode('/', $_POST['fechaCierre']);
$sqlTanques = "INSERT INTO cierres_cem_tanques (fechaCarga, fechaCierre, turno, tq1, tq2, tq3, tq4, tq5, tq6) values (now(), '$fecha[2]-$fecha[1]-$fecha[0] 22:00', '$_POST[turno]', $_POST[tq1], $_POST[tq2], $_POST[tq3], $_POST[tq4], $_POST[tq5], $_POST[tq6])";
echo $sqlTanques;
$result = $mysqli->query($sqlTanques);
if(isset($_POST['yerNS'])&&$_POST['yerNS']>0){$yerNS=-$_POST['yerNS'];} else {$yerNS=0;}
if(isset($_POST['yerUD'])&&$_POST['yerUD']>0){$yerUD=-$_POST['yerUD'];} else {$yerUD=0;}
if(isset($_POST['yerED'])&&$_POST['yerED']>0){$yerED=-$_POST['yerED'];} else {$yerED=0;}
if(isset($_POST['yerNI'])&&$_POST['yerNI']>0){$yerNI=-$_POST['yerNI'];} else {$yerNI=0;}
$sqlYER = "INSERT INTO yer (fecha, despachos,ns,ud,np,ed) VALUES ('$fecha[2]-$fecha[1]-$fecha[0]', 1, $yerNS, $yerUD, $yerNI, $yerED)";
$result2 = $mysqli->query($sqlYER);

echo $sqlYER;
if(isset($_POST['tipoDeCargaCierreCEM'])&&$_POST['tipoDeCargaCierreCEM']=='litros'){
  $sql = "INSERT INTO cierres_cem_aforadores (fechaCarga, fechaCierre, ed1, ns1, ni1, ed2, ns2, ni2, ud3, ed4, ud5, ud6, ed7) VALUES (now(), '$fecha[2]-$fecha[1]-$fecha[0] 22:00', $_POST[calc_ed1], $_POST[calc_ns1], $_POST[calc_ni1], $_POST[calc_ed2], $_POST[calc_ns2], $_POST[calc_ni2], $_POST[calc_ud3], $_POST[calc_ed4], $_POST[calc_ud5], $_POST[calc_ud6], $_POST[calc_ed7]);";
} else {
  $sql = "INSERT INTO cierres_cem_aforadores (fechaCarga, fechaCierre, ed1, ns1, ni1, ed2, ns2, ni2, ud3, ed4, ud5, ud6, ed7) VALUES (now(), '$fecha[2]-$fecha[1]-$fecha[0] 22:00', $_POST[ed1], $_POST[ns1], $_POST[ni1], $_POST[ed2], $_POST[ns2], $_POST[ni2], $_POST[ud3], $_POST[ed4], $_POST[ud5], $_POST[ud6], $_POST[ed7]);";
}
fb($sql);

$result = $mysqli->query($sql);
die;
if(!$result){
    echo "error";
} else {
    echo "success";
}

// $tanquePorSurtidor = array('ns1' => 3, 'ns2' => 3, 'ed1' => 4, 'ed2' => 4, 'np1' => 5, 'np2' => 5, 'ud1' => 2, 'ed3' => 1, 'ed4' => 4, 'ud5' => 6, 'ud6' => 6);
?>
