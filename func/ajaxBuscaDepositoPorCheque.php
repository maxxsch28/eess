<?php
// buscaDepositoPorCheque.php
// busca cheques depositados en bancos y también busca ingresos de cheques por recibos
include_once('../include/inicia.php');

$limit=11;
$offset=0;

$andFecha=(isset($_REQUEST['rangoInicio']))?" AND dbo.DepositosBancarios.Fecha>='{$_REQUEST['rangoInicio']}' AND dbo.DepositosBancarios.Fecha<='{$_REQUEST['rangoFin']} 23:59:59'":'';

$andFecha2=(isset($_REQUEST['rangoInicio']))?" AND dbo.Recibos.Fecha>='{$_REQUEST['rangoInicio']}' AND dbo.Recibos.Fecha<='{$_REQUEST['rangoFin']} 23:59:59'":'';

$fuzziness=(isset($_REQUEST['fuzzy']))?" AND dbo.ChequesTerceros.Importe>=".floor(($_REQUEST['importe']-$_REQUEST['fuzziness']))." AND dbo.ChequesTerceros.Importe<=".ceil($_REQUEST['importe']+$_REQUEST['fuzziness']):" AND dbo.ChequesTerceros.Importe=$_REQUEST[importe]";

if(!isset($_REQUEST['importe'])||$_REQUEST['importe']==0&&($leyenda||$cuenta)<>''){
	$fuzziness='';
} elseif(!isset($_REQUEST['importe'])||$_REQUEST['importe']==0&&($leyenda&&$cuenta)==''){
	echo "<tbody><tr><td colspan='2' class='act'>Ingrese parámetros de búsqueda</td></tr></tbody>";
	die;
}


$sqlAsientos = trim("SELECT dbo.DepositosBancarios.fecha as fecha, Numero, Emisor, dbo.chequesterceros.importe as importeCheque, dbo.movimientosBancarios.idAsiento, dbo.movimientosBancarios.idCuentaBancaria, dbo.movimientosBancarios.importe as importeTotal FROM dbo.ChequesTerceros, dbo.MovimientosBancarios, dbo.DepositosBancarios WHERE dbo.chequesTerceros.idMovimientoBancario=dbo.MovimientosBancarios.idMovimientoBancario AND dbo.MovimientosBancarios.idDepositoBancario=dbo.DepositosBancarios.idDepositoBancario{$fuzziness} $andFecha;");

$sqlRecibos = trim("select dbo.Recibos.fecha as fecha, dbo.chequesTerceros.Numero, Emisor, dbo.chequesTerceros.importe as importeCheque, dbo.Recibos.idAsiento, dbo.Recibos.Numero as numRec FROM dbo.ChequesTerceros, dbo.Recibos WHERE Recibos.idRecibo=ChequesTerceros.IdRecibo {$fuzziness} $andFecha2;");

// AND (dbo.asientos.IdModeloContable=dbo.ModelosContables.IdModeloContable OR dbo.asientos.IdModeloContable is NULL)

// , dbo.ModelosContables

//, dbo.ModelosContables.Nombre

echo $sqlAsientos;

$stmt = sqlsrv_query( $mssql, $sqlAsientos);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlAsientos<br/>";
     die( print_r( sqlsrv_errors(), true));
}
while($rowAsientos = sqlsrv_fetch_array($stmt)){
    $a1=1;
	$fecha = date_format($rowAsientos['fecha'], "d/m/Y");
	echo "<tbody class='asiento' id='$rowAsientos[idAsiento]'><tr class='encabezaAsiento'><td align='left'>$fecha</td><td colspan='2'>Nº $rowAsientos[idAsiento]</td></tr>";
	$monto = sprintf("%.2f",$rowAsientos['importeCheque']);
    $montoTotal = sprintf("%.2f",$rowAsientos['importeTotal']);
    $monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
    $montoTotal = number_format(str_replace(',', '.', $montoTotal), 2, ',', '.');
    if(isset($_REQUEST['fuzzy'])){
		$act = (($rowAsientos['importeCheque']>=($_REQUEST['importe']-$_REQUEST['fuzziness'])) && ($rowAsientos['importeCheque']<=($_REQUEST['importe']+$_REQUEST['fuzziness'])))?" montoBuscado":'';
	} else {
		$act = ($rowAsientos['importeCheque']==$_REQUEST['importe'])?" montoBuscado":'';
	}
	echo "<tr class='fila'><td class='cuentaD'>$fecha, $rowAsientos[Emisor]</td><td class='debe$act'>$monto</td><td class='haber'>$montoTotal</td></tr>";
	echo "</tbody>";	
} 
if(!isset($a1))echo "<tbody><tr><td colspan='2'>No hay depósitos</td></tr></tbody>";

$stmt = sqlsrv_query( $mssql, $sqlRecibos);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlRecibos<br/>";
     die( print_r( sqlsrv_errors(), true));
}
while($rowRecibos = sqlsrv_fetch_array($stmt)){
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