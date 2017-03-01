<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');


if(isset($_GET['id'])){
	// carga los datos de esta orden
	
}

$sqlClientes = "select IdCliente, Codigo, RazonSocial, Identificador from dbo.Clientes where IdZonaCliente=2 order by RazonSocial";
$stmt = odbc_exec( $mssql, $sqlClientes);
$options="<option value='' selected='selected'>Seleccione cliente</option>";
$arrayReemplazo = array('TELEFONO', 'TELEFONOS');
while($row = odbc_fetch_array($stmt)){
	if(trim(str_replace($arrayReemplazo, '', $row['Identificador']))<>''){
		$identificador = "(".str_replace($arrayReemplazo, '', $row['Identificador']).")";
	} else $identificador = "";
	$options.="<option value='$row[IdCliente]'>$row[RazonSocial] $identificador</option>";
}

$tabla="";

$actualiza = false;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Carga Factura | Movistar</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
   
  </head>

  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
	<div class="container">
		<div class='row'>
			<div class="col-md-6">
				<h2><?php if($actualiza&&!$fechaFactura)echo"Actualizar"; elseif(isset($fechaFactura))echo "Revisa";else echo "Nuevo";?> Cliente</h2>
				<form name='nuevaOP' id='nuevaOP' class='form-horizontal well'<?php if(isset($fechaFactura))echo" disabled='true'"?>>
				<?php if($actualiza)echo "<input type='hidden' name='actualiza' value='$_GET[id]'/>";?>
				<fieldset>
					<div class="form-group" id='rop'>  
					<label class="control-label" for="cliente">Cliente</label>
					<div class="controls">
					<div class="input-group">
						<select id='idCliente' name='idCliente' class='input' style='text-align:left'>
							<?php echo $options?>
						</select><span class="input-group-addon input-group-addon-medium" id='idClienteNro'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;
					</div></div></div>
					<div id='celularesCliente'>
					</div>
					<br/>
					
				</fieldset>
				<div class="form-group" id='botonEnvio'>
				<label for='enviar' class="control-label"></label>
				<div class="controls"> 
					<button class="btn btn-primary btn-lg" id='enviar'><?php if($actualiza)echo"Actualizar"; else echo "Cargar";?> &raquo;</button><div id='loader_gif'><img src=''/> Cargando...</div>
				</div></div>
				</form>
			</div>
			
		</div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>

    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
		$(document).ready(function() {
			$("#idCliente").change(function() {
				$('#idClienteNro').html($(this).val());
				$.get('func/celularesCliente.php?idCliente='+$(this).val(), function(data) {
					$('#celularesCliente').html(data).fadeIn();
					$('#botonEnvio').fadeIn();
					if($('#actualiza').val()=='1')$('#enviar').val('Actualizar');
				});
			});
			
			// definimos las opciones del plugin AJAX FORM
            var opciones= {
                beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
                success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
				url:       'func/cargaNuevosCelulares.php',         // override for form's 'action' attribute 
				type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
            };
             //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
            $('#nuevaOP').ajaxForm(opciones) ; 
 
             //lugar donde defino las funciones que utilizo dentro de "opciones"
            function mostrarLoader(){
                $('#loader_gif').fadeIn("slow"); //muestro el loader de ajax
            };
            function mostrarRespuesta (responseText){
				if(responseText.search('yes')>0){
					//document.location='/ypf/cargaMovistar.php';
					
				}
                $("#loader_gif").fadeOut("slow"); // Hago desaparecer el loader de ajax
				$.get('func/celularesCliente.php?idCliente='+$(this).val(), function(data) {
					$('#celularesCliente').html(data).fadeIn();
					$('#botonEnvio').fadeIn();
				});
				
			}
			
			$('.noCel').click(function(){
				var id = $(this).attr('id').substr(1);
				$('#r'+id).remove();
				if($('.noComb:visible').length==1)
					$('.noComb').remove();
			});
		});
		</script>
	</body>
</html>