<?php
// calculaPromedios.php
include_once('../include/inicia.php');

$limit=11;
$offset=0;

//print_r($_REQUEST);
// llego con recibo o llego con cliente...
if(isset($_POST['buscaCliente2'])){
    $sqlPrincipal = "SELECT DISTINCT dbo.movimientoscta.idRecibo, numero from dbo.movimientoscta, dbo.recibos where idTipomovimiento='REC' AND dbo.recibos.idRecibo=dbo.movimientoscta.idrecibo AND dbo.movimientoscta.idCliente=(SELECT idCliente from dbo.clientes where codigo='$_POST[cliente2]')";
} else{
    $sqlPrincipal = "SELECT DISTINCT dbo.movimientoscta.idRecibo, Numero FROM dbo.MovimientosCta, dbo.Recibos WHERE dbo.MovimientosCta.IdTipoMovimiento='REC' AND dbo.Recibos.IdRecibo=dbo.MovimientosCta.IdRecibo AND dbo.MovimientosCta.IdRecibo=(SELECT IdRecibo FROM dbo.Recibos WHERE Numero=$_POST[recibo])";
}
echo $sqlPrincipal;
$stmt0 = sqlsrv_query( $mssql, $sqlPrincipal);
if( $stmt0 === false ){
     echo "1. Error in executing query.</br>$sqlPrincipal<br/>";
     die( print_r( sqlsrv_errors(), true));
}
echo "<tbody class='turno'>";

while($rowPrincipal = sqlsrv_fetch_array($stmt0)){
    $sqlFactura = "select * from dbo.movimientoscta where idrecibo=$rowPrincipal[0];";
    $stmt = sqlsrv_query( $mssql, $sqlFactura);
    if( $stmt === false ){
         echo "1. Error in executing query.</br>$sqlFactura<br/>";
         die( print_r( sqlsrv_errors(), true));
    }
    
    echo "<tr class='encabezaAsiento'><td> IdMovimientoCta Nº $rowRecibo[0]</td><td align='left'>$fecha</td><td>Recibo 99-$rowPrincipal[1]</td><td></td></tr>";
    while($rowRecibo = sqlsrv_fetch_array($stmt)){

        $fecha = date_format($rowRecibo['Fecha'], "d/m/Y H:i:s");


        $sqlMovimientoImputado = "select * from dbo.movimientoscta where idmovimientocta=$rowRecibo[IdMovimientoImputado]";
        $stmt2 = sqlsrv_query( $mssql, $sqlMovimientoImputado);
        if( $stmt2 === false ){
             echo "1. Error in executing query.</br>$sqlMovimientoImputado<br/>";
             die( print_r( sqlsrv_errors(), true));
        }
        $rowMovimientoImputado = sqlsrv_fetch_array($stmt2);

        echo "<tr class='fila".(($rowRecibo['Importe']==$rowMovimientoImputado['Importe'])?'':' error OPcaida')."'><td>$rowMovimientoImputado[IdTipoMovimiento]</td><td>Imputado $rowRecibo[Importe]</td><td> $rowMovimientoImputado[Importe]</td><td>".(($rowRecibo['Importe']==$rowMovimientoImputado['Importe'])?'':'<span class="error OPcaida">PARCIAL</b>')."</td></tr>";

    }
    if(!isset($fecha))echo "<tbody><tr><td colspan='2'>No hay resultados</td></tr></tbody>";
}
echo "</tbody>";
?>