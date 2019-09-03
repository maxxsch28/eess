<?php

include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//cargaLecturaAforadores2.php
//print_r($_POST);
ChromePhp::log($_POST);
/*
Array ( 
[turno] => Noche 
[tipoDeCargaCierreCEM] => litros 
[fechaCierre] => 09/05/2019 
[calc_01a_14] => 949.88 
[01a_14] => 1.65 
[calc_01b_15] => 972.61 
[01b_15] => 0 
[calc_01c_16] => 1920.83 
[01c_16] => 0 
[calc_01d_17] => 1004.72 
[01d_17] => 0 
[calc_02a_18] => 750.72 
[02a_18] => 35.87 
[calc_02b_19] => 1004.31 
[02b_19] => 60.79 
[calc_02c_20] => 3040.6000000000004 
[02c_20] => 706.26 
[calc_02d_21] => 1134.45 
[02d_21] => 213.48 
[calc_03a_22] => 1161.7800000000002 
[03a_22] => 98.89 
[calc_03b_23] => 2419.91 
[03b_23] => 172.58 
[calc_03c_24] => 1622.04 
[03c_24] => 382.95 
[calc_03d_25] => 755.9 
[03d_25] => 73 
[calc_04a_26] => 1485.1599999999999 
[04a_26] => 132.86 
[calc_04b_27] => 1153.17 
[04b_27] => 112.76 
[calc_04c_28] => 1844 
[04c_28] => 339.77 
[calc_04d_29] => 916.31 
[04d_29] => 92.75 
[calc_05_38] => 1978.2900000000002 
[05_38] => 189.66 
[calc_06_39] => 1575.8999999999999 
[06_39] => .3 
[calc_07_40] => 4530.13 
[07_40] => 172.06 
[calc_08_41] => 5632.3099999999995 
[08_41] => 200.49 
[tq1] => 10738 
[tq2] => 4185 
[tq3] => 0 
[tq4] => 10576 
[tq5] => 111 
[tq6] => 11 
[yerNS] => 0 
[yerUD] => 0 
[yerNI] => 50.0000 
[yerED] => 0 ) 

*/












$fecha = explode('/', $_POST['fechaCierre']);
$sqlTanques = "INSERT INTO cierres_cem_tanques (fechaCarga, fechaCierre, turno, tq1, tq2, tq3, tq4, tq5, tq6) values (now(), '$fecha[2]-$fecha[1]-$fecha[0] 22:00', '$_POST[turno]', $_POST[tq1], $_POST[tq2], $_POST[tq3], $_POST[tq4], $_POST[tq5], $_POST[tq6])";
//echo $sqlTanques;
//$result = $mysqli->query($sqlTanques);
if(isset($_POST['yerNS'])&&$_POST['yerNS']>0){$yerNS=-$_POST['yerNS'];} else {$yerNS=0;}
if(isset($_POST['yerUD'])&&$_POST['yerUD']>0){$yerUD=-$_POST['yerUD'];} else {$yerUD=0;}
if(isset($_POST['yerED'])&&$_POST['yerED']>0){$yerED=-$_POST['yerED'];} else {$yerED=0;}
if(isset($_POST['yerNI'])&&$_POST['yerNI']>0){$yerNI=-$_POST['yerNI'];} else {$yerNI=0;}
$sqlYER = "INSERT INTO yer (fecha, despachos,ns,ud,np,ed) VALUES ('$fecha[2]-$fecha[1]-$fecha[0]', 1, $yerNS, $yerUD, $yerNI, $yerED)";
$result2 = $mysqli->query($sqlYER);

echo $sqlYER;
if(isset($_POST['tipoDeCargaCierreCEM'])&&$_POST['tipoDeCargaCierreCEM']=='litros'){
  // CIO x nueva configuracion
  $insert = "fechaCarga, fechaCierre, ";
  $values = "now(), '$fecha[2]-$fecha[1]-$fecha[0] 22:00', ";
  foreach($_POST as $key => $value){
    $pos = strpos($key, 'calc_');
    echo "$key -> $value --$pos--<br/>";
    if($pos !== false){
      $insert.= "`".substr($key, 5)."`, ";
      $values .= "'".round($value,2)."', ";
    }
  }
  $insert = substr($insert, 0, -2);
  $values = substr($values, 0, -2);
  $sql = "INSERT INTO cierres_cio_aforadores ($insert) VALUES ($values);";
} else {
  $sql = "INSERT INTO cierres_cio_aforadores ($insert) VALUES (now(), '$fecha[2]-$fecha[1]-$fecha[0] 22:00', $_POST[ed1], $_POST[ns1], $_POST[ni1], $_POST[ed2], $_POST[ns2], $_POST[ni2], $_POST[ud3], $_POST[ed4], $_POST[ud5], $_POST[ud6], $_POST[ed7]);";
}
ChromePhp::log($sql);
echo $sql;
$result = $mysqli->query($sql);
die;
if(!$result){
    echo "error";
} else {
    echo "success";
}

// $tanquePorSurtidor = array('ns1' => 3, 'ns2' => 3, 'ed1' => 4, 'ed2' => 4, 'np1' => 5, 'np2' => 5, 'ud1' => 2, 'ed3' => 1, 'ed4' => 4, 'ud5' => 6, 'ud6' => 6);
?>
