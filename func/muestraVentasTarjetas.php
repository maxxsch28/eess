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
if(!isset($_POST['mes']))$_POST['mes']=date("Ym");
$tarjetaDebito = array('VISA DEBITO', 'MAESTRO', 'S VISA DEBITO', 'S MAESTRO');
$hasta = date("Y-m-d", strtotime("+1 month", strtotime($_POST['mes'].'01')));
$inicia = date("Y-m-d", strtotime("+0 month", strtotime($_POST['mes'].'01')));

if(!isset($_SESSION['ventasTarjetas'])||1){
unset($_SESSION['ventasTarjetas']);
$a=0;
for ($i = 12; $i > 0; $i--) {
    $a++;
   // $desde = date("Ym01", mktime(0, 0, 0, date("m")-$i, date("d"),   date("Y")));
   // $hasta = date("Ymd", mktime(0, 0, 0, date("m")-($i-1), '01',   date("Y"))-1);
    
    $desde = date("Y-m-d", strtotime("-$i month", strtotime($inicia)));
    $hasta = date("Y-m-d", strtotime("+1 month", strtotime($desde)));
    
    
    $sqlAsientos = trim("select dbo.TarjetasCredito.Nombre, SUM(Importe) as VentasTarjeta from dbo.LotesTarjetasCredito, dbo.TarjetasCredito where Importe >0 and dbo.TarjetasCredito.IdTarjeta=dbo.LotesTarjetasCredito.IdTarjeta  AND Fecha>='$desde' AND Fecha<'$hasta' GROUP BY dbo.TarjetasCredito.Nombre ;");
//echo $sqlAsientos."\n"    ;
    
    $sqlFacturas = trim("select sum(total) as tot from dbo.movimientosfac where idtipomovimiento NOT IN ('NCA','NCB','NLP') AND Fecha>='$desde' AND Fecha<'$hasta'");
    $sqlNotasCredito = trim("select sum(total) as tot from dbo.movimientosfac where idtipomovimiento IN ('NCA','NCB') AND Fecha>='$desde' AND Fecha<'$hasta'");
    $stmtFac = odbc_exec2( $mssql, $sqlFacturas, __LINE__, __FILE__);
    $stmtNC  = odbc_exec2( $mssql, $sqlNotasCredito, __LINE__, __FILE__);
    $rowFac = sqlsrv_fetch_array($stmtFac, SQLSRV_FETCH_ASSOC);
    $rowNC = sqlsrv_fetch_array($stmtNC, SQLSRV_FETCH_ASSOC);
    
    $stmt = odbc_exec2( $mssql, $sqlAsientos, __LINE__, __FILE__);

    //$b = date("Y/m/d", mktime(0, 0, 0, date("m")-$i, 1,   date("Y")));
    $b = date('Y-m-d',mktime(1,1,1,date("m")-$i, 1, date('Y'))); 
    $_SESSION['ventasTarjetas'][$b]['totalVentasMes'] = round($rowFac['tot']-$rowNC['tot'],2);
    $_SESSION['ventasTarjetas'][$b]['debito']=0;
    $_SESSION['ventasTarjetas'][$b]['credito']=0;
    $_SESSION['ventasTarjetas'][$b]['totalTarjetas']=0;
    $_SESSION['ventasTarjetas'][$b]['porcentaje']=0;
    $_SESSION['ventasTarjetas'][$b]['porcentajeSobreFacturacion']=0;
    while($rowAsientos = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
        if(in_array($rowAsientos['Nombre'], $tarjetaDebito)){
            $_SESSION['ventasTarjetas'][$b]['debito']+=$rowAsientos['VentasTarjeta'];
        } else {
            $_SESSION['ventasTarjetas'][$b]['credito']+=$rowAsientos['VentasTarjeta'];
        }
        $_SESSION['ventasTarjetas'][$b]['totalTarjetas']+=$rowAsientos['VentasTarjeta'];
    }
    $_SESSION['ventasTarjetas'][$b]['porcentaje']=round(($_SESSION['ventasTarjetas'][$b]['credito']/$_SESSION['ventasTarjetas'][$b]['totalTarjetas'])*100,2);
    
    $_SESSION['ventasTarjetas'][$b]['porcentajeSobreFacturacion']=round((($_SESSION['ventasTarjetas'][$b]['totalTarjetas']/$_SESSION['ventasTarjetas'][$b]['totalVentasMes']))*100,2);
    
    $_SESSION['ventasTarjetas'][$b]['porcentajeCreditoSobreFacturacion']=round((($_SESSION['ventasTarjetas'][$b]['credito']/$_SESSION['ventasTarjetas'][$b]['totalVentasMes']))*100,2);
}}
//print_r($_SESSION['ventasTarjetas']);
ksort($_SESSION['ventasTarjetas']);
echo json_encode($_SESSION['ventasTarjetas']);
die;
/*{
    "201310":{"debito":427972.87,"credito":55116.88,"total":483089.75,"porcentaje":12.878592047201},
    "201311":{"debito":388653.49,"credito":94062.95,"total":482716.44,"porcentaje":24.202265622264},
    "201312":{"debito":613384.12,"credito":124821.46,"total":738205.58,"porcentaje":20.349639961335},
    "201401":{"debito":471394.46,"credito":110619.07,"total":582013.53,"porcentaje":23.46634918026},
    "201403":{"debito":586914.18,"credito":179767.75,"total":766681.93,"porcentaje":30.629307678339},
    "201404":{"debito":545134.81,"credito":147174.65,"total":692309.46,"porcentaje":26.997844808333},
    "201405":{"debito":629449.83,"credito":163444.03,"total":792893.86,"porcentaje":25.966172713082},
    "201406":{"debito":590166.05,"credito":185980.02,"total":776146.07,"porcentaje":31.513168200712},
    "201407":{"debito":618807.13,"credito":196850.75,"total":815657.88,"porcentaje":31.811325444812},
    "201309":{"debito":397818.05,"credito":13386.04,"total":411204.09,"porcentaje":3.3648649175169}
    }*/
/*
﻿{
 * "2013-09-01":{"totalVentasMes":2916650.71,"debito":397818.05,"credito":13386.04,"totalTarjetas":411204.09,"porcentaje":3.36,"porcentajeSobreFacturacion":14.1,"porcentajeCreditoSobreFacturacion":0.46},
 * "2013-10-01":{"totalVentasMes":3160338.75,"debito":427972.87,"credito":55116.88,"totalTarjetas":483089.75,"porcentaje":12.88,"porcentajeSobreFacturacion":15.29,"porcentajeCreditoSobreFacturacion":1.74},
 * "2013-11-01":{"totalVentasMes":3214132.31,"debito":388653.49,"credito":94062.95,"totalTarjetas":482716.44,"porcentaje":24.2,"porcentajeSobreFacturacion":15.02,"porcentajeCreditoSobreFacturacion":2.93},
 * "2013-12-01":{"totalVentasMes":4461321.93,"debito":613384.12,"credito":124821.46,"totalTarjetas":738205.58,"porcentaje":20.35,"porcentajeSobreFacturacion":16.55,"porcentajeCreditoSobreFacturacion":2.8},
 * "2014-01-01":{"totalVentasMes":4035479.66,"debito":471394.46,"credito":110619.07,"totalTarjetas":582013.53,"porcentaje":23.47,"porcentajeSobreFacturacion":14.42,"porcentajeCreditoSobreFacturacion":2.74},
 * "2014-02-01":{"totalVentasMes":4416146.12,"debito":455950.58,"credito":131831.82,"totalTarjetas":587782.4,"porcentaje":28.91,"porcentajeSobreFacturacion":13.31,"porcentajeCreditoSobreFacturacion":2.99},
 * "2014-03-01":{"totalVentasMes":4744066.4,"debito":586914.18,"credito":179767.75,"totalTarjetas":766681.93,"porcentaje":30.63,"porcentajeSobreFacturacion":16.16,"porcentajeCreditoSobreFacturacion":3.79},
 * "2014-04-01":{"totalVentasMes":4674199.86,"debito":545134.81,"credito":147174.65,"totalTarjetas":692309.46,"porcentaje":27,"porcentajeSobreFacturacion":14.81,"porcentajeCreditoSobreFacturacion":3.15},
 * "2014-05-01":{"totalVentasMes":5012521.1,"debito":629449.83,"credito":163444.03,"totalTarjetas":792893.86,"porcentaje":25.97,"porcentajeSobreFacturacion":15.82,"porcentajeCreditoSobreFacturacion":3.26},
 * "2014-06-01":{"totalVentasMes":5711501.88,"debito":590166.05,"credito":185980.02,"totalTarjetas":776146.07,"porcentaje":31.51,"porcentajeSobreFacturacion":13.59,"porcentajeCreditoSobreFacturacion":3.26},
 * "2014-07-01":{"totalVentasMes":4736774.95,"debito":618807.13,"credito":197200.75,"totalTarjetas":816007.88,"porcentaje":31.87,"porcentajeSobreFacturacion":17.23,"porcentajeCreditoSobreFacturacion":4.16},
 * 
 * 1
 * "2014-08-01":{"totalVentasMes":5280022.32,"debito":801433.42,"credito":264607.96,"totalTarjetas":1066041.38,"porcentaje":33.02,"porcentajeSobreFacturacion":20.19,"porcentajeCreditoSobreFacturacion":5.01}}
 
 */
?>
