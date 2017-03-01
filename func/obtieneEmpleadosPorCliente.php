<?php
// obtieneEmpleadosPorCliente.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$fechaDesde='2014-01-01';
$tabla ="";
// sql
$sql1 = "SELECT razonsocial, idcliente FROM dbo.clientes WHERE razonsocial like ('$_POST[letra]%') order by razonsocial";
//echo $sql1;
$stmt = odbc_exec( $mssql, $sql1);
while($rowCliente = odbc_fetch_array($stmt)){
    $q=0;
    $sql2 = "select empleado, count(empleado) as q from dbo.Empleados, dbo.CierresTurno where (Empleados.IdEmpleado=CierresTurno.IdEmpleado2 OR Empleados.IdEmpleado=CierresTurno.IdEmpleado3 OR Empleados.IdEmpleado=CierresTurno.IdEmpleado4) and fecha>='$fechaDesde' and IdCierreTurno in (select IdCierreTurno from dbo.MovimientosFac, dbo.MovimientosDetalleFac where dbo.movimientosfac.idmovimientofac=dbo.MovimientosDetalleFac.IdMovimientoFac and IdCliente=$rowCliente[1] and Consignado=1 and idCondicionVenta=2) group by empleado order by q;";
    //echo $sql2.'<br>';
    $stmt2 = odbc_exec($mssql, $sql2);
    if(sqlsrv_has_rows($stmt2)>0){
        $tabla2 = "<tr><th colspan=2>$rowCliente[0] ".sqlsrv_num_rows($stmt2)."</th></tr>";
        while($rowEmpleado = odbc_fetch_array($stmt2)){
            $tabla2 .= "<tr><td align=left>$rowEmpleado[0]</td><td>$rowEmpleado[1]</td></tr>";
            $q+=$rowEmpleado[1];
        }
        if($q>=50)$tabla.=$tabla2;
        //$tabla .= "</td></tr>";
    } else { $tabla .= "";"<tr><th colspan=2>SIN DATOS $rowCliente[0]</th></tr>";}
}
echo $tabla;    
?>