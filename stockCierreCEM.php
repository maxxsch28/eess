<?php
include('include/inicia.php');
$titulo="";

function muestraDetallesTanquesTelemedidos(){
    global $mssql, $articulo, $classArticulo, $mysqli, $CFG;
	$tablaTanques="";

    $sqlTanques = "select Capacidad, idArticulo, numero, idTanque from dbo.tanques order by numero;";
	$stmt = sqlsrv_query($mssql, $sqlTanques);
	if( $stmt === false ){
		 echo "Error in executing query.</br>";
		 die( print_r( sqlsrv_errors(), true));
	}
    while($tanque = sqlsrv_fetch_array($stmt)){
      $sqlTelemedicion = "SELECT TOP 1 Litros, NivelAgua, Nivel from dbo.tanquesmediciones WHERE idTanque=$tanque[idTanque] ORDER BY LastUpdated DESC";
      $stmtTelemedicion = sqlsrv_query($mssql, $sqlTelemedicion);
      if( $stmt === false ){
        echo "Error in executing query.</br>";
        die( print_r( sqlsrv_errors(), true));
      }
      $telemedido[$tanque[3]] = sqlsrv_fetch_array($stmtTelemedicion);
      
      $stockActual = $telemedido[$tanque['idTanque']]['Litros'];
      
      // arreglo para tomar tanques desde milimetros para super y euro
      // 18/8/2016
      $sqlConversion = "SELECT tq$tanque[3] FROM `cierres_tanques_equivalencias` WHERE mm=".round($telemedido[$tanque['idTanque']]['Nivel'],0).";";
      //fb($sqlConversion);
      //fb($telemedido);
      $result = $mysqli->query($sqlConversion);
      $litrosDesdeMM = $result->fetch_assoc();
      $tq = "tq$tanque[3]";
      
      //var_dump($litrosDesdeMM[$tq]);
      if((in_array($tanque[3], $CFG->tanquesATomarMilimetrosDesdeTablas))){
        $telemedido[$tanque[3]][0] = $litrosDesdeMM[$tq];
        $stockActual   = $litrosDesdeMM[$tq];
      }
      // fin 18/8/2016
      
      $disponible = $tanque['Capacidad'] - $stockActual;
      $porcentajeOcupacion = $stockActual / $tanque['Capacidad'] * 100;
      if(($porcentajeOcupacion)<10){
        $classNoVentas=' class="noVentas"';}
      else {
        $classNoVentas='';}
      if($porcentajeOcupacion<25){$classAlerta='progress-bar-danger';}
      elseif($porcentajeOcupacion<50){$classAlerta='progress-bar-warning';}
      elseif($porcentajeOcupacion<75){$classAlerta='progress-bar-info';}
      else {$classAlerta='progress-bar-success';}
      
      

      $tablaTanques.="<tr$classNoVentas>"
      . "<td class='alert alert-{$classArticulo[$tanque['idArticulo']]}'>".$articulo[$tanque['idArticulo']]." <span class='badge '>$tanque[idTanque]</span></td>"
      ."<td>".sprintf('%01.2f',$stockActual)."</td>"
      ."<td colspan='2'><div class='progress' style='margin-bottom: 0;'><div class='progress-bar $classAlerta  progress-bar-striped active' role='progressbar' aria-valuenow='$porcentajeOcupacion' aria-valuemin='0' aria-valuemax='100' style='width: $porcentajeOcupacion%;'>".round($porcentajeOcupacion,0)."%</div>".round($disponible,0)."</div></td>"
      //. "<td><span class='$classAlerta'>".round($porcentajeOcupacion,0)."%</span></td>"
      . "</tr>";
      if(!isset($combustible[$tanque['idArticulo']])){
        $combustible[$tanque['idArticulo']]=$tanque;
        $combustible[$tanque['idArticulo']]['Medicion']=$stockActual;
        $combustible[$tanque['idArticulo']]['Capacidad']=$tanque['Capacidad'];
        $combustible[$tanque['idArticulo']]['Disponible']=$disponible;
      } else {
        $combustible[$tanque['idArticulo']]['Medicion']+=$stockActual;
        $combustible[$tanque['idArticulo']]['Capacidad']+=$tanque['Capacidad'];
        $combustible[$tanque['idArticulo']]['Disponible']+=$disponible;
      }
    }
	//<span class='sh2'>Turno $dia a las $hora</span>
	$detalleTanques = "<table class='table'>
			<thead><tr><th></th><th>Actual</th><th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;                      &nbsp;</th><th>Vacío</th></tr></thead>
			<tbody>
				$tablaTanques
			</tbody>	
		</table>";
	echo trim($detalleTanques);
    //echo microtime()-$a;
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Estado tanques para cierre 22hs CEM | YPF</title>
    <?php include ('/include/head.php');?>
    <link rel="stylesheet" href="css/jquery.modal.css" type="text/css" media="screen" />
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
    <link rel="stylesheet" href="css/print.css" type="text/css" media="print"/>
  </head>
  <body>
    <?php if(!isset($_GET['soloComb'])){include("include/menuSuperior.php");} ?>
    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div class='col-md-5'>
          <div class="panel panel-primary" id='combustibles'>
          <div class="panel-heading">
            <h3 class="panel-title">Detalle tanques (<?php echo date('H:i:s - d/m/Y')?>)</h3>
          </div>
          <div class="panel-body gris" id="panelDetalle">
            <?php $b=microtime();muestraDetallesTanquesTelemedidos();?>
            <?php //muestraDetalleTanques()?>
          </div>
        </div></div>
        </div>
        <?php include ('include/footer.php')?>
    </div> <!-- /container -->
	<?php include('include/termina.php');?>
  </body>
</html>
