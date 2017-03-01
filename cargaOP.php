<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

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
		$ltsPedido[$orden['idArticulo']]	= $orden['litrosPedidos'];			
		if($orden['op']<>'0000000000'){
			$ltsEntregado[$orden['idArticulo']]	= $orden['litrosEntregados'];
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
    <title>Carga OP | YPF</title>
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
	<!-- Main hero unit for a primary marketing message or call to action -->
		<div class='row'>
			<div class="col-md-6">
				<h2><?php if($actualiza&&!$fechaDespacho)echo"Actualizar"; elseif(isset($fechaDespacho))echo "Revisa";else echo "Nueva";?> orden de provisión<h2>
				<form name='nuevaOP' id='nuevaOP' class='form-horizontal well'<?php if(isset($fechaDespacho))echo" disabled='true'"?>>
				<?php if($actualiza)echo "<input type='hidden' name='actualiza' value='$_GET[id]'/>";?>
				<fieldset>
					<div class="form-group" id='rop'>  
					<label class="control-label" for="op">Numero orden</label>
					<div class="controls">
					<div class="input-group"> 
						<input type='text' name='op' id='op' required="required" pattern="[0-9]{5,}" maxlength="12" class='input-sm'<?php if($cargadoEnYPF)echo" value='$op'";?>/><?php if(!$cargadoEnYPF)echo"<button class='btn btn-default noOP remove' type='button' id='xop'>No</button>";?>
					</div></div></div>
					<div class="form-group">  
					<label for='fechaPedido' class="control-label">Fecha pedido</label>
					<div class="controls">  
						<input type='text' name='fechaPedido' id='fechaPedido' value="<?php if($actualiza&&$fechaPedido<>'0/00/0000')echo$fechaPedido;else echo date("d/m/Y")?>" class='input-sm' required="required"/> <span class=''>&nbsp;&nbsp;&nbsp;&nbsp;</span> <input type='text' name='fechaEstimada' id='fechaEstimada' value="<?php if($actualiza&&$fechaEstimada<>'0/00/0000')echo$fechaEstimada;else echo date("d/m/Y",strtotime('+2 days'))?>" class='input-sm'/>
					</div></div>
					<!--<div class="form-group">  
					<label for='fechaEstimada' class="control-label">Entrega</label>
					<div class="controls">  
						<input type='text' name='fechaEstimada' id='fechaEstimada' value="<?php echo date("d/m/Y",strtotime('+2 days'))?>" class='input-sm'/>
					</div></div>-->
					<div class="form-group" id='r2068'>  
					<label for='c2068' class="control-label"><?php echo $articulo[2068]?></label>
					<div class="controls"> 
					<div class="input-group"> 
						<input type='text' name='c2068' id='c2068' class="input-sm" placeholder="Litros…" required="required" pattern="[0-9]{4,}" maxlength="5"<?php if(isset($ltsPedido[2068]))echo" value='$ltsPedido[2068]'";?>/><button class="btn btn-default noComb" type="button" id='x2068'>No</button>
						<?php if($cargadoEnYPF){?>
						<input type='text' name='e2068' id='e2068' class="input-sm" placeholder="Entregados..." pattern="[0-9]{4,}" maxlength="5"<?php if(isset($ltsEntregado[2068]))echo" value='$ltsEntregado[2068]'";?>/>
						<?php }?>
					</div></div></div>
					<div class="form-group" id='r2069'>  
					<label for='c2069' class="control-label"><?php echo $articulo[2069]?></label>
					<div class="controls">
					<div class="input-group">  
						<input type='text' name='c2069' id='c2069' class="input-sm" placeholder="Litros…" required="required" pattern="[0-9]{4,}" maxlength="5"<?php if(isset($ltsPedido[2069]))echo" value='$ltsPedido[2069]'";?>/><button class="btn btn-default noComb" type="button" id='x2069'>No</button>
						<?php if($cargadoEnYPF){?>
						<input type='text' name='e2069' id='e2069' class="input-sm" placeholder="Entregados..." pattern="[0-9]{4,}" maxlength="5"<?php if(isset($ltsEntregado[2069]))echo" value='$ltsEntregado[2069]'";?>/>
						<?php }?>
					</div></div></div>
					<div class="form-group" id='r2076'>  
					<label for='c2076' class="control-label"><?php echo $articulo[2076]?></label>
					<div class="controls"> 
					<div class="input-group"> 
						<input type='text' name='c2076' id='c2076' class="input-sm" placeholder="Litros…" required="required" pattern="[0-9]{4,}" maxlength="5"<?php if(isset($ltsPedido[2076]))echo" value='$ltsPedido[2076]'";?>/><button class="btn btn-default noComb" type="button" id='x2076'>No</button>
						<?php if($cargadoEnYPF){?>
						<input type='text' name='e2076' id='e2076' class="input-sm" placeholder="Entregados..." pattern="[0-9]{4,}" maxlength="5"<?php if(isset($ltsEntregado[2076]))echo" value='$ltsEntregado[2076]'";?>/>
						<?php }?>
					</div></div></div>
					<div class="form-group" id='r2078'>
					<label for='c2078' class="control-label"><?php echo $articulo[2078]?></label>
					<div class="controls">
					<div class="input-group">
						<input type='text' name='c2078' id='c2078' class="input-sm" placeholder="Litros…" required="required" pattern="[0-9]{4,}" maxlength="5"<?php if(isset($ltsPedido[2078]))echo" value='$ltsPedido[2078]'";?>/><button class="btn btn-default noComb" type="button" id='x2078'>No</button>
						<?php if($cargadoEnYPF){?>
						<input type='text' name='e2078' id='e2078' class="input-sm" placeholder="Entregados..." pattern="[0-9]{4,}" maxlength="5"<?php if(isset($ltsEntregado[2078]))echo" value='$ltsEntregado[2078]'";?>/>
						<?php }?>
					</div></div></div>
					<div class="form-group" id='est'> 
					<label for='estado' class="control-label">Estado</label>
					<div class="controls">  
						<input type='text' name='estado' id='estado' class="input" placeholder="Estado" required="required" maxlength="255"<?php if(isset($estado))echo" value='$estado'";?>/> <input type='checkbox' name='entregado' id='entregado' <?php if(isset($fechaDespacho)&&$fechaDespacho)echo" checked='checked'"?>/>
					</div></div>
				</fieldset>
				<?php if(!isset($fechaDespacho)||isset($fechaDespacho)&&!$fechaDespacho){?>
				<div class="form-group">
				<label for='enviar' class="control-label"></label>
				<div class="controls"> 
					<button class="btn btn-primary btn-lg" id='enviar'><?php if($actualiza)echo"Actualizar"; else echo "Cargar";?> &raquo;</button><div id='loader_gif'><img src=''/> Cargando...</div>
				</div>
				</div>
				<?php }?>
				</form>
			</div>
			<div class="col-md-6" id='ultimasOrdenes'>
				<h2>Ultimas órdenes</h2>
				<table class='table'>
					<thead><tr><th width='10%'>Orden</th><th width='10%'>Pedido</th><th width='75%'>Estado</th><th width='5%'></th></tr></thead>
					<tbody>
						<?php echo $tabla;?>
					</tbody>
				</table>
			</div>
		</div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>

    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
		$(document).ready(function() {
			$('#fechaPedido, #fechaEstimada').datepicker({
				format: 'dd/mm/yyyy'
			});
			// definimos las opciones del plugin AJAX FORM
            var opciones= {
                beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
                success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
				url:       'func/cargaNuevaOP.php',         // override for form's 'action' attribute 
				type:      'post',       // 'get' or 'post', override for form's 'method' attribute 
				clearForm: true,        // clear all form fields after successful submit 
				resetForm: true        // reset the form after successful submit 
            };
             //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
            $('#nuevaOP').ajaxForm(opciones) ; 
 
             //lugar donde defino las funciones que utilizo dentro de "opciones"
            function mostrarLoader(){
                $('#loader_gif').fadeIn("slow"); //muestro el loader de ajax
            };
            function mostrarRespuesta (responseText){
				alert(responseText);  //responseText es lo que devuelve la página contacto.php. Si en contacto.php hacemos echo "Hola" , la variable responseText = "Hola" . Aca hago un alert con el valor de response text
                $("#loader_gif").fadeOut("slow"); // Hago desaparecer el loader de ajax
			}
			
			$('.noComb').click(function(){
				var id = $(this).attr('id').substr(1);
				$('#r'+id).remove();
				if($('.noComb:visible').length==1)
					$('.noComb').remove();
			});
			
			$('.noOP').click(function(){
				var id = $(this).attr('id').substr(1);
				$('#r'+id).remove();
			});
			
			$('.infoOP').bind('hover',function(){
				var el=$(this);
				var id = $(this).attr('id').substr(2);
				$.get('func/detalleOrden.php?id='+id,function(d){
					el.unbind('hover').popover({content: d, placement:'left'}).popover('show');
				});
			});
			
			$('#entregado').change(function(){
				if($(this).is(':checked')){
					$('#estado').attr('disabled',true).val('Entregado');
				} else {
					$('#estado').removeAttr("disabled");
				}
			});
			<?php if(isset($fechaDespacho)&&$fechaDespacho){?>
				$("#nuevaOP :input").attr("disabled", true);
			<?php }?>
		});
		</script>
	</body>
</html>