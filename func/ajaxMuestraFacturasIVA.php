<?php
// listaStockTanques.php
// muestra y lleva el control de stocks de combustibles.
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_GET);
 // $array=array();
//$_POST['mes']='201411';

$teresa = (isset($_GET['teresa']))?" AND Fecha>'".date("Y-m-d")."'":" AND Fecha>'".date("Y")."-01-01'";

if($loggedInUser->display_username<>'maxxs'){
  $SoloParaTeresa = "AND dbo.movimientosfac.UserName NOT LIKE ('%@SCHIMMEL2') AND dbo.movimientosfac.UserName NOT LIKE ('%@HEGEL')";
} else {
  $SoloParaTeresa = "";
}

$sql = "select dbo.movimientosfac.IdMovimientoFac, IdTipoMovimiento, PuntoVenta, Numero, Fecha, RazonSocial, Total, Cantidad, IdArticulo, PercepcionIIBB from dbo.movimientosfac, dbo.MovimientosDetalleFac WHERE dbo.movimientosfac.IdMovimientoFac=dbo.MovimientosDetalleFac.IdMovimientoFac AND idtipomovimiento IN ('FAA','FAB') AND IdCliente>0 AND IdCondicionVenta=1 AND NetoCombustibles>0 AND Consignado=1 AND DocumentoAnticipado=0 AND DocumentoCancelado=0  $teresa AND PuntoVenta=12 AND dbo.movimientosfac.UserName NOT LIKE ('%@SERENO') $SoloParaTeresa order by dbo.MovimientosFac.IdMovimientoFac desc";
// Chromephp::log($sql);
$stmt = odbc_exec2( $mssql, $sql, __FILE__, __LINE__);

$row_count = sqlsrv_num_rows( $stmt );
// var_dump(sqlsrv_has_rows($stmt));
// var_dump($row_count);
//IdMovimientoFac	IdTipoMovimiento	PuntoVenta	Numero	Fecha	RazonSocial	Total	Cantidad	IdArticulo
//473236	FAA	8	77650	2016-04-14 09:15:08.983	STIP NESTOR	1968.6577	108.0500	2068

echo "<form name='listaFacturasExcluidas' id='listaFacturasExcluidas'><table class='table table-striped table-bordered IdCierreTurno'><thead><tr><th><b>($row_count facturas)</b>  --  Socio</th><th>Documento</th><th>Fecha</th><th>Articulos</th><th>Importe</th><th>Percep.</th></tr></thead><tbody>";
$sumaLote = 0;
$_SESSION['percepcionesFcDiferencia']=0;
while($rowFacturasExluidas = sqlsrv_fetch_array($stmt)){
  //print_r($rowFacturasExluidas);
  //Array ( [0] => 129889 [IdLoteTarjetasCredito] => 129889 [1] => VISA DEBITO [Nombre] => VISA DEBITO [2] => 2 [Loteprefijo] => 2 [3] => 257 [LoteNumero] => 257 [4] => 1193.5000 [Importe] => 1193.5000 [5] => 714 [idCuentaContable_presentacion] => 714 [6] => 616219 [idAsiento] => 616219 ) 
  $fecha = date_format($rowFacturasExluidas['Fecha'], "d/m/Y");
  echo "<tr id='fac_$rowFacturasExluidas[IdMovimientoFac]'><td>$rowFacturasExluidas[RazonSocial]</td><td>$rowFacturasExluidas[IdTipoMovimiento] $rowFacturasExluidas[PuntoVenta]-$rowFacturasExluidas[Numero]</td><td>$fecha</td><td>".sprintf("%.2f",$rowFacturasExluidas['Cantidad'])." lts. de {$articulo[$rowFacturasExluidas['IdArticulo']]}</td><td>$ ".sprintf("%.2f",$rowFacturasExluidas['Total']-$rowFacturasExluidas['PercepcionIIBB'])."</td><td>$ ".sprintf("%.2f",$rowFacturasExluidas['PercepcionIIBB'])."</td></tr>";
  if(!isset($factura)||$factura<>$rowFacturasExluidas['Numero']){
    $factura = $rowFacturasExluidas['Numero'];
    $_SESSION['percepcionesFcDiferencia'] += $rowFacturasExluidas['PercepcionIIBB'];
  }
}
echo "<tr><th colspan='5' style='text-align:right'>Total percepciones a recuperar</th><th>$ ".sprintf("%.2f",$_SESSION['percepcionesFcDiferencia'])."</th></tr></table>";
?>
