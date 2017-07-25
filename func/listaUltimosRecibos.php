<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$limit=11;
$offset=0;

$sqlTurnos = "SELECT IdCierreTurno, fecha FROM dbo.CierresTurno WHERE IdCaja=1 AND DATEPART(hh, Fecha)>=19 AND DATEPART(hh, Fecha)<=23 and Fecha>'".date("Y")."-03-01' ORDER BY fecha DESC";
$stmt = odbc_exec2( $mssql, $sqlTurnos, __FILE__, __LINE__);
while($rowTurnos = sqlsrv_fetch_array($stmt)){
  // levanta datos que faltan en tabla temporal
  $sqlUltimosRecibos = "select IdRecibo, PuntoVenta, Numero, Efectivo, dbo.Recibos.UserName, dbo.Recibos.Observaciones, Fecha, dbo.Recibos.IdCliente, Codigo, RazonSocial from dbo.recibos, dbo.clientes WHERE IdCierreTurno IS NULL AND (PuntoVenta=98 OR PuntoVenta=97) AND IdCaja<>4 AND IdCierreCajaTesoreria IS NULL AND dbo.recibos.IdCliente=dbo.Clientes.IdCliente order by idrecibo desc;";
  $stmt = odbc_exec2( $mssql, $sqlUltimosRecibos, __FILE__, __LINE__);

  while($rowRecibo = sqlsrv_fetch_array($stmt)){
    echo "<tbody class='turno' id='t$rowRecibo[0]'><tr class='encabezaAsiento'><td align='left'>Recibo <b>$rowRecibo[PuntoVenta]-$rowRecibo[Numero]</b></td><td>{$rowRecibo['Fecha']->format('d/m/Y')}</td><td>$rowRecibo[Codigo] - $rowRecibo[RazonSocial]</td><td>".(($rowRecibo['Efectivo']>0)?"Efectivo <b>\$".sprintf("%01.2f",$rowRecibo['Efectivo']):'')."</td><td><span class='cambiaRecibo label label-warning' id='r_$rowRecibo[0]'><i class='glyphicon glyphicon-warning-sign'></i>CAMBIAR</span></td></tr>";
    $sqlChequesRecibos = "SELECT Fecha, Localidad, Emisor, Importe, Numero, Nombre FROM dbo.ChequesTerceros, dbo.Bancos WHERE dbo.ChequesTerceros.IdBanco=dbo.bancos.IdBanco and IdRecibo = $rowRecibo[IdRecibo];";
    //ChromePhp::log($sqlChequesRecibos);
    $stmt1 = odbc_exec2( $mssql, $sqlChequesRecibos, __FILE__, __LINE__);
    if(sqlsrv_has_rows($stmt1)){
      while($rowCheques = sqlsrv_fetch_array($stmt1)){
        echo "<tr><td colspan='3'>$rowCheques[Nombre], CP $rowCheques[Localidad]. Nº$rowCheques[Numero]. {$rowCheques['Fecha']->format('d/m/Y')} <small>$rowCheques[Emisor]</small></td><td><b>\$".sprintf("%01.2f",$rowCheques['Importe'])."</b></td><td></td></tr>";
      }
    }
    echo "</tbody>";
  }
}
if(!isset($stmt1))echo "<tbody><tr><td colspan='3'>No hay resultados</td></tr></tbody>";
?>
