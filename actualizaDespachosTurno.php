<?php
if(substr($_SERVER['HTTP_USER_AGENT'], 0,4)=='curl'){
  //lo llame desde cron
} else {
  $nivelRequerido = 4;
}
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');




// Para los dos turnos del día me fijo cuantos despachos hubo y cuantos envases de elaion de litro se vendieron

$sqlTurnos = "SELECT IdCierreturno, Fecha, IdEmpleado1, IdEmpleado2, IdEmpleado3, IdEmpleado4 FROM dbo.cierresturno where idCaja=1 AND Fecha>='".date("Y-m-d", time()-90000)." 19:00:00' ORDER BY Fecha ASC;";
//$sqlTurnos = "SELECT IdCierreturno, Fecha, IdEmpleado1, IdEmpleado2, IdEmpleado3, IdEmpleado4 FROM dbo.cierresturno where idCaja=1 AND Fecha>='2017-06-30 19:00:00' ORDER BY Fecha ASC;";
echo $sqlTurnos.'<br>';
 
$stmt = odbc_exec2( $mssql, $sqlTurnos, __FILE__, __LINE__);
while($rowTurnos = sqlsrv_fetch_array($stmt)){
  
  $idEmpleado = Array();
  $qAceites = array();
  $rellenaEmpleados='';
  $idEmpleado[]=$rowTurnos['IdEmpleado1'];
  $idEmpleado[]=$rowTurnos['IdEmpleado2'];
  $idEmpleado[]=$rowTurnos['IdEmpleado3'];
  $idEmpleado[]=$rowTurnos['IdEmpleado4'];
  $idEmpleado = array_filter($idEmpleado);
  $idEmpleado = array_diff($idEmpleado, array(17, 0));
  asort($idEmpleado);
  for($i=0; $i<3; $i++){
    $rellenaEmpleados .= (isset($idEmpleado[$i]))?", '$idEmpleado[$i]'":", ''";
  }
  if(!isset($fechaAnterior)){
    $fechaAnterior = $rowTurnos[1];
  } else {
    $sqlDespachos = "SELECT count(IdDespacho), sum(Cantidad), IdArticulo FROM dbo.despachos WHERE Fecha>='".$fechaAnterior->format('Y-m-d H:i:s')."' AND Fecha<='".$rowTurnos[1]->format('Y-m-d H:i:s')."' GROUP BY IdArticulo;";
    //echo $sqlDespachos.'<br>';
    $stmt2 = odbc_exec2( $mssql, $sqlDespachos, __FILE__, __LINE__);
    $qDespachos = 0;
    while($rowDespachos = sqlsrv_fetch_array($stmt2)){
      $qDespachos += $rowDespachos[0];
      // $articulo = array(2068=>"Infinia D.",2069=>"Ultra",2076=>"Infinia",2078=>"Super");
      $c[$rowDespachos[2]]=$rowDespachos[1];
    }
    $mixInfinia = round(100*$c[2076]/($c[2076]+$c[2078]),2);
    $mixInfiniaDiesel = round(100*$c[2068]/($c[2068]+$c[2069]),2);
    $fechaAnterior = $rowTurnos[1];
    
    $sqlAceite = "SELECT SUM(Cantidad) as q, b.IdGrupoArticulo, CASE WHEN Descripcion LIKE ('%1 L%') THEN '1L' ELSE '4L' END AS envase FROM dbo.MovimientosDetalleFac a, dbo.articulos b WHERE IdCierreTurno=$rowTurnos[0] AND a.IdArticulo=b.IdArticulo AND b.IdGrupoArticulo IN (1,57) AND (Descripcion like ('%XV%') OR Descripcion like ('%ELAION%')) AND Descripcion NOT LIKE ('% MOTO %') GROUP BY b.IdGrupoArticulo, Descripcion;";
    $stmt4 = odbc_exec2( $mssql, $sqlAceite, __FILE__, __LINE__);
    
    while($rowAceites = sqlsrv_fetch_array($stmt4)){
      if($rowAceites[1]==1){
        $qAceites[$rowAceites[1]] += $rowAceites[0];
      } else {
        $qAceites["$rowAceites[1]$rowAceites[2]"] += $rowAceites[0];
      }
    }
    
    
    $sqlGrabaTurno = "INSERT INTO coop.dbo.despachosTurnos (idCierreTurno, qDespachos, idEmpleado1, idEmpleado2, idEmpleado3, fecha, mixNI, mixID, qElaionL, qElaion, qXV) VALUES ($rowTurnos[0], $qDespachos $rellenaEmpleados, '".$rowTurnos[1]->format('Y-m-d H:i:s')."', $mixInfinia, $mixInfiniaDiesel, '{$qAceites['571L']}', '{$qAceites['574L']}', '{$qAceites['1']}');";
    //echo $sqlGrabaTurno.'<br>';
    $stmt3 = odbc_exec2( $mssql, $sqlGrabaTurno, __FILE__, __LINE__);
    ChromePhp::log($sqlGrabaTurno);
    
  }
  unset($idEmpleado);
}

ChromePhp::log($sqlTurnos);
?>
