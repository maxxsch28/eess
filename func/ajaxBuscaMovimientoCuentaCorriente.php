<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$limit=11;
$offset=0;

//print_r($_REQUEST);

$sqlFactura = "select * from dbo.MovimientosFac where puntoVenta=$_POST[prefijo] and numero=$_POST[numero];";

$stmt = odbc_exec2( $mssql, $sqlFactura, __LINE__, __FILE__);


$facturaTotal = sqlsrv_fetch_array($stmt);
$importeRastrear = round($facturaTotal['Total'] - $_POST['importe'],2);
if($importeRastrear==0){
    $fecha=true;
    echo "<tbody class='turno'><tr class='encabezaAsiento'><td align='left'><b>Importe total factura</b></td><td colspan='2'></td></tr></tbody>";
}

$sqlMovimientos = "SELECT IdMovimientoCta, RazonSocial, Fecha, IdTipoMovimiento, IdMovimientoFac, IdRecibo, IdMovimientoImputado, Importe FROM dbo.movimientosCta, dbo.Clientes 
WHERE dbo.Clientes.IdCliente=dbo.movimientosCta.idCliente AND idmovimientoimputado = (select IdMovimientoCta from dbo.movimientosCta where idmovimientofac=(select IdMovimientofac from dbo.MovimientosFac where puntoVenta=$_POST[prefijo] and numero=$_POST[numero] and idtipomovimiento='FAA'))";

//$sqlImporte = "select IdMovimientoCta, RazonSocial, Fecha, IdTipoMovimiento, IdMovimientoFac, IdRecibo, IdMovimientoImputado from dbo.MovimientosCta, dbo.Clientes where Importe=$importeRastrear and dbo.Clientes.idCliente=dbo.MovimientosCta.IdCliente and dbo.MovimientosCta.IdCliente=$facturaTotal[IdCliente];";


echo $sqlMovimientos;
//echo $sqlImporte;
//echo $sqlFactura;


$stmt = odbc_exec2( $mssql, $sqlMovimientos, __LINE__, __FILE__);

echo "<tbody class='turno'>";
while($rowImporte = sqlsrv_fetch_array($stmt)){
    //print_r($rowImporte); 
	$fecha = date_format($rowImporte['Fecha'], "d/m/Y H:i:s");
	
	echo "<tr class='encabezaAsiento'><td align='left' colspan='3'>$fecha // IdMovimientoCta Nº $rowImporte[0]</td></tr>";
	
	echo "<tr class='fila'><td class='cuentaD'>$rowImporte[RazonSocial]</td><td>$rowImporte[IdTipoMovimiento]</td><td class='debe'>$ $rowImporte[Importe]</td></tr>";
    if($rowImporte['IdTipoMovimiento']=='REC'){
        // muestra número de recibo
        $sqlRecibo = "select * from dbo.Recibos where IdRecibo=$rowImporte[IdRecibo];";
        $stmt = odbc_exec2( $mssql, $sqlRecibo, __LINE__, __FILE__);
        $rowRecibo = sqlsrv_fetch_array($stmt);
        echo "<tr class='fila'><td class='cuentaD' colspan=3>Recibo $rowRecibo[PuntoVenta] - $rowRecibo[Numero]</td></tr>";
        //echo $sqlRecibo;
    }
}
echo "</tbody>";	
if(!isset($fecha))echo "<tbody><tr><td colspan='2'>No hay resultados</td></tr></tbody>";
?>
