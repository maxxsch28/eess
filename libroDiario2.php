<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$imprimeTotalAsiento=true;
$imprimeDetalleAsiento=true;
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Libro Diario</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
		body {
			padding-top: 60px;
			padding-bottom: 40px;
		}
    </style>
   
  </head>
  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
    <div class="container">
		<!-- Example row of columns -->
		<div class="row">
			<div class="col-md-12">
				<h2>Libro diario<span class='sh2'><?php echo date("d/m/y H:i:s"); $inicio=time();?></span></h2>
			</div>
		</div>
		<div class='row'>
			<div class='col-md-12'>
				<?php
				
				echo "<table id='libroDiario' class='table'><tbody>";
				$fila = 1;
				$numeroAsiento=0;
				$debeTotal = $haberTotal = 0;
				$conceptoAsiento=array();
				$arrayCSV = array("LibroDiario-1ra2012", "LibroDiario-2da2012");
				$arrayCSV = array("LibroDiario-2013");
				foreach($arrayCSV as $archivoCSV)
				if (($gestor = fopen("$archivoCSV.csv", "r")) !== FALSE) {
					$tiempo=time();
					while (($datos = fgetcsv($gestor, 0, ";")) !== FALSE) {
						// ["A"]=> string(5) "Fecha" 
						// ["B"]=> string(13) "NumeroAsiento" 
						// ["C"]=> string(8) "Concepto" 
						// ["D"]=> string(6) "Codigo" 
						// ["E"]=> string(11) "Descripcion" 
						// ["F"]=> string(7) "Detalle" 
						// ["G"]=> string(4) "Debe" 
						// ["H"]=> string(5) "Haber"
						if($datos[5]<>"Transf. de PLAYA a Tesoreria"&&$datos[5]<>"Transf. de SHOP a Tesoreria"){
							$asientoActual = $datos[1];
							if($numeroAsiento<>$asientoActual){
								$asientoAnterior = ($numeroAsiento>0)?$numeroAsiento:false;
								$numeroAsiento = $asientoActual;

								$fechaAsiento = $datos[0];

								if($asientoAnterior){
									echo "<tr class='encabezaAsiento'><td colspan='3'><b>Nº $asientoAnterior</b> || $fechaAsiento - $modeloResumen[$asientoAnterior]</td></tr>";
									// imprime asiento anterior
									if(isset($debe[$asientoAnterior])){//asort($debe[$asientoAnterior]);
									foreach($debe[$asientoAnterior] as $codigo => $monto){
										$monto = sprintf("%.2f",$monto);
										$monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
										echo "<tr class='fila'><td class='cuentaD'>($codigo) $cuenta[$codigo]</td><td class='debe'>$monto</td><td class='haber'>&nbsp;</td></tr>";
									}}
									if(isset($haber[$asientoAnterior])){//asort($haber[$asientoAnterior]);
									foreach($haber[$asientoAnterior] as $codigo => $monto){
										$monto = sprintf("%.2f",$monto);
										$monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
										echo "<tr class='fila'><td class='cuentaH'><i>a</i> ($codigo) $cuenta[$codigo]</td><td class='debe'>&nbsp;</td><td class='haber'>$monto</td></tr>";
									}}
									if($imprimeTotalAsiento)
									echo "<tr class='fila'><td class='cuentaH'>&nbsp;</td><td class='debe cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$debeAsiento[$asientoAnterior])), 2, ',', '.')."</td><td class='haber cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$haberAsiento[$asientoAnterior])), 2, ',', '.')."</td></tr>";
								
									if($imprimeDetalleAsiento&&false)
									if($modeloResumen[$asientoAnterior]<>'COBRANZAS CUENTA CORRIENTE'&&$modeloResumen[$asientoAnterior]<>'VENTAS AGRUPADAS MENSUAL'){
										echo "<tr><td colspan='3' class='cierraAsiento'>";
										//print_r($conceptoAsiento[$asientoAnterior]);
										$array = array_unique($conceptoAsiento[$asientoAnterior]);
										foreach($array as $concepto){echo $concepto.'; ';}
										echo "</td></tr>";
									}
									$debeTotal+=$debeAsiento[$asientoAnterior];
									$haberTotal+=$haberAsiento[$asientoAnterior];
									unset($debe[$asientoAnterior], $haber[$asientoAnterior], $conceptoAsiento[$asientoAnterior], $debeAsiento[$asientoAnterior], $haberAsiento[$asientoAnterior]);
									$debeAsiento[$asientoActual]=$haberAsiento[$asientoActual]=0;
								}else{
									$debeAsiento[$asientoActual]=$haberAsiento[$asientoActual]=0;
								}
								// arranca asiento
								if($datos[5]<>"Transf. de PLAYA a Tesoreria"&&$datos[5]<>"Transf. de SHOP a Tesoreria"){
									if($datos[6]<>0){
										$debe[$asientoActual][$datos[3]]=$datos[6];
										$debeAsiento[$asientoActual]+=$datos[6];
									}	
									if($datos[7]<>0){
										$haber[$asientoActual][$datos[3]]=$datos[7];
										$haberAsiento[$asientoActual]+=$datos[7];
									}	
									$conceptoAsiento[$asientoActual][] = $datos[5];
									$modeloResumen[$asientoActual] = $datos[2];
								}	
							} else {
								if($datos[5]<>"Transf. de PLAYA a Tesoreria"&&$datos[5]<>"Transf. de SHOP a Tesoreria"){
									if($datos[6]<>0){
										if(isset($debe[$asientoActual][$datos[3]])){
											$debe[$asientoActual][$datos[3]]+=floatval(str_replace(',','.',$datos[6]));
											$debeAsiento[$asientoActual]+=floatval(str_replace(',','.',$datos[6]));
										}else{
											$debe[$asientoActual][$datos[3]]=floatval(str_replace(',','.',$datos[6]));
											$debeAsiento[$asientoActual]+=floatval(str_replace(',','.',$datos[6]));
										}	
									}
									if($datos[7]<>0){
										if(isset($haber[$asientoActual][$datos[3]])){
											$haber[$asientoActual][$datos[3]]+=floatval(str_replace(',','.',$datos[7]));
											$haberAsiento[$asientoActual]+=floatval(str_replace(',','.',$datos[7]));
										}else{
											$haber[$asientoActual][$datos[3]]=floatval(str_replace(',','.',$datos[7]));
											$haberAsiento[$asientoActual]+=floatval(str_replace(',','.',$datos[7]));
										}
									}
									if(!in_array($datos[5], $conceptoAsiento))
										$conceptoAsiento[$asientoActual][]=$datos[5];
								}	
							}
							if(!isset($cuenta[$datos[3]]))$cuenta[$datos[3]]=$datos[4];
						}
					}
					fclose($gestor);
				}
				if(isset($fechaAsiento)){
					// imprime asiento anterior
					echo "<tr class='encabezaAsiento'><td colspan='3'><b>Nº $asientoActual</b> || $fechaAsiento - $modeloResumen[$asientoActual]</td></tr>";
					// imprime asiento anterior
					foreach($debe[$asientoActual] as $codigo => $monto){
						$monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
						echo "<tr class='fila'><td class='cuentaD'>($codigo) $cuenta[$codigo]</td><td class='debe'>$monto</td><td class='haber'>&nbsp;</td></tr>";
					}
					foreach($haber[$asientoActual] as $codigo => $monto){
						$monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
						echo "<tr class='fila'><td class='cuentaH'><i>a</i> ($codigo) $cuenta[$codigo]</td><td class='debe'>&nbsp;</td><td class='haber'>$monto</td></tr>";
					}
					echo "<tr class='fila'><td class='cuentaH'>&nbsp;</td><td class='debe cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$debeAsiento[$asientoActual])), 2, ',', '.')."</td><td class='haber cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$haberAsiento[$asientoActual])), 2, ',', '.')."</td></tr>";
					// echo "<tr class='cierraAsiento'><td colspan='3'>";
					// foreach($conceptoAsiento as $concepto){echo $concepto.'; ';}
					// echo "</td></tr>";
					unset($debe, $haber, $conceptoAsiento);
				}
				echo "<tr class='fila cierraAsiento'><td class='cuentaH'>Total</td><td class='debe cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$debeTotal)), 2, ',', '.')."</td><td class='haber cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$haberTotal)), 2, ',', '.')."</td></tr>";
				echo"</tbody></table><br/><br/>";
				//echo date("d/m/y H:i:s"); echo time()-$inicio." segs  || $tiempo";
				?>
			</div>
		</div>
	<!--	<hr>
		<footer>
			<p>&copy; Cooperativa de Transporte 2012</p>
		</footer>-->
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
	</script>
  </body>
</html>
