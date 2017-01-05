<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include('../include/inicia.php');
 // print_r($_POST);
 // $array=array();
if(isset($_POST['mes'])){
    $mes=substr($_POST['mes'],4,2);
    $anio=substr($_POST['mes'],0,4);
}

$rangoMes = "DATEPART(month, fecha) = $mes and DATEPART(year, fecha)=$anio";
$rangoMes = "mes = $mes and anio = $anio";
// determina que movimientos suman y cuales restan.
$tiposMovimientosQueSuman = "'FAA', 'FAC', 'FAB', 'ACR', 'REC', 'REA', 'NDI', 'NDA'";


$sqlGastos = "select distinct razonsocial, sum(Costo*Cantidad*(case when IdTipoMovimientoProveedor IN ($tiposMovimientosQueSuman) then 1 else -1 end)) as resumenRenglon, sum(netoNoGravado*(case when IdTipoMovimientoProveedor IN ($tiposMovimientosQueSuman) then 1 else -1 end)) as NoGravado, sum(NetoMercaderias*(case when IdTipoMovimientoProveedor IN ($tiposMovimientosQueSuman) then 1 else -1 end)) as Mercaderias, sum(NetoCombustibles*(case when IdTipoMovimientoProveedor IN ($tiposMovimientosQueSuman) then 1 else -1 end)) as Combustibles, sum(NetoLubricantes*(case when IdTipoMovimientoProveedor IN ($tiposMovimientosQueSuman) then 1 else -1 end)) as Lubricantes, sum(NetoGastos*(case when IdTipoMovimientoProveedor IN ($tiposMovimientosQueSuman) then 1 else -1 end)) as Gastos, sum(NetoFletes*(case when IdTipoMovimientoProveedor IN ($tiposMovimientosQueSuman) then 1 else -1 end)) as Fletes, sum(Total*(case when IdTipoMovimientoProveedor IN ($tiposMovimientosQueSuman) then 1 else -1 end)) as Total, dbo.cuentasgastos.Descripcion as CuentaGasto from dbo.MovimientosPro, dbo.CuentasGastos, dbo.MovimientosDetallePro where $rangoMes and dbo.MovimientosDetallePro.IdCuentaGastos=dbo.CuentasGastos.IdCuentaGastos and dbo.MovimientosPro.IdMovimientoPro=dbo.MovimientosDetallePro.IdMovimientoPro and (IdTipoMovimientoProveedor<>'RV' AND IdTipoMovimientoProveedor<>'VP') and dbo.movimientosdetallepro.IdCuentaGastos<>43 and dbo.movimientosdetallepro.IdCuentaGastos<>48 group by  razonsocial, dbo.cuentasgastos.Descripcion order by dbo.cuentasgastos.Descripcion asc, RazonSocial asc";

fb($sqlGastos);

$stmt = sqlsrv_query( $mssql, $sqlGastos);


$tabla = "";$a=0;$q=0;
$sumaNoGravado=$sumaFletes=$sumaGastos=$sumaLubricantes=$sumaMercaderias=$sumaCombustibles=0;
while($fila = sqlsrv_fetch_array($stmt)){
    if(!isset($encabezado)){
        //$tabla.="<tr><td colspan=7>&nbsp;</td></tr>";
        //$tabla .= "<tr><td colspan='8'><b>".substr($fila['numeroFactura'],-9)." | ".substr($fila['periodo'],4).'/'.substr($fila['periodo'],0,4)."$socio</b> <a href='/ypf/cargaMovistar.php?id=$fila[idFacturaRecibida]'><i class='glyphicon glyphicon-pencil'></i></a></td></tr>";
        $encabezado=$fila['CuentaGasto'];
    } elseif($encabezado<>$fila['CuentaGasto']){
         $tabla.="<tr style='font-weight:bold' class='alert alert-success'><td>Total $encabezado</td><td>".(($sumaMercaderias<>0)?sprintf("%01.2f",$sumaMercaderias):'')."</td><td>".(($sumaGastos<>0)?sprintf("%01.2f",$sumaGastos):'')."</td><td>".(($sumaNoGravado<>0)?sprintf("%01.2f",$sumaNoGravado):'')."</td><td>".(($sumaLubricantes<>0)?sprintf("%01.2f",$sumaLubricantes):'')."</td><td>".(($sumaFletes<>0)?sprintf("%01.2f",$sumaFletes):'')."</td></tr><tr><td colspan='6'><br/></td></tr>";
                 //. "<tr><td colspan=7>&nbsp;</td></tr>";
         $sumaNoGravado=$sumaFletes=$sumaGastos=$sumaLubricantes=$sumaMercaderias=$sumaCombustibles=0;
         $encabezado=$fila['CuentaGasto'];
    }
    $ultima = "<tr style='font-weight:bold' class='alert alert-success'><td>Total $encabezado</td>
    <td>".(($sumaMercaderias<>0)?sprintf("%01.2f",$sumaMercaderias):'')."</td>
    <td>".(($sumaGastos<>0)?sprintf("%01.2f",$sumaGastos):'')."</td>
    <td>".(($sumaNoGravado<>0)?sprintf("%01.2f",$sumaNoGravado):'')."</td>
    <td>".(($sumaLubricantes<>0)?sprintf("%01.2f",$sumaLubricantes):'')."</td>
    <td>".(($sumaFletes<>0)?sprintf("%01.2f",$sumaFletes):'')."</td>
    </tr>";
    $Mercaderias = ($fila['Mercaderias']==$fila['resumenRenglon'])?$fila['Mercaderias']:$fila['resumenRenglon'];
    $tabla .= "<tr><td>$fila[razonsocial]</td><td>".(($Mercaderias<>0)?sprintf("%01.2f", ($Mercaderias)):'')."</td><td>".(($fila['Gastos']<>0)?sprintf("%01.2f", ($fila['Gastos'])):'')."</td><td>".(($fila['NoGravado']<>0)?sprintf("%01.2f", ($fila['NoGravado'])):'')."</td><td>".(($fila['Lubricantes']<>0)?sprintf("%01.2f", ($fila['Lubricantes'])):'')."</td><td>".(($fila['Fletes']<>0)?sprintf("%01.2f", ($fila['Fletes'])):'')."</td></tr>";
    
    $sumaNoGravado+=$fila['NoGravado'];
    $sumaMercaderias+=($fila['Mercaderias']==$fila['resumenRenglon'])?$fila['Mercaderias']:$fila['resumenRenglon'];
    //$sumaMercaderias+=$fila['Mercaderias'];
    $sumaCombustibles+=$fila['Combustibles'];
    $sumaLubricantes+=$fila['Lubricantes'];
    $sumaFletes+=$fila['Fletes'];
    $sumaGastos+=$fila['Gastos'];
    // <td><input type='text' name='ingresosBrutos[]' class='input-sm' value='0' required='required' pattern='[0-9\.]{1,}' maxlength='5'/></td>
}
echo $tabla.$ultima;
?>
