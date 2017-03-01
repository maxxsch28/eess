<?php
$nivelRequerido = 5;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo="Detalla comprobantes adelantos gasoil cancelados";

?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
    <style type="text/css">
        @media print
        {    
            .no-print, .no-print *
            {
                display: none !important;
            }
        }
    </style>
  </head>
  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
	<div class="container">
		<div class='row'>
				<h2></h2>
			<div class="col-md-5 no-print">
                <h2>Ingresar datos pago realizado</h2>
				<form name='nuevaOP' id='nuevaOP' class='form-horizontal well'>
				<fieldset>
					<div class="form-group" id='rop'>  
					<label class="control-label" for="numero">Número documento</label>
					<div class="controls">
					<div class="input-group">
						<input type='text' name='numero' id='numero' class="input-sm form-control col-md-5" pattern="[0-9\.]{1,}" maxlength="12" data-plus-as-tab='true'/>
					</div></div></div>
                                  
                                    
					<div class="form-group" class='no'>  
					<label class="control-label" for="rangoFechas">Rango de fechas</label>
					<div class="controls">
					<div class="input-group" id='rop'>
					  <input type='text' name='rangoInicio' id='rangoInicio' class="input-sm form-control" value="01/01/<?php echo date("Y")?>" data-date-format="mm/dd/yy"  data-plus-as-tab='true'/>
					  <span class="input-group-addon">a</span>
					  <input type='text' name='rangoFin' id='rangoFin' class="input-sm form-control"  value="12/31/<?php echo date("Y")?>" data-date-format="mm/dd/yy" data-plus-as-tab='true'/>
                      <span class="input-group-addon presetAnio btn" id="1000">TODOS</span>
					  <span class="input-group-addon presetAnio btn" id="<?php echo date('Y', strtotime("-1 year"))?>"><?php echo date('Y', strtotime("-1 year"))?></span>
					  <span class="input-group-addon presetAnio btn" id="<?php echo date('Y')?>"><?php echo date('Y')?></span>
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
			</div>
			<div class="col-md-7">
				<h2>Listado comprobantes</h2>
				<div style='height:80%;overflow-y:scroll;max-height:600px'>
				<table id='libroDiarioTransporte' class='table' style='text-align:left'>
				</table>
				</div>
			</div>
		</div>
        <?php include($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
		$(document).ready(function() {
			$('#botonEnvio').fadeIn();
			$('#rangoInicio').datepicker();
			$('#rangoFin').datepicker();
            $('.presetAnio').click(function(){
                var year = $(this).attr('id');
                $('.presetAnio').removeClass('label-success');
                $(this).addClass('label-success');
                if(year==='1000'){
                    $('#rangoInicio').val('01/01/2011');
                    $('#rangoFin').val('12/31/2069');
                } else {
                    $('#rangoInicio').val('01/01/'+year);
                    $('#rangoFin').val('12/31/'+year);
                }
            });

            //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
            // $('#enviarDeposito').ajaxForm(opciones2) ; 
            // $('#nuevaOP').ajaxForm(opciones) ; 
            
            $('#enviar').click(function() {
                var opciones= {
                    beforeSubmit: mostrarLoaderTransporte, //funcion que se ejecuta antes de enviar el form
                    success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
                    url:       'func/listaDetallePagoAdelantosGasoil.php', // override for form's 'action' attribute 
                    type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
                };
               $('#nuevaOP').ajaxForm(opciones);    
            });
      

             //lugar donde defino las funciones que utilizo dentro de "opciones"
            function mostrarLoaderTransporte(){
                $('#botonEnvio').hide();
                $('#botonEnviando').show();
				$('#libroDiarioTransporte').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
                
            };
            function mostrarRespuesta(responseText){
                $('#botonEnviando').hide();
                $('#botonEnvio').fadeIn();
				$('#libroDiarioTransporte').html(responseText).slideDown('slow');
				$('.asiento').click(function() {
					$('.asiento').removeClass('act');
					$('.recibo').removeClass('act');
					$(this).addClass('act');
					$('#detalle').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
					$.get('func/muestraDetalleMovimientoTransporte.php?idAsiento='+$(this).attr('id')+'&monto='+$('#importe').val()+'&fuzzy='+$('#fuzzy').val()+'&fuzziness='+$('#fuzziness').val(), function(data) {
						$('#detalle').html(data).fadeIn();
					});
				});
				$('.recibo').click(function() {
					$('.recibo').removeClass('act');
					$('.asiento').removeClass('act');
					$(this).addClass('act');
					$('#detalle').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
					$.get('func/muestraDetalleMovimientoTransporte.php?idAsiento='+$(this).attr('id')+'&monto='+$('#importe').val()+'&fuzzy='+$('#fuzzy').val()+'&fuzziness='+$('#fuzziness').val(), function(data) {
						$('#detalle').html(data).fadeIn();
					});
				});
			}
            <?php if(isset($_POST['srch-term'])){  // viene del search header ?>
            $('#importe').val(<?php echo $_POST['srch-term'];?>);
            $('#enviar').click();
            <?php }?>
		});
		</script>
	</body>
</html>
