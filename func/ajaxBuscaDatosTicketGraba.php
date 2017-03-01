<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
if(isset($_POST['soloResultados'])){
  $sqlResultados = "select e.Empleado, count(c.IdTicket) as cuantos, sum(c.esNafta) as nafta, sum(c.esDespacho) as despachos from [coop].dbo.promoDesayunos as c, [CoopDeTrabajo.Net].dbo.Empleados as e where c.IdEmpleado=e.IdEmpleado AND c.mesAsignado=".date("Ym")." Group by e.Empleado order by cuantos desc;";
  $stmt = odbc_exec( $mssql, $sqlResultados);
  $tr='';
  while($rowResultados = odbc_fetch_array($stmt)){
    $tr .= "<tr><td>$rowResultados[0]<td>$rowResultados[cuantos]</td><td>".round($rowResultados['nafta']/$rowResultados['cuantos']*100,0)."%</td><td>".round($rowResultados['despachos']/$rowResultados['cuantos']*100,0)."%</td></tr>";
  }
  
  echo json_encode(array('status' => 'yes', 'resultados'=>$tr));
  die;
} else {

  //[coop].[dbo].[promoDesayunos]
  // Revisa si corresponde a esDespacho
  $sqlDespacho = "SELECT IdDespacho FROM dbo.despachos WHERE IdMovimientoFac=$_POST[IdMovimientoFac]";
  $stmt = odbc_exec( $mssql, $sqlDespacho);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sqlDespacho<br/>";
      die( print_r( sqlsrv_errors(), true));
  }
  //fb($sqlDespacho);
  $rowDespacho = odbc_fetch_array($stmt);
  if(isset($rowDespacho[0])&&is_numeric($rowDespacho[0])){
    $esDespacho = 1;
  } else {
    $esDespacho = 0;
  }

  $dia = explode('/', $_POST['fcanje']);
  $fCanje = $dia[2].$dia[1].$dia[0];


  $sqlGraba = "INSERT INTO [coop].[dbo].[promoDesayunos] (IdMovimientoFac, esNafta, fechaCanje, esDespacho, IdEmpleado, mesAsignado) VALUES ('$_POST[IdMovimientoFac]', '$_SESSION[esNafta]', '$fCanje', '$esDespacho', '$_POST[IdEmpleado]', '$_POST[mesAsignado]')";

  //fb($sqlGraba);
  $stmt = odbc_exec( $mssql, $sqlGraba);
  if( $stmt === false ){
    echo "1. Error in executing query.</br>$sqlGraba<br/>";
    die( print_r( sqlsrv_errors(), true));
  } else {
    $sqlUltimoTicket = "select facturas.IdMovimientoFac, esNafta, fechaCanje, esDespacho, mesAsignado, Empleado, IdTipoMovimiento, PuntoVenta, Numero FROM [coop].[dbo].[promoDesayunos] as cupones, [CoopDeTrabajo.Net].[dbo].[empleados] as empleados, [CoopDeTrabajo.Net].[dbo].[movimientosfac] as facturas WHERE cupones.IdMovimientoFac=facturas.IdMovimientoFac and cupones.IdEmpleado=empleados.IdEmpleado ORDER BY idTicket desc;";
    $stmt = odbc_exec( $mssql, $sqlUltimoTicket);
    $rowUltimoTicket = odbc_fetch_array($stmt);
    
    $sqlResultados = "select e.Empleado, count(c.IdTicket) as cuantos, sum(c.esNafta) as nafta, sum(c.esDespacho) as despachos from [coop].dbo.promoDesayunos as c, [CoopDeTrabajo.Net].dbo.Empleados as e where c.IdEmpleado=e.IdEmpleado AND c.mesAsignado=$_POST[mesAsignado] Group by e.Empleado order by cuantos desc;";
    $stmt = odbc_exec( $mssql, $sqlResultados);
    $tr='';
    while($rowResultados = odbc_fetch_array($stmt)){
      $tr .= "<tr><td>$rowResultados[0]<td>$rowResultados[cuantos]</td><td>".round($rowResultados['nafta']/$rowResultados['cuantos']*100,0)."%</td><td>".round($rowResultados['despachos']/$rowResultados['cuantos']*100,0)."%</td></tr>";
    }
    
    echo json_encode(array('status' => 'yes', 'resultados'=>$tr, 'ultimoTicket'=>"<tr><td>$rowUltimoTicket[Empleado]</td><td>$rowUltimoTicket[mesAsignado]</td><td>$rowUltimoTicket[IdTipoMovimiento] $rowUltimoTicket[PuntoVenta]-$rowUltimoTicket[Numero]</td><td>".$rowUltimoTicket['fechaCanje']->format('d/m/Y')."</td><td>".(($rowUltimoTicket['esNafta']==1)?'Nafta':'Diesel')."</td><td>".(($rowUltimoTicket['esDespacho']==1)?'':'MANUAL')."</td></tr>"));
    //fb(json_encode(array('status' => 'yes', 'ultimoTicket'=>"<tr><td>$rowUltimoTicket[Empleado]</td><td>$rowUltimoTicket[mesAsignado]</td><td>$rowUltimoTicket[IdTipoMovimiento] $rowUltimoTicket[PuntoVenta]-$rowUltimoTicket[Numero]</td><td>".$rowUltimoTicket['fechaCanje']->format('d/m/Y')."</td><td>".(($rowUltimoTicket['esNafta']==1)?'Nafta':'Diesel')."</td><td>".(($rowUltimoTicket['esDespacho']==1)?'':'MANUAL')."</td></tr>")));
  }
}
?>
