<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
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
    <link href="css/print.css" rel="stylesheet" type="text/css" media="print"/>

  </head>
  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
    <div class="container">
		<!-- Example row of columns -->
		<div class="row">
			<div class="col-md-12">
				<h2>Facturas por diferencia<span class='sh2'><?php echo date("d/m/y H:i:s"); $inicio=time();?></span></h2>
			</div>
		</div>
		<div class='row'>
			<div class='col-md-12'>
				<?php
				
				echo "<div id='libroDiario'>";
				$fila = 1;
				$numeroAsiento=0;
				$debeTotal = $haberTotal = 0;
				$conceptoAsiento=array();
				$arrayCSV = array("LibroDiario-1ra2012", "LibroDiario-2da2012");
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
						if($datos[5]<>"Transf. de PLAYA a Tesoreria"){
							$asientoActual = $datos[1];
							if($numeroAsiento<>$asientoActual){
								$asientoAnterior = ($numeroAsiento>0)?$numeroAsiento:false;
								$numeroAsiento = $asientoActual;

								$fechaAsiento = $datos[0];

								if($asientoAnterior){
									echo "<div class='encabezaAsiento'><b>Nº $asientoAnterior</b> || $fechaAsiento - $modeloResumen[$asientoAnterior]</div>";
									// imprime asiento anterior
									asort($debe[$asientoAnterior]);
									foreach($debe[$asientoAnterior] as $codigo => $monto){
										$monto = sprintf("%.2f",$monto);
										$monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
										echo "<div class='fila'><div class='cuentaD'>($codigo) $cuenta[$codigo]</div><div class='debe'>$monto</div><div class='haber'>&nbsp;</div></div>";
									}
									asort($haber[$asientoAnterior]);
									foreach($haber[$asientoAnterior] as $codigo => $monto){
										$monto = sprintf("%.2f",$monto);
										$monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
										echo "<div class='fila'><div class='cuentaH'><i>a</i> ($codigo) $cuenta[$codigo]</div><div class='debe'>&nbsp;</div><div class='haber'>$monto</div></div>";
									}
									echo "<div class='fila'><div class='cuentaH'>&nbsp;</div><div class='debe cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$debeAsiento[$asientoAnterior])), 2, ',', '.')."</div><div class='haber cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$haberAsiento[$asientoAnterior])), 2, ',', '.')."</div></div>";
									// echo "<tr class='cierraAsiento'><td colspan='3'>";
									// foreach($conceptoAsiento as $concepto){echo $concepto.'; ';}
									// echo "</td></tr>";
									$debeTotal+=$debeAsiento[$asientoAnterior];
									$haberTotal+=$haberAsiento[$asientoAnterior];
									unset($debe[$asientoAnterior], $haber[$asientoAnterior], $conceptoAsiento[$asientoAnterior], $debeAsiento[$asientoAnterior], $haberAsiento[$asientoAnterior]);
									$debeAsiento[$asientoActual]=$haberAsiento[$asientoActual]=0;
								}else{
									$debeAsiento[$asientoActual]=$haberAsiento[$asientoActual]=0;
								}
								// arranca asiento
								if($datos[5]<>"Transf. de PLAYA a Tesoreria"){
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
								if($datos[5]<>"Transf. de PLAYA a Tesoreria"){
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
					echo "<div class='encabezaAsiento'><b>Nº $asientoActual</b> || $fechaAsiento - $modeloResumen[$asientoActual]</div>";
					// imprime asiento anterior
					foreach($debe[$asientoActual] as $codigo => $monto){
						$monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
						echo "<div class='fila'><div class='cuentaD'>($codigo) $cuenta[$codigo]</div><div class='debe'>$monto</div><div class='haber'>&nbsp;</div></div>";
					}
					foreach($haber[$asientoActual] as $codigo => $monto){
						$monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
						echo "<div class='fila'><div class='cuentaH'><i>a</i> ($codigo) $cuenta[$codigo]</div><div class='debe'>&nbsp;</div><div class='haber'>$monto</div></div>";
					}
					echo "<div class='fila'><div class='cuentaH'>&nbsp;</div><div class='debe cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$debeAsiento[$asientoActual])), 2, ',', '.')."</div><div class='haber cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$haberAsiento[$asientoActual])), 2, ',', '.')."</div></div>";
					// echo "<tr class='cierraAsiento'><td colspan='3'>";
					// foreach($conceptoAsiento as $concepto){echo $concepto.'; ';}
					// echo "</td></tr>";
					unset($debe, $haber, $conceptoAsiento);
				}
				echo "<div class='fila cierraAsiento'><div class='cuentaH'>Total</div><div class='debe cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$debeTotal)), 2, ',', '.')."</div><div class='haber cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$haberTotal)), 2, ',', '.')."</div></div>";
				echo"</div><br/><br/>";
				//echo date("d/m/y H:i:s"); echo time()-$inicio." segs  || $tiempo";
				?>
			</div>
		</div>
	<!--	
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?> -->
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
	</script>
  </body>
</html>
