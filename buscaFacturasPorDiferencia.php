<?php
$nivelRequerido = 2;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');


if(!isset($_SESSION['ultimosMeses'])||1){
	$_SESSION['ultimosMeses']='';
	$currentMonth = (int)date('m');
	for($x = $currentMonth; $x > $currentMonth-6; $x--) {
		$_SESSION['ultimosMeses'] .= "<option value='".date('Y-m-01', mktime(0, 0, 0, $x, 1))."'>".date('F, Y', mktime(0, 0, 0, $x, 1))."</option>";
	}
}
if(!isset($_SESSION['ultimosCierresTesoreria'])){
	// carga los datos de esta orden
	$sqlCajas = "SELECT IdCierreCajaTesoreria, FechaCierre FROM dbo.CierresCajaTesoreria WHERE FechaCierre>=DATEADD(month, -1, GETDATE()) ORDER BY FechaCierre desc;";
	$stmt = odbc_exec( $mssql, $sqlCajas);
	$_SESSION['ultimosCierresTesoreria']='';
	while($rowCuentas = odbc_fetch_array($stmt)){
		$_SESSION['ultimosCierresTesoreria'].="<option value='$rowCuentas[IdCierreCajaTesoreria]'>".date_format($rowCuentas['FechaCierre'], "d/m/Y H:i:s")."</option>";
	}
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Detalle facturas pendientes</title>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
	  html {
    
}
    </style>
   
  </head>

  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
	<div class="container">
		<div class='row'>
			<div class="col-md-6">
				<h2>Busca turnos</h2>
				<form name='nuevaOP' id='nuevaOP' class='form-horizontal well'>
				<fieldset>
					<div class="form-group" id='rop'>  
					<label class="control-label" for="mes">Mes</label>
					<div class="controls">
					<div class="input-group" id='rmes'>
						<input type='text' name='activaMes' id='activaMes' class="input-sm form-control col-md-10" data-plus-as-tab='true' placeholder='Click para activar'/>
					  <select name='mes' id='mes' class='input-sm form-control col-md-10 selector' style='display:none'>
						<option value='' selected='selected'>Filtrar por mes</option>
						<?php echo $_SESSION['ultimosMeses'] ?>
					  </select>	
					</div></div></div>
                    
                                   
					<div class="form-group">  
					<label class="control-label" for="rangoFechas">Rango de fechas</label>
					<div class="controls">
					<div class="input-group" id='rop'>
					  <input type='text' name='rangoInicio' id='rangoInicio' class="input-sm form-control" value="<?php echo date("m/d/y", mktime(0, 0, 0, date("m", strtotime("-1 month")), 1, date("Y", strtotime("-1 month"))));?>" data-date-format="mm/dd/yy"  data-plus-as-tab='true'/>
					  <span class="input-group-addon">a</span>
					  <input type='text' name='rangoFin' id='rangoFin' class="input-sm form-control"  value="<?php echo date("m/d/y");?>" data-date-format="mm/dd/yy" data-plus-as-tab='true'/>
					  <span class="input-group-addon presetAnio btn" id="<?php echo date('y', strtotime("-1 year"))?>"><?php echo date('Y', strtotime("-1 year"))?></span>
					  <span class="input-group-addon presetAnio btn" id="<?php echo date('y')?>"><?php echo date('Y')?></span>
					</div></div></div>
                    
				
					<div class="form-group" style='display:none'>  
					<label class="control-label" for="activaCuentas">Cierres tesorería</label>
					<div class="controls">
					<div class="input-group" id='rcierrestesoreria'>
						<input type='text' name='activaCierres' id='activaCierres' class="input-sm" data-plus-as-tab='true' disabled='disabled' placeholder='Click para activar'/>
					  <select name='idCierreCajaTesoreria[]' id='idCierreCajaTesoreria' class='input-sm selector' style='display:none' multiple="multiple">
						<option value='' selected='selected'>Filtrar por caja</option>
						<?php echo $_SESSION['ultimosCierresTesoreria'] ?>
					  </select>	
					</div></div></div>
				</fieldset>
				<div class="form-group" id='botonEnvio'>
				<label for='enviar' class="control-label"></label>
				<div class="controls"> 
					<button class="btn btn-primary btn-lg" id='enviar'>Buscar &raquo;</button>
				</div></div>
				</form>
				
				<h2>Totales combustibles sin facturar</h2>
				<div class='well' id='detalle'>
				</div>
			</div>
			<div class="col-md-6">
				<h2>Listado turnos con pendientes</h2>
				<div style='height:80%;overflow-y: scroll;'>
				<table id='libroDiario' class='table'>
				</table>
				</div>
			</div>
			
		</div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>

    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
		$(document).ready(function() {
			$('#botonEnvio').fadeIn();
			$('#rangoInicio').datepicker();
			$('#rangoFin').datepicker();
			$('#fuzzy').click(function() {
				if ($(this).attr('checked')) {
					$('#fuzziness').removeAttr('disabled');
				} else {
					$('#fuzziness').attr('disabled', true);
				}
			});
			$('#rleyenda').click(function() {
				$('#leyenda').removeAttr('disabled');
			});
			$('#rmes, #activaMes').click(function() {
				$('#activaMes').hide();
				$('#mes').show();
				
				$('#idCierreCajaTesoreria').hide();
				$('#activaCierres').show();
				$('#rangoInicio').attr('disabled', true);
				$('#rangoFin').attr('disabled', true);
			});
			$('#rcierrestesoreria').click(function() {
				$('#activaCierres').hide();
				$('#idCierreCajaTesoreria').show();
				
				$('#rangoInicio').attr('disabled', true);
				$('#rangoFin').attr('disabled', true);
				$('#mes').hide();
				$('#activaMes').show();
			});
			$('#activaFechas').click(function() {
				$('#rangoInicio').removeAttr('disabled');
				$('#rangoFin').removeAttr('disabled');
				
				$('#idCierreCajaTesoreria').hide();
				$('#activaCierres').show();
				$('#mes').hide();
				$('#activaMes').show();
				
			});
            $('.presetAnio').click(function(){
                var year = $(this).attr('id');
                $('.presetAnio').removeClass('label-success');
                $(this).addClass('label-success');
                $('#rangoInicio').val('01/01/'+year);
                $('#rangoFin').val('12/31/'+year);
            });
		
			// definimos las opciones del plugin AJAX FORM
            var opciones= {
                beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
                success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
				url:       'func/ajaxBuscaTurnosFacturasPorDiferencia.php',         // override for form's 'action' attribute 
				type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
            };
             //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
            $('#nuevaOP').ajaxForm(opciones) ; 
 
             //lugar donde defino las funciones que utilizo dentro de "opciones"
            function mostrarLoader(){
				$('#enviar').text('Buscando...').addClass('disabled');
				$('#libroDiario').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
            };
            function mostrarRespuesta(responseText){
				$('#enviar').text('Buscar').removeClass('disabled');
				$('#libroDiario').html(responseText).slideDown('slow');
				$('#botonEnvio').fadeIn();
                $.get('func/muestraAgrupadosDiferencia.php', function(data) {
						$('#detalle').html(data).fadeIn();
					});
				$('.debe').click(function() {
					$(this).html("<center><img src='img/ajax-loader-chico.gif'/></center>").removeClass().fadeIn();
					var id = $(this).attr('id');
					$.get('func/abreCierra.php?idTurno=' + id, function(data) {
						$('#' + id).html(data).fadeIn().addClass('debe');
					});
				});
			}
            $('#nuevaOP').submit();
            
		});
		</script>
	</body>
</html>
