<?php
// buscaTarjetaCuponLote.php
// busca lotes, cupones o acreditaciones de tarjeta, tratando de dar la máxima información posible
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$limit=11;
$offset=0;


// IdCuponTarjetaCredito	IdTarjeta	IdCaja	NumeroCupon	Fecha	IdCliente	RazonSocial	Importe	IdCierreTurno	NumeroTarjeta	Telefono	CodigoAprobacion	IdTipoDocumento	NumeroDocumento	FechaPresentacion	FechaAcreditacion	TipoIngreso	IdMovimientoFac	IdRecibo	Transaccion	IdLoteTarjetasCredito	UserName	LastUpdated	RowVersion	AcreditadoEnSeleccion	SyncGUID	IdEstacion	IdAsiento	IdRNComanda	Controlado


$andFecha=(isset($_REQUEST['rangoInicio']))?" AND dbo.CuponesTarjetasCredito.Fecha>='{$_REQUEST['rangoInicio']}' AND dbo.CuponesTarjetasCredito.Fecha<='{$_REQUEST['rangoFin']} 23:59:59'":'';

$andFecha2=(isset($_REQUEST['rangoInicio']))?" AND dbo.LotesTarjetasCredito.Fecha>='{$_REQUEST['rangoInicio']}' AND dbo.LotesTarjetasCredito.Fecha<='{$_REQUEST['rangoFin']} 23:59:59'":'';

$fuzziness=(isset($_REQUEST['fuzzy']))?" AND CuponesTarjetasCredito.Importe>=".floor(($_REQUEST['importe']-$_REQUEST['fuzziness']))." AND CuponesTarjetasCredito.Importe<=".ceil($_REQUEST['importe']+$_REQUEST['fuzziness']):" AND CuponesTarjetasCredito.Importe=$_REQUEST[importe]";

$fuzziness1=(isset($_REQUEST['fuzzy']))?" AND LotesTarjetasCredito.Importe>=".floor(($_REQUEST['importe']-$_REQUEST['fuzziness']))." AND LotesTarjetasCredito.Importe<=".ceil($_REQUEST['importe']+$_REQUEST['fuzziness']):" AND LotesTarjetasCredito.Importe=$_REQUEST[importe]";

if(!isset($_REQUEST['importe'])||$_REQUEST['importe']==0&&($leyenda||$cuenta)<>''){
	$fuzziness='';
} elseif(!isset($_REQUEST['importe'])||$_REQUEST['importe']==0&&($leyenda&&$cuenta)==''){
	echo "<tbody><tr><td colspan='2' class='act'>Ingrese parámetros de búsqueda</td></tr></tbody>";
	die;
}


$sqlCupones = trim("SELECT dbo.TarjetasCredito.Nombre, NumeroCupon as Numero, dbo.CuponesTarjetasCredito.Fecha as fecha, dbo.cuponesTarjetasCredito.idAsiento, dbo.LotesTarjetasCredito.importe as importeTotal FROM dbo.TarjetasCredito, dbo.CuponesTarjetasCredito, dbo.LotesTarjetasCredito WHERE dbo.TarjetasCredito.idTarjeta=dbo.CuponesTarjetasCredito.idTarjeta AND dbo.CuponesTarjetasCredito.IdLoteTarjetasCredito=dbo.LotesTarjetasCredito.IdLoteTarjetasCredito{$fuzziness} $andFecha;");

$sqlLotes = trim("select dbo.Recibos.fecha as fecha, dbo.chequesTerceros.Numero, Emisor, dbo.chequesTerceros.importe as importeCheque, dbo.Recibos.idAsiento, dbo.Recibos.Numero as numRec FROM dbo.ChequesTerceros, dbo.Recibos WHERE Recibos.idRecibo=ChequesTerceros.IdRecibo {$fuzziness} $andFecha2;");

// AND (dbo.asientos.IdModeloContable=dbo.ModelosContables.IdModeloContable OR dbo.asientos.IdModeloContable is NULL)

// , dbo.ModelosContables

//, dbo.ModelosContables.Nombre

echo $sqlCupones;

$stmt = odbc_exec( $mssql, $sqlCupones);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlCupones<br/>";
     die( print_r( sqlsrv_errors(), true));
}
while($rowCupones = odbc_fetch_array($stmt)){
    $a1=1;
	$fecha = date_format($rowCupones['fecha'], "d/m/Y");
	echo "<tbody class='asiento' id='$rowCupones[idAsiento]'><tr class='encabezaAsiento'><td align='left'>$fecha</td><td colspan='2'>Nº $rowCupones[idAsiento]</td></tr>";
	$monto = sprintf("%.2f",$rowCupones['importeCheque']);
    $montoTotal = sprintf("%.2f",$rowCupones['importeTotal']);
    $monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
    $montoTotal = number_format(str_replace(',', '.', $montoTotal), 2, ',', '.');
    if(isset($_REQUEST['fuzzy'])){
		$act = (($rowCupones['importeCheque']>=($_REQUEST['importe']-$_REQUEST['fuzziness'])) && ($rowCupones['importeCheque']<=($_REQUEST['importe']+$_REQUEST['fuzziness'])))?" montoBuscado":'';
	} else {
		$act = ($rowCupones['importeCheque']==$_REQUEST['importe'])?" montoBuscado":'';
	}
	echo "<tr class='fila'><td class='cuentaD'>$fecha, $rowCupones[Emisor]</td><td class='debe$act'>$monto</td><td class='haber'>$montoTotal</td></tr>";
	echo "</tbody>";	
} 
if(!isset($a1))echo "<tbody><tr><td colspan='2'>No hay depósitos</td></tr></tbody>";

$stmt = odbc_exec( $mssql, $sqlRecibos);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlRecibos<br/>";
     die( print_r( sqlsrv_errors(), true));
}
while($rowRecibos = odbc_fetch_array($stmt)){
    $a2=1;
	$fecha = date_format($rowRecibos['fecha'], "d/m/Y");
	echo "<tbody class='recibo' id='$rowRecibos[idAsiento]'><tr class='encabezaAsiento'><td align='left'>$fecha</td><td colspan='2'>Nº $rowRecibos[idAsiento]</td></tr>";
	$monto = sprintf("%.2f",$rowRecibos['importeCheque']);
//    $montoTotal = sprintf("%.2f",$rowRecibos['importeTotal']);
    $monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
 //   $montoTotal = number_format(str_replace(',', '.', $montoTotal), 2, ',', '.');
    if(isset($_REQUEST['fuzzy'])){
		$act = (($rowRecibos['importeCheque']>=($_REQUEST['importe']-$_REQUEST['fuzziness'])) && ($rowRecibos['importeCheque']<=($_REQUEST['importe']+$_REQUEST['fuzziness'])))?" montoBuscado":'';
	} else {
		$act = ($rowRecibos['importeCheque']==$_REQUEST['importe'])?" montoBuscado":'';
	}
	echo "<tr class='fila'><td class='cuentaD'>$fecha, <b>Recibo $rowRecibos[numRec]</b>, $rowRecibos[Emisor]</td><td class='debe$act'>$monto</td><td class='haber'>$montoTotal</td></tr>";
	echo "</tbody>";	
}
if(!isset($a2))echo "<tbody><tr><td colspan='2'>No hay recibos</td></tr></tbody>";
?>