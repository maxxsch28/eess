<?php
$nivelRequerido = 5;
include('include/inicia.php');


if(!isset($_SESSION['cuentasContables'])){
	// carga los datos de esta orden
	$sqlCuentas = "SELECT IdCuentaContable, Descripcion FROM dbo.CuentasContables WHERE Imputable=1 ORDER BY Descripcion;";
	$stmt = sqlsrv_query( $mssql, $sqlCuentas);
	$_SESSION['cuentasContables']='';
	while($rowCuentas = sqlsrv_fetch_array($stmt)){
		$_SESSION['cuentasContables'].="<option value='$rowCuentas[IdCuentaContable]'>$rowCuentas[Descripcion]</option>";
	}
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Busca asiento por importe</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
    </style>
  </head>
  <body>
	<?php include('include/menuSuperior.php');?>
	<div class="container">
        <div class='row'>
          <h2></h2>
          <div class="col-md-6">
          <h2>Busca asiento</h2>
          <form name='nuevaOP' id='nuevaOP' class='form-horizontal well'>
          <input type='hidden' id='ambito' name='ambito' value='eess'/>
          <fieldset>
            <div class='col-md-3'>
              <div class="form-group" id='rop'> 
              <label class="control-label" for="importe">Importe</label>
              <div class="controls">
              <div class="input-group">
                      <input type='text' name='importe' id='importe' class="input-sm form-control col-md-5" pattern="[0-9\.]{1,}" maxlength="12" data-plus-as-tab='true'/><span class="input-group-addon" id='errorFactura'>$</span>
              </div></div></div>
              </div>
              <div class='col-md-8 col-md-offset-1'>
              <div class="form-group">  
              <label class="control-label" for="rangoFechas">Rango de fechas</label>
              <div class="controls">
              <div class="input-group" id='rop'>
                <input type='text' name='rangoInicio' id='rangoInicio' class="input-sm form-control" value="01/01/<?php echo date("y")?>" data-date-format="mm/dd/yy"  data-plus-as-tab='true'/>
                <span class="input-group-addon"></span>
                <input type='text' name='rangoFin' id='rangoFin' class="input-sm form-control"  value="12/31/<?php echo date("y")?>" data-date-format="mm/dd/yy" data-plus-as-tab='true'/>
<span class="input-group-addon presetAnio btn" id="1000">&#8734; </span>
                <span class="input-group-addon presetAnio btn" id="<?php echo date('Y', strtotime("-1 year"))?>"><?php echo date('Y', strtotime("-1 year"))?></span>
                <span class="input-group-addon presetAnio btn" id="<?php echo date('Y')?>"><?php echo date('Y')?></span>
              </div></div></div>
            </div>
          </fieldset>
          <fieldset>
            <div class="form-group col-md-offset-1">
              <div class="controls">
              <div class='col-md-5'>
                <label class="radio" for='fuzzy'>
                  <input type="checkbox" value="1" name='fuzzy' id='fuzzy'> Fuzzy +/- <div class='col-md-4 pull-right'><input type='text' name='fuzziness' id='fuzziness' class="input-sm form-control col-md-3" value="1" data-plus-as-tab='true' disabled='disabled'/></div>
                </label>
                <label class="radio" for='conciliando'>
                    <input type="checkbox" value="1" name='conciliando' id='conciliando'> Muestra checks
                </label>
              </div>
              <div class='col-md-6 col-md-offset-1'>
                <label class="radio" for='ord_imp'>
                    <input type="checkbox" value="1" name='ord_imp' id='ord_imp' checked> Ordenado por importes
                </label>
              </div>
            </div></div>
            <div class='col-md-5'>
            <div class="form-group">  
            <label class="control-label" for="leyenda">Leyenda</label>
            <div class="controls">
            <div class="input-group" id='rleyenda'>
              <input type='text' name='leyenda' id='leyenda' class="input-sm form-control" data-plus-as-tab='true' disabled='disabled'/>
              <span class="input-group-addon"><input type="checkbox" value="1" name='buscaLeyenda' id='buscaLeyenda'></span>
            </div></div></div>
            </div>
            <div class='col-md-6 col-md-offset-1'>
            <div class="form-group">  
            <label class="control-label" for="activaCuentas">Cuenta contable</label>
            <div class="controls">
            <div class="input-group" id='ractivaCuentas'>
              <select name='cuentaEESS' id='cuentaEESS' class='input-sm form-control' disabled>
                    <option value='' selected='selected'>Filtrar por cuenta</option>
                    <?php echo $_SESSION['cuentasContables'] ?>
              </select><span class="input-group-addon"><input type="checkbox" value="1" name='filtraCuenta' id='filtraCuenta'></span>	
            </div></div></div>
            </div>
          </fieldset>
          <fieldset>
              <div class="form-group" id='botonEnvio'>
              <label for='enviar' class="control-label"></label>
              <div class="controls"> 
              <button class="btn btn-primary btn-lg" id='enviar'>Buscar &raquo;</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <div class='pull-right'><button id="enviarDeposito" class="btn btn-primary " style='align:right' align='right'><i class="glyphicon glyphicon-search"></i> Cheque»</button>&nbsp;<button id="enviarTarjeta" class="btn btn-primary " style='align:right' align='right'><i class="glyphicon glyphicon-search"></i> Tarjeta»</button></div>
              </div>
              </div>   
              <div class="form-group" id='botonEnviando' style="display:none">
                <label for='enviandor' class="control-label"></label>
                <div class="controls"> 
                        <button class="btn btn-primary btn-lg" >Buscando....</button>
                </div>
              </div>
              </fieldset>
              
              
              
                          </form>
                          
                          <h2>Ultimas consultas <span class='badge' id='alternoMiosTodos'>Míos/Todos</span></h2>
                          <table class='highlight table table-striped table-condensed' id='historico'>
                          <thead></thead>
                          <tbody></tbody>
                          </table>
                          <h2>Detalle movimiento</h2>
                          <div class='well' id='detalle'>
                          </div>
                  </div>
			<div class="col-md-6">
				<h2>Listado asientos</h2>
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
          $('#rangoInicio').datepicker();
          $('#rangoFin').datepicker();
          $('#fuzzy').click(function() {
            if ($(this).attr('checked')) {
              $('#fuzziness').removeAttr('disabled').focus();
            } else {
              $('#fuzziness').attr('disabled', true);
            }
          });
          $('#buscaLeyenda').click(function() {
            if ($(this).attr('checked')) {
              $('#leyenda').removeAttr('disabled').focus();
            } else {
              $('#leyenda').attr('disabled', true);
            }
          });
          $('#filtraCuenta').click(function() {
            if ($(this).attr('checked')) {
              $('#cuentaEESS').removeAttr('disabled').focus();
            } else {
            $('#cuentaEESS').attr('disabled', true);
            }
          });
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
          $.ajax({ url: "/func/ajaxHistorico.php", success: function(data){
            //Update your dashboard gauge
            $('#historico tbody').html(data);
          }, dataType: "html"});
          setInterval(function(){
            $.ajax({ url: "/func/ajaxHistorico.php", success: function(data){
              //Update your dashboard gauge
              $('#historico tbody').html(data);
            }, dataType: "html"});
          }, 10000);
            $('#alternoMiosTodos').click(function(){
              $.ajax({ url: "/func/ajaxHistorico.php?alterno=1", success: function(data){
              //Update your dashboard gauge
              $('#historico tbody').html(data);
            }, dataType: "html"});
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
                    url:       'func/ajaxBuscaAsientoPorImporte.php',         // override for form's 'action' attribute 
                    type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
                };
               $('#nuevaOP').ajaxForm(opciones);    
            });
      
          
            $('#enviarDeposito').click(function() {
                 var opciones= {
                    beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
                    success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
                    url:       'func/ajaxBuscaDepositoPorCheque.php',         // override for form's 'action' attribute 
                    type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
                };
                $('#nuevaOP').ajaxForm(opciones);    
            });
          
            $('#enviarTarjeta').click(function() {
                 var opciones= {
                    beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
                    success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
                    url:       'func/ajaxBuscaTarjetaCuponLote.php',         // override for form's 'action' attribute 
                    type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
                };
                $('#nuevaOP').ajaxForm(opciones);    
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
                $('.asiento').click(function() {
                  $('.asiento').removeClass('act');
                  $('.recibo').removeClass('act');
                  $(this).addClass('act');
                  $('#detalle').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
                  $.get('func/muestraDetalleMovimiento.php?idAsiento='+$(this).attr('id')+'&monto='+$('#importe').val()+'&fuzzy='+$('#fuzzy').val()+'&fuzziness='+$('#fuzziness').val(), function(data) {
                          $('#detalle').html(data).fadeIn();
                  });
                });
                $('.recibo').click(function() {
                  $('.recibo').removeClass('act');
                  $('.asiento').removeClass('act');
                  $(this).addClass('act');
                  $('#detalle').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
                  $.get('func/muestraDetalleMovimiento.php?idAsiento='+$(this).attr('id')+'&monto='+$('#importe').val()+'&fuzzy='+$('#fuzzy').val()+'&fuzziness='+$('#fuzziness').val(), function(data) {
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
