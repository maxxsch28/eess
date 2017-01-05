<?php
$nivelRequerido = 5;
include('include/inicia.php');


?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Rastrea cuentas corrientes</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
        .encabezaAsiento{
            font-weight: bold;
        }
    </style>
    
  </head>

  <body>
	<?php include('include/menuSuperior.php');?>
	<div class="container">
		<div class='row'>
			<div class="col-md-6">
				<h2>Rastrea cuentas corrientes</h2>
				<form name='nuevaOP' id='nuevaOP' class='form-horizontal well'>
				<fieldset>          
					<div class="form-group">  
					<label class="control-label" for="rangoFechas">Datos factura</label>
					<div class="controls">
					<div class="input-group" id='rop'>
                        <span class="input-group-addon">Nº</span>
					  <input type='text' name='prefijo' id='prefijo' class="input-sm form-control" pattern="[0-9\.]{1,}" maxlength="2" data-plus-as-tab='true'/>
					  <span class="input-group-addon">-</span>
					  <input type='text' name='numero' id='numero' class="input-sm form-control" pattern="[0-9\.]{1,}" maxlength="6"  data-plus-as-tab='true'/>
					  <span class="input-group-addon">$</span>
					  <input type='text' name='importe' id='importe' class="input-sm form-control" pattern="[0-9\.]{1,}" maxlength="8"  data-plus-as-tab='true'/>
					</div></div></div>
                     
					<div class="form-group">  
					<label class="control-label" for="cliente">Cliente</label>
					<div class="controls">
					<div class="input-group" id='rcliente'>
					  <input type='text' name='cliente' id='cliente' class="input-sm form-control" data-plus-as-tab='true' disabled='disabled'/>
					  <span class="input-group-addon"><input type="checkbox" value="1" name='buscaCliente' id='buscaCliente'></span>
					</div></div></div>
 
				</fieldset>
                    
				<div class="form-group" id='botonEnvio'>
				<label for='enviar' class="control-label"></label>
				<div class="controls"> 
					<button class="btn btn-primary btn-lg" id='enviar'>Buscar &raquo;</button>
				</div>
                </div>   
                    <div class="form-group" id='botonEnviando' style="display:none">
				<label for='enviandor' class="control-label"></label>
				<div class="controls"> 
					<button class="btn btn-primary btn-lg" >Buscando....</button>
				</div>
                </div>
				</form>
				<form name='nuevaOP2' id='nuevaOP2' class='form-horizontal well'>
				<fieldset>          
					<div class="form-group">  
					<label class="control-label" for="rangoFechas">Datos recibo</label>
					<div class="controls">
					<div class="input-group" id='rop2'>
                        <span class="input-group-addon">Nº 99</span>
					  <input type='text' name='recibo' id='recibo' class="input-sm form-control" pattern="[0-9\.]{1,}" maxlength="5" data-plus-as-tab='true'/>
					  
					</div></div></div>
                    <div class="form-group">  
					<label class="control-label" for="cliente2">Cliente</label>
					<div class="controls">
					<div class="input-group" id='rcliente2'>
					  <input type='text' name='cliente2' id='cliente2' class="input-sm form-control"  disabled='disabled'/>
					  <span class="input-group-addon"><input type="checkbox" value="1" name='buscaCliente2' id='buscaCliente2'></span>
					</div></div></div>
 
				</fieldset>
                    
				<div class="form-group" id='botonEnvio2'>
				<label for='enviar2' class="control-label"></label>
				<div class="controls"> 
					<button class="btn btn-primary btn-lg" id='enviar2'>Buscar &raquo;</button>
				</div>
                </div>   
                    <div class="form-group" id='botonEnviando2' style="display:none">
				<div class="controls"> 
					<button class="btn btn-primary btn-lg" >Buscando....</button>
				</div>
                </div>
				</form>
			</div>
			<div class="col-md-6">
				<h2>Detalle movimientos</h2>
				<div style='height:80%;overflow-y: scroll;max-height:600px'>
				<table id='libroDiario' class='table'>
				</table>
				</div>
			</div>
			
		</div>
        <?php include ('include/footer.php')?>

    </div> <!-- /container -->
	<?php include('include/termina.php');?>
	<script>
		$(document).ready(function() {
			$('#botonEnvio').fadeIn();
			$('#buscaCliente').click(function() {
				if ($(this).attr('checked')) {
					$('#cliente').removeAttr('disabled').focus();
				} else {
					$('#cliente').attr('disabled', true);
				}
			});
			$('#buscaCliente2').click(function() {
				if ($(this).attr('checked')) {
					$('#recibo').attr('disabled', true);
					$('#cliente2').removeAttr('disabled').focus();
				} else {
					$('#cliente2').attr('disabled', true);
					$('#recibo').removeAttr('disabled').focus();
				}
			});

			/* $("#importe").change(function() {
				$.get('func/buscaAsientoPorImporte.php?importe='+$(this).val()+'&rangoInicio='+$('#rangoInicio').val()+'&rangoFin='+$('#rangoFin').val(), function(data) {
					$('#libroDiario').html(data).fadeIn();
					$('#botonEnvio').fadeIn();
				});
			}); */
			
			// definimos las opciones del plugin AJAX FORM
            //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
           // $('#enviarDeposito').ajaxForm(opciones2) ; 
           // $('#nuevaOP').ajaxForm(opciones) ; 
            
            $('#enviar').click(function() {
                var opciones= {
                    beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
                    success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
                    url:       'func/ajaxBuscaMovimientoCuentaCorriente.php',         // override for form's 'action' attribute 
                    type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
                };
               $('#nuevaOP').ajaxForm(opciones);    
            });
            $('#enviar2').click(function() {
                var opciones= {
                    beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
                    success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
                    url:       'func/ajaxBuscaMovimientoReciboCuentaCorriente.php',         // override for form's 'action' attribute 
                    type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
                };
               $('#nuevaOP2').ajaxForm(opciones);    
            });
 
             //lugar donde defino las funciones que utilizo dentro de "opciones"
            function mostrarLoader(){
                $('#botonEnvio').hide();
                $('#botonEnviando').show();
		$('#libroDiario').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
                
            };
            function mostrarRespuesta(responseText){
                $('#botonEnviando').hide();
                $('#botonEnvio').fadeIn();
		$('#libroDiario').html(responseText).slideDown('slow');
			}
		});
		</script>
	</body>
</html>
