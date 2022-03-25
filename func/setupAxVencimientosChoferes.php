<?php
// setupAxVencimientosChoferes.php
// levanta de distintas bases todas las fechas de documentacion de los choferes
// devuelve json

include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
$arrayChofer = array();

if(isset($_POST['idChofer'])&&is_int(intval($_POST['idChofer']))){
  $idChofer = $_POST['idChofer'];
  $sqlChofer = "select vtopsicofi, vtocarnet, vtocharla, segurovida from dbo.choferes where codigo=$idChofer;";
  $sqlEquipos = "select b.equipo, b.tipoequipo, b.marca, b.patente, b.modelo, b.anio, b.chasis, b.vtopoliza, vtoveritec, ruta, segurocarg from dbo.choferes a, dbo.equipos b where a.codigo=$idChofer and (a.equipo=b.equipo OR a.acoplado=b.equipo) ";
  ChromePHP::log($sqlEquipos);

  $stmt = odbc_exec2( $mssql2, $sqlChofer, __LINE__, __FILE__);
  while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
    $arrayChofer = array('vtopsicofi'=>$row['vtopsicofi']->format('Y-m-d'), 'vtocarnet'=>$row['vtocarnet']->format('Y-m-d'), 'vtocharla'=>$row['vtocharla']->format('Y-m-d'), 'segurovida'=>$row['segurovida']->format('Y-m-d'));
  }

  $stmt = odbc_exec2( $mssql2, $sqlEquipos, __LINE__, __FILE__);
  //json_encode(array('tipo'=>'remito', 'status' => 'success','message'=> 'Camión registrado'));
  while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
    if($row['tipoequipo']==4)$row['tipoequipo']=6;
    $arrayChofer['equipo'.$row['tipoequipo']] = trim($row['equipo']);
    $arrayChofer['marca'.$row['tipoequipo']] = trim($row['marca']);
    $arrayChofer['patente'.$row['tipoequipo']] = trim($row['patente']);
    $arrayChofer['anio'.$row['tipoequipo']] = trim($row['anio']);
    $arrayChofer['modelo'.$row['tipoequipo']] = trim($row['modelo']);
    $arrayChofer['chasis'.$row['tipoequipo']] = trim($row['chasis']);
    $arrayChofer['vtopoliza'.$row['tipoequipo']] = $row['vtopoliza']->format('Y-m-d');
    $arrayChofer['vtoveritec'.$row['tipoequipo']] = $row['vtoveritec']->format('Y-m-d');
    $arrayChofer['ruta'.$row['tipoequipo']] = $row['ruta']->format('Y-m-d');
    $arrayChofer['segurocarg'.$row['tipoequipo']] = $row['segurocarg']->format('Y-m-d');
  }

  ChromePHP::log(json_encode($arrayChofer));
  echo json_encode($arrayChofer);
} else if(isset($_POST)){
  ChromePHP::log('Recibe datos para actualizar chofer id ' . $_POST['busca']);
  ChromePHP::log($_POST);
  $sqlChofer = "UPDATE dbo.choferes SET segurovida='$_POST[segurovida]', vtocarnet='$_POST[vtocarnet]', vtocharla='$_POST[vtocharla]', vtopsicofi='$_POST[vtopsicofi]'  WHERE codigo='$_POST[busca]'";
  if(isset($_POST['equipo6'])){
    $sqlEquipo6 = "UPDATE equipos SET segurocarg='$_POST[segurocarg6]', vtopoliza='$_POST[vtopoliza6]', vtoveritec='$_POST[vtoveritec6]', ruta='$_POST[ruta6]' WHERE equipo='$_POST[equipo6]';";
  }
  if(isset($_POST['equipo2'])){
    $sqlEquipo2 = "UPDATE equipos SET segurocarg='$_POST[segurocarg2]', vtopoliza='$_POST[vtopoliza2]', vtoveritec='$_POST[vtoveritec2]', ruta='$_POST[ruta2]' WHERE equipo='$_POST[equipo2]';";
  }
  
  $stmt = odbc_exec2( $mssql2, $sqlChofer, __LINE__, __FILE__);
  $stmt2 = odbc_exec2( $mssql2, $sqlEquipo2, __LINE__, __FILE__);
  $stmt6 = odbc_exec2( $mssql2, $sqlEquipo6, __LINE__, __FILE__);
  ChromePHP::log($sqlChofer, $sqlEquipo6, $sqlEquipo2);
  if($stmt && $stmt2 && $stmt6){
    echo "ok";
  } else {
    echo "error";
  }
} else {
  echo "Error";
}

?>