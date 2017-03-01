<?php
if(substr($_SERVER['HTTP_USER_AGENT'], 0,4)=='curl'){
  //lo llame desde cron
} else {
  $nivelRequerido = 4;
}
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');




// Grabo el estado de tanques a las 22 hs
// Saco el contenido del archivo de stock de teknivel
$output = shell_exec('copy "C:\\Program Files (x86)\\Teknivel\\Tnq\\stocks.txt" E:\\htdocs\\ypf\\tmp');
//var_dump($output);
$file = file_get_contents('tmp/stocks.txt');
//var_dump($file);
if(strpos($file, 'DESCARGA EN TANQUE')){
  $file3 = substr($file, 0, strpos($file, 'DESCARGA EN TANQUE'));
  $file2 = explode('Tanque', $file3);
} else {
  $file2 = explode('Tanque', $file);
}
//var_dump($file2);
$lecturaTanque = array();
foreach($tanques as $idTanque => $idArticulo){
  $lecturaTanque[$idTanque] = explode(',', $file2[$idTanque]);
  echo "Tanque $idTanque, {$lecturaTanque[$idTanque][8]} lts<br>";
  echo "Nivel tq $idTanque, {$lecturaTanque[$idTanque][5]} mm<br>";
  if($idTanque==3){
    // calculo litros en base a los milímetros
    $sqlConversion = "SELECT tq3 FROM `cierres_tanques_equivalencias` WHERE mm=".round($lecturaTanque[$idTanque][5],0).";";
    echo $sqlConversion;
    $result = $mysqli->query($sqlConversion);
    $litrosDesdeMM = $result->fetch_assoc();
    $lecturaTanque[$idTanque][8] = $litrosDesdeMM['tq3'];
  }
  if(!isset($tqs))$tqs = "'{$lecturaTanque[$idTanque][8]}'";
  else $tqs .= ", '{$lecturaTanque[$idTanque][8]}'";
  
}
//print_r($lecturaTanque);
$sqlCierreTanques = "INSERT INTO `pedidosypf`.`cierres_cem_tanques` (`fechaCarga`, `fechaCierre`, `turno`, `tq1`, `tq2`, `tq3`, `tq4`, `tq5`, `tq6`) VALUES (CURRENT_TIMESTAMP, '".date('Y-m-d 22:00:00')."', 'noche', $tqs);";
fb($sqlCierreTanques);
echo $sqlCierreTanques;
//$result = $mysqli->query($sqlCierreTanques);



$sqlVentas = "select IdArticulo, sum(AforadorElectronico) as Electronico, sum(AforadorMecanico) as Mecanico from dbo.CierresDetalleSurtidores, dbo.CierresSurtidores, dbo.mangueras where dbo.CierresSurtidores.IdCierreSurtidores=dbo.CierresDetalleSurtidores.IdCierreSurtidores AND dbo.mangueras.idmanguera=dbo.cierresdetallesurtidores.idManguera AND IdCierreTurno IN (5016, 5011) group by idarticulo";



// Saco el estado de los tanques al último cierre de las 22
$sqlMediciones = "select IdArticulo, SUM(medicion) from dbo.CierresDetalleTanques where IdCierreTurno=(select top 1 IdCierreTurno from dbo.CierresTurno where IdCaja=1 AND idCierreTurno<=(select top 1 idCierreturno FROM dbo.Cierresturno where DATEPART(hh, Fecha)>=21 AND DATEPART(hh, Fecha)<=22 order by Fecha desc) order by fecha desc) GROUP BY IdArticulo";
$stmt = odbc_exec( $mssql, $sqlMediciones);
while($rowMediciones = odbc_fetch_array($stmt)){
	$mediciones[$rowMediciones[0]]=$rowMediciones[1];
}

// selecciono los dos turnos de las 22 de ayer y antes de ayer
$sqlTurnos = "select top 2 IdCierreTurno, fecha from dbo.CierresTurno where IdCaja=1 AND idCierreTurno<=(select top 1 idCierreturno FROM dbo.Cierresturno where DATEPART(hh, Fecha)>=19 AND DATEPART(hh, Fecha)<=22 order by Fecha desc) AND  DATEPART(hh, Fecha)>=19 AND DATEPART(hh, Fecha)<=22 order by fecha desc";
$stmt = odbc_exec( $mssql, $sqlTurnos);
while($rowTurnos = odbc_fetch_array($stmt)){
  //print_r($rowTurnos);
    if(!isset($fechaCierre))$fechaCierre=$rowTurnos[1];
	$turnos[]=$rowTurnos[0];
}
//print_r($turnos);

// obtengo el estado de los aforadores al cierre de las 22 de ayer y antes de ayer
foreach($turnos as $idcierreturno){
    $sqlAforadores = "select IdArticulo, sum(AforadorElectronico) as Electronico, sum(AforadorMecanico) as Mecanico from dbo.CierresDetalleSurtidores, dbo.CierresSurtidores, dbo.mangueras where dbo.CierresSurtidores.IdCierreSurtidores=dbo.CierresDetalleSurtidores.IdCierreSurtidores AND dbo.mangueras.idmanguera=dbo.cierresdetallesurtidores.idManguera AND IdCierreTurno =$idcierreturno group by idarticulo";
    $stmt = odbc_exec( $mssql, $sqlAforadores);
    while($rowAforadores = odbc_fetch_array($stmt)){
        $electronicos[$idcierreturno][$rowAforadores[0]]=$rowAforadores[1];
        $mecanicos[$idcierreturno][$rowAforadores[0]]=$rowAforadores[2];
    }
    $sqlAforadores2 = "select dbo.CierresDetalleSurtidores.idManguera, AforadorElectronico as Electronico, AforadorMecanico as Mecanico from dbo.CierresDetalleSurtidores, dbo.CierresSurtidores, dbo.mangueras where dbo.CierresSurtidores.IdCierreSurtidores=dbo.CierresDetalleSurtidores.IdCierreSurtidores AND dbo.mangueras.idmanguera=dbo.cierresdetallesurtidores.idManguera AND IdCierreTurno =$idcierreturno";
    //echo $sqlAforadores2;
    $stmt2 = odbc_exec( $mssql, $sqlAforadores2);
    while($rowAforadores2 = odbc_fetch_array($stmt2)){
        $electronicos2[$idcierreturno][$rowAforadores2[0]]=$rowAforadores2[1];
        $mecanicos2[$idcierreturno][$rowAforadores2[0]]=$rowAforadores2[2];
    }
}
// para stock
$sqlGrabaAforadoresDiarios = "INSERT INTO cierres_cem_aforadores (fechaCarga, fechaCierre, ed1, ns1, ni1, ed2, ns2, ni2, ud3, ed4, ud5, ud6, ed7) values (now(), '".date_format($fechaCierre, 'Y-m-d H:i:s')."'";
foreach($electronicos2[$turnos[1]] as $pico => $aforador) {
  $sqlGrabaAforadoresDiarios .=", ".($electronicos2[$turnos[0]][$pico]-$aforador);
  echo "Calculo: $pico -> $aforador; ".($electronicos2[$turnos[0]][$pico]-$aforador)." lts<br/>";
}
$sqlGrabaAforadoresDiarios .=");";
//echo $sqlGrabaAforadoresDiarios;


// para cron
$sqlGrabaVentaDiaria = "INSERT INTO ventasDiarias (ed, ud, np, ns, fecha, diaSemana) VALUES (";
foreach($articulo as $idArticulo => $producto){
    $ventaElectronica[$idArticulo]=round($electronicos[$turnos[0]][$idArticulo]-$electronicos[$turnos[1]][$idArticulo],2);
    $ventaMecanica[$idArticulo]=round($mecanicos[$turnos[0]][$idArticulo]-$mecanicos[$turnos[1]][$idArticulo],2);
    $sqlGrabaVentaDiaria .= "$ventaElectronica[$idArticulo], ";
    if($ventaElectronica[$idArticulo]<>$ventaMecanica[$idArticulo])$existenDiferencias=true;
}
$sqlGrabaVentaDiaria.="'".date_format($fechaCierre, 'Y-m-d H:i:s')."', '".date_format($fechaCierre, 'N')."');";
//$result = $mysqli->query($sqlGrabaVentaDiaria);




// COMBUSTIBLES
$tabla=$tabla2="";
foreach($mediciones as $key => $litros){
    
	$limiteParaQuiebre	= ($key=='2069'||$key=='2068')?2400:1000;
	$classQuiebre		= ($litros<$limiteParaQuiebre)?' class="noVentas"':'';
	//$promedio			= ($litros<$limiteParaQuiebre)?'----':"(".round($estadoComb[$key]['stock']/$estadoComb[$key]['promedio'],1)." d.)";
    
    $tabla2.="<tr$classQuiebre>"
            . "<th width='18%'>".$articulo[$key]."</th>"
            . "<td>".$ventaElectronica[$key]." lts</td>"
            . "<td><span class=''>$mediciones[$key] lts</span></td>"
            . "<td>".((isset($existenDiferencias))?$ventaMecanica[$key].' lts':'')."</td>"
            . "</tr>";//<td>$stockCierre</td>
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Estado tanques | YPF</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
        body{
            margin: 50px auto;
        }
        .OPentregada{
            background-color:#fcf8e3;
        }
        #myModal2 table{
            background-color:#fff;
        }
    </style>
    <?php if(isset($_GET['soloComb'])){?><link href="css/graficobarras.css" rel="stylesheet" type="text/css" media="screen"/><?php }?>
	<link href="css/print.css" rel="stylesheet" type="text/css" media="print"/>
  </head>
  <body>
	<?php if(!isset($_GET['soloComb'])){include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');} ?>
    <div class="container">
		<!-- Example row of columns -->
		<div class="row">
                    <div class='col-md-5'>
			<div class="panel panel-primary" id='combustibles'>
                            <div class="panel-heading">
                                <h3 class="panel-title">APIES 1570 Combustibles al <?php echo date_format($fechaCierre, 'Y-m-d H:i:s'); ?></span></h3>
                              </div>
				 <div class="panel-body">
                    <table class='table'><thead><tr><th></th><th>Ventas</th><th>Mediciones</th><th><?php if(isset($existenDiferencias)){echo"VM";}?></th></tr></thead>
					<tbody>
						<?php echo $tabla2;?>
					</tbody>
				</table>
			</div>
			<!--<p><a class="btn" href="#" id='detallesTanques'>Detalle por tanques &raquo;</a></p>-->
			</div>
			</div>
		</div>

        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
    $(document).ready(function() {

       
    });
	</script>
  </body>
</html>
