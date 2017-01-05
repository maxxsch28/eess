<?php
$nivelRequerido = 4;
include('include/inicia.php');

$sqlOrdenes = "SELECT DISTINCT(ordenes.idOrden), op, DATE_FORMAT(fechaPedido, '%e/%m/%Y') as fPedido, fechaDespacho FROM ordenes, pedidos WHERE ordenes.idOrden=pedidos.idOrden ORDER BY fechaPedido DESC, idOrden DESC LIMIT 10;";
/* Select queries return a resultset */
$tabla="";
if ($result = $mysqli->query($sqlOrdenes)){
	//idOrden 	op 	fechaPedido Descendente 	fechaDespacho 	idPedido 	idOrden 	idArticulo 	litrosPedidos 	litrosEntregados 	idEstado 	idOrden 	fechaEstado 	estado
	/* free result set */
	while ($fila = $result->fetch_assoc()) {
		$sqlEstado = "SELECT estado, fechaEstado FROM estados WHERE idOrden=$fila[idOrden] ORDER BY idEstado DESC LIMIT 1";
		$resultEstado = $mysqli->query($sqlEstado);
		if($resultEstado){
			$status = $resultEstado->fetch_assoc();
			$estado = $status['estado'];
		} else {
			$estado = 'S/D';
		}		
		//Array ( [idOrden] => 7 [op] => 4294967295 [fechaPedido] => 2012-05-29 [fechaDespacho] => 0000-00-00 [fechaEstado] => 2012-05-29 [estado] => ) 
		$op		= ($fila['op']<>'')?$fila['op']:$fila['idOrden'];
		$tabla .= "<tr><td><a href='cargaOP.php?id=$fila[idOrden]'>$op</a></td><td>$fila[fPedido]</td><td>$estado</td><td><a href='#' class='badge badge-info infoOP' rel='popover' title='Orden $op' id='op$fila[idOrden]'>i</a></td></tr>";
	}
	$result->close();
}
$cargadoEnYPF=false;
$actualiza=false;
if(isset($_GET['id'])){
	// carga los datos de esta orden
	$sqlOrden = "SELECT op, DATE_FORMAT(fechaPedido, '%e/%m/%Y') as fechaPedido, DATE_FORMAT(fechaDespacho, '%e/%m/%Y') as fechaDespacho, DATE_FORMAT(fechaEstimada, '%e/%m/%Y') as fechaEstimada, idArticulo, litrosPedidos, litrosEntregados FROM ordenes, pedidos WHERE ordenes.idOrden=pedidos.idOrden AND ordenes.idOrden='$_GET[id]';";
	$result = $mysqli->query($sqlOrden);
	while($orden = $result->fetch_assoc()){
		$actualiza=true;
		$fechaPedido  						= $orden['fechaPedido'];
		$fechaEstimada						= $orden['fechaEstimada'];
		$fechaDespacho						= ($orden['fechaDespacho']<>'0/00/0000')?$orden['fechaDespacho']:false;
		$op									= $orden['op'];							
		$ltsPedido[$orden['idArticulo']]	= (1000*(round($orden['litrosPedidos']*1.25/1000)));			
		if($orden['op']<>'0000000000'){
			$ltsEntregado[$orden['idArticulo']]	= (1000*(round($orden['litrosEntregados']/1000)));
			$cargadoEnYPF=1;
		}	
	}
	$sqlEstado = "SELECT estado FROM estados WHERE idOrden='$_GET[id]' ORDER BY fechaEstado DESC, idEstado DESC LIMIT 1";
	$result = $mysqli->query($sqlEstado);
	$status = $result->fetch_assoc();
	$estado = $status['estado'];
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Descarga camión | YPF</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
  </head>

  <body>
	<?php include('include/menuSuperior.php') ?>
	<div class="container">
		<div class='row'>
			<div class="col-md-6">
				<h2>Recepción de combustible<h2>
				<form name='recibeCamion' id='recibeCamion' class='form-horizontal well'>
				<input type='hidden' name='inputOculto' id='inputOculto' value=''/>
					<?php
					$sql1 = "SELECT * FROM ordenes WHERE entregado=0 AND ultimoEstado IN ('Despachado', 'Pedido Ruteado', 'Despachado Parcialmente', 'Pedido Ruteado Parcialmente') ORDER BY remito desc, op DESC LIMIT 5";
					$res1 = $mysqli->query($sql1);
					while($orden = $res1->fetch_assoc()){
						$remito = '000'.substr($orden['remito'],0,1).'-'.substr($orden['remito'],1);
						echo "<div id='op$orden[idOrden]' class='eligeOP'><legend>OP $orden[op]".(($orden['remito']<>0)?" | <span class='badge badge-warning'><b>Remito Nº $remito</b></span>":'')."<button class='btn btn-primary btn-sm btn-recibir' id='x$orden[idOrden]'>Recibir &raquo;</button></legend><fieldset>";
						$sql2 = "SELECT * FROM pedidos WHERE idOrden='$orden[idOrden]'";
						$res2 = $mysqli->query($sql2);
						while($pedido = $res2->fetch_assoc()){
							echo "<div class='form-group'><label for='p$pedido[idPedido]' class='control-label'>{$articulo[$pedido['idArticulo']]}</label><div class='controls'><div class='input-group'><input type='text' name='p$pedido[idPedido]' id='p$pedido[idPedido]' class='input-sm asignacion' value='".(1000*(round($pedido['litrosDespachados']/1000)))."' disabled='disabled'/><span class='input-group-addon'>lts.</span></div></div></div>";
						}
						echo "</fieldset><br/></div>";
					}
					if(!isset($remito)){
						echo "<h3>No existen ordenes vigentes</h3>";
					}
					?>
				</form>
			</div>
			<div class="col-md-6" id='ultimasOrdenes'>
				<h2>Litros descargados</h2>
				<form name='asignaTanques' id='asignaTanques' class='form-horizontal'>
				<input type='hidden' name='totalCalculado' id='totalCalculado' value='0'/>
				<input type='hidden' name='idOrden' id='idOrden' value='0'/>
				<div id='msgBox'></div>
				<table class='table' id='tanques'>
					<thead><tr><th width='60%'>&nbsp;</th><th width='40%'>&nbsp;</th></tr></thead>
					<tbody>
					</tbody>
				</table>
				</form>
			</div>
		</div>
        <?php include ('include/footer.php')?>

    </div> <!-- /container -->
	<?php include('include/termina.php');?>
	<script>
		$(document).ready(function() {
			$(".btn-recibir").click(function() {
				//event.preventDefault();
				$('.eligeOP').unblock();
				$('.btn-recibir').removeClass('recibiendo').html('Recibir &raquo;');
				$(this).addClass('recibiendo').html('&raquo;');
				$('#idOrden').val($(this).attr('id'));
				$.get('func/cargaDistribucionTanques.php?idOrden='+$(this).attr('id'), function(data) {
					$('#tanques').show();
					$('#msgBox').hide();
					$('#tanques tbody').html(data).fadeIn();
					$(".asignacion").each(function() {
						var that = this; // fix a reference to the <input> element selected
						$(this).keyup(function(){
							newSum.call(that); // pass in a context for newsum():
						});
					});
					$('#observado').click(function() {
						$('#observaciones').toggle('slow', function() {
							// Animation complete.
						});
					});
				});
				var borrar = $('#idOrden').val().substr(1);
				$('#op'+borrar).block({ message: null }); 
				return false;
			});
			
			function newSum() {
				var sum = 0;
				//sirvepara ir sumando las cantidades asignadas y que no deje pasar si no se asigno el total
				$('#asignaTanques tbody').find('input:text').each( function(){
					if(!isNaN(this.value)){
						sum += parseFloat(this.value);}
				});
				$('#totalCalculado').val(sum);
			}
			
			var opciones= {
                beforeSubmit: validate, //funcion que se ejecuta antes de enviar el form
                success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
				url:       'func/asignaTanques.php',         // override for form's 'action' attribute 
				type:      'post'       // 'get' or 'post', override for form's 'method' attribute
            };
             //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
            $('#asignaTanques').ajaxForm(opciones) ; 
 
             //lugar donde defino las funciones que utilizo dentro de "opciones"
            function mostrarLoader(){
                $('#loader_gif').fadeIn("slow"); //muestro el loader de ajax
            };
			
			function validate(formData, jqForm, options) { 
				// valido que todos los litros despachados se hayan asignado a tanques.
				var acumulado = 0;
				$(".controlIngreso").each(function() {
					//add only if the value is number
					if(!isNaN(this.value) && this.value.length!=0) {
						acumulado += parseFloat(this.value);
					}
				});
				if(acumulado != $('#totalCalculado').val() && !$('#observado').is(':checked')){
					alert('Revisar las cantitades descargadas en cada tanque o marcar la casilla de "Otras descargas" ' + acumulado + ' - '+ $('#totalCalculado').val()); 
					return false; 
				} //else alert('2"' + acumulado + ' - '+ $('#totalCalculado').val()); 
			}

            function mostrarRespuesta(responseText){
                $("#loader_gif").fadeOut("slow"); // Hago desaparecer el loader de ajax
				var n=responseText.split("||");
				$('#msgBox').html(n[1]).fadeIn('slow');
				$('#tanques').hide();
				var borrar = $('#idOrden').val().substr(1);
				$('#op'+borrar).hide('slow');

				// eliminar bloque en box izquierdo
				// mejorar efecto de feedback
				// eliminar opcion dedistribuir el mismo camion de nuevo
			};
		});
		
	</script>
	</body>
</html>
<!--
// definimos las opciones del plugin AJAX FORM
            

-->