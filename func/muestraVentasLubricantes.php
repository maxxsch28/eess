<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$limit=11;
//$offset=0;
//$ultimoMes = date("Y-m-d", strtotime("+1 month", strtotime($desde)));
//for($i=12;$i>0;$i++){
//    $hasta = date("Y-m-d", strtotime("+1 month", strtotime($desde)));
//    
//}
if(!isset($_SESSION['ventasLubricantes'])||1){
for ($i = 10; $i > 0; $i--) {
    $desde = date("Ym01", mktime(0, 0, 0, date("m")-$i, date("d"),   date("Y")));
    $hasta = date("Ymd", mktime(0, 0, 0, date("m")-($i-1), '01',   date("Y"))-1);
//    echo "desde $desde hasta $hasta<br><br><br>";

    $sqlAsientos = trim("SELECT MovimientosDetalleFac.IdArticulo, Cantidad, Precio, (Cantidad * Precio) as Facturado, MovimientosDetalleFac.IdCierreTurno, Codigo, Descripcion, IdGrupoDescuento, IdEmpleado2, IdEmpleado3, Precio, dbo.CierresTurno.Fecha, dbo.MovimientosFac.IdTipoMovimiento, dbo.CierresTurno.IdCierreTurno, DATEPART(hh, dbo.CierresTurno.Fecha) as hora, IdGrupoDescuento "
            . "FROM dbo.MovimientosDetalleFac, dbo.Articulos, dbo.MovimientosFac, dbo.CierresTurno "
            . "WHERE  (IdCliente NOT IN (1993) OR IdCliente is null) AND dbo.Articulos.IdGrupoArticulo=1 AND dbo.MovimientosDetalleFac.IdArticulo=dbo.Articulos.IdArticulo AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND dbo.MovimientosFac.Fecha>='".$desde."' AND dbo.MovimientosFac.Fecha<='".$hasta."' AND dbo.CierresTurno.IdCierreTurno=dbo.MovimientosDetalleFac.IdCierreTurno AND dbo.CierresTurno.IdCierreTurno<>3227 AND descripcion LIKE ('%LT%')"
            . "ORDER BY dbo.CierresTurno.IdCierreTurno, IdEmpleado2, IdEmpleado3,  MovimientosDetalleFac.IdArticulo;");
    //echo $sqlAsientos;
    
    $stmt = odbc_exec( $mssql, $sqlAsientos);
    if( $stmt === false ){
         echo "1. Error in executing query.</br>$sqlAsientos<br/>";
         die( print_r( sqlsrv_errors(), true));
    }
    $b = date("Ym", mktime(0, 0, 0, date("m")-$i, date("d"),   date("Y")));
    $_SESSION['ventasLubricantes'][$b]['lts_elaion']=0;
    $_SESSION['ventasLubricantes'][$b]['lts_resto']=0;
    $_SESSION['ventasLubricantes'][$b]['elaion']=0;
    $_SESSION['ventasLubricantes'][$b]['resto']=0;
    $_SESSION['ventasLubricantes'][$b]['total']=0;
    $_SESSION['ventasLubricantes'][$b]['porcentaje']=0;
    $_SESSION['ventasLubricantes'][$b]['lts_porcentaje']=0;
    while($rowAsientos = odbc_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
        if($rowAsientos['IdGrupoDescuento']>0 || strpos($rowAsientos['Descripcion'], 'ELAION')){
            $_SESSION['ventasLubricantes'][$b]['lts_elaion']+=(strpos($rowAsientos['Descripcion'], '1 LT')||strpos($rowAsientos['Descripcion'], '1LT'))?1:(strpos($rowAsientos['Descripcion'], '4 LT')||strpos($rowAsientos['Descripcion'], '4LT'))?4:(strpos($rowAsientos['Descripcion'], '20 LT')||strpos($rowAsientos['Descripcion'], '20LT'))?20:0;
            $_SESSION['ventasLubricantes'][$b]['elaion']+=$rowAsientos['Facturado'];
        } else {
            $_SESSION['ventasLubricantes'][$b]['lts_resto']+=(strpos($rowAsientos['Descripcion'], '1 LT')||strpos($rowAsientos['Descripcion'], '1LT'))?1:(strpos($rowAsientos['Descripcion'], '4 LT')||strpos($rowAsientos['Descripcion'], '4LT'))?4:(strpos($rowAsientos['Descripcion'], '20 LT')||strpos($rowAsientos['Descripcion'], '20LT'))?20:0;
            $_SESSION['ventasLubricantes'][$b]['resto']+=$rowAsientos['Facturado'];
        }
        $_SESSION['ventasLubricantes'][$b]['total']+=$rowAsientos['Facturado'];
    }
    $_SESSION['ventasLubricantes'][$b]['porcentaje']=($_SESSION['ventasLubricantes'][$b]['elaion']/($_SESSION['ventasLubricantes'][$b]['elaion']+$_SESSION['ventasLubricantes'][$b]['resto']))*100;
    $_SESSION['ventasLubricantes'][$b]['lts_porcentaje']=($_SESSION['ventasLubricantes'][$b]['lts_elaion']/($_SESSION['ventasLubricantes'][$b]['lts_elaion']+$_SESSION['ventasLubricantes'][$b]['lts_resto']))*100;
}}

//print json_encode($_SESSION['ventasLubricantes']);
echo json_encode($_SESSION['ventasLubricantes']);
die;

?>