<?php
$nivelRequerido = 2;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');


if(!isset($_SESSION['ultimosMeses'])){
  $_SESSION['ultimosMeses']='';
  $currentMonth = (int)date('m');
  for($x = $currentMonth; $x > $currentMonth-6; $x--) {
    $_SESSION['ultimosMeses'] .= "<option value='".date('Y-m-01', mktime(0, 0, 0, $x, 1))."'>".date('F, Y', mktime(0, 0, 0, $x, 1))."</option>";
  }
}
if(!isset($_SESSION['ultimosCierresTesoreria'])){
  // carga los datos de esta orden
  $sqlCajas = "SELECT IdCierreCajaTesoreria, FechaCierre FROM dbo.CierresCajaTesoreria WHERE FechaCierre>=DATEADD(month, -1, GETDATE()) ORDER BY FechaCierre desc;";
  $stmt = odbc_exec2( $mssql, $sqlCajas, __LINE__, __FILE__);
  $_SESSION['ultimosCierresTesoreria']='';
  while($rowCuentas = sqlsrv_fetch_array($stmt)){
    $_SESSION['ultimosCierresTesoreria'].="<option value='$rowCuentas[IdCierreCajaTesoreria]'>".date_format($rowCuentas['FechaCierre'], "d/m/Y H:i:s")."</option>";
  }
}
if(!isset($_SESSION['tarjetasCredito'])){
  $sqlTarjetas = "SELECT IdTarjeta, Nombre, IdCuentaContable_Presentacion FROM dbo.tarjetasCredito WHERE Activa=1 ORDER BY Nombre ASC;";
  ChromePhp::log($sqlTarjetas);
  $stmt = odbc_exec2( $mssql, $sqlTarjetas, __LINE__, __FILE__);
  $_SESSION['tarjetasCredito']=array();
  while($rowTarjetas = sqlsrv_fetch_array($stmt)){
    $_SESSION['tarjetasCredito'][$rowTarjetas['IdTarjeta']] = $rowTarjetas['Nombre'];
    $_SESSION['tarjetasCreditoCuenta'][$rowTarjetas['IdTarjeta']] = $rowTarjetas['IdCuentaContable_Presentacion'];
  }
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Busca lotes tarjetas presentados en turnos</title>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
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
        <h2>Busca turnos</h2>
        <form name='nuevaOP' id='nuevaOP' class='form-horizontal well'>
          <input type='hidden' name='buscador' value='tarjetas'/>
          <fieldset>
            <div class="form-group">  
            <label class="control-label" for="rangoFechas">Rango de fechas</label>
            <div class="controls">
            <div class="input-group" id='rop'>
              <input type='text' name='rangoInicio' id='rangoInicio' class="input-sm form-control" value="<?php echo date("d/m/y", mktime(0, 0, 0, date("m", strtotime("-1 month")), 1, date("Y", strtotime("-1 month"))));?>" data-date-format="dd/mm/yy"  data-plus-as-tab='true' placeholder='dd/mm/aa'/>
              <span class="input-group-addon">a</span>
              <input type='text' name='rangoFin' id='rangoFin' class="input-sm form-control"  value="<?php echo date("d/m/y");?>" data-date-format="dd/mm/yy" data-plus-as-tab='true' placeholder='dd/mm/aa'/>
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
            
            <div class="form-group col-md-7 col-md-offset-2">
              <div class="controls">
                <div class="input-group">
                  <label class="checkbox">
                    <input type="checkbox" name='soloabiertos' id='soloabiertos' value='1'/> Solo abiertos
                  </label>
                </div>
              </div>
              <div class="controls">
                <label class="radio">
                  <input type="radio" name="solonorevisados" id="solonorevisados" value="">Todos
                </label>
                <label class="radio">
                  <input type="radio" name="solonorevisados" id="solonorevisados" value="1"><span class="label label-success">Revisados</span>
                </label>
                <label class="radio">
                  <input type="radio" name="solonorevisados" id="solonorevisados" value="2" checked><span class="label label-danger">Sin Revisar</span>
                </label>
              </div> 
            </div>
            <div class="form-group col-md-5 col-md-offset-1">
            <div class="controls">
              <label class="radio">
                <input type="radio" name="idCaja" id="idCaja1" value="">Todas las cajas
              </label>
              <label class="radio">
                <input type="radio" name="idCaja" id="idCaja2" value="1" checked><span class="label label-warning">PLAYA</span>
              </label>
              <label class="radio">
                <input type="radio" name="idCaja" id="idCaja3" value="2"><span class="label label-info">SHOP</span>
              </label>
              <label class="radio">
                <input type="radio" name="idCaja" id="idCaja4" value="3"><span class="label label-success">ADMINISTRACION</span>
              </label>
            </div></div>
          </fieldset>
          <div class="form-group" id='botonEnvio'>
          <label for='enviar' class="control-label"></label>
          <div class="controls"> 
                  <button class="btn btn-primary btn-lg" id='enviar'>Buscar &raquo;</button>
          </div></div>
          </form>
          <h2>Detalle lotes ingresados</h2>
          <div class='' id='detalle'>
          </div>
      </div>
      <div class="col-md-6">
              <h2>Listado turnos</h2>
              <div style='height:40em;overflow-y: scroll;'>
              <table id='libroDiario' class='table'>
              </table>
              </div>
      </div>
            
    </div>
    <div class='row'>
      
    </div>
    <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>

    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
          $(document).ready(function() {
            $('#botonEnvio').fadeIn();
            $('#rangoInicio').datepicker();
            $('#rangoFin').datepicker();

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
                $('#rangoFin').val('31/12/'+year);
            });
		
			// definimos las opciones del plugin AJAX FORM
            var opciones= {
                beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
                success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
                url:       'func/ajaxBuscaTurnosPorFecha.php',         // override for form's 'action' attribute 
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
                $('.debe').click(function() {
                  var id = $(this).attr('id');
                  $(this).html("<center><img src='img/ajax-loader-chico.gif'/></center>").removeClass().fadeIn();
                  $.get('func/abreCierra.php?idTurno=' + id, function(data) {
                          $('#' + id).html(data).fadeIn();
                  });
                });
                $('.verTurno').click(function() {
                    $(this).html("<center><img src='img/ajax-loader-chico.gif'/></center>").removeClass().fadeIn();
                    var id2 = $(this).attr('id').split("_");
                    var id = id2[1];
                    $('.turno').removeClass('bg-info');
                    $('#t'+id).addClass('bg-info');
                    $.get('func/ajaxMuestraLotesTurno.php?idTurno=' + id, function(data) {
                        $('#detalle').html(data).fadeIn();
                        $('#turno_'+id).html("<td class='verTurno' id='"+id+"'><span class='viendo label label-success'>VER TURNO</span></td>").removeClass().fadeIn();
                        $('#marcarRevisado').click(function(){
                          $(this).html("<center><img src='img/ajax-loader-chico.gif'/></center>").removeClass().fadeIn();
                          $.get('func/ajaxMarcaTurnoRevisado.php?idTurno=' + id, function(data) {
                            $('#marcarRevisado').html(data).fadeIn();
                          });
                        });
                        $('.graba').click(function(){
                          // graba cambios en los lotes de tarjetas
                          $(this).html("<center><img src='img/ajax-loader-chico.gif'/></center>").removeClass().fadeIn();
                          // elimino el "actualizaLote_" del id para obtener el idLote correspondiente
                          var id3 = $(this).attr('id').split("_");
                          var IdLoteTarjetasCredito = id3[1];
                          var LoteNumero = $('#LoteNumero_'+IdLoteTarjetasCredito).val();
                          var IdTarjeta = $('#selectorTarjeta_'+IdLoteTarjetasCredito).val();
                          var IdCuentaContable_presentacion = $('#IdCuentaContable_presentacion_'+IdLoteTarjetasCredito).val();
                          $.get('func/ajaxActualizaLoteTarjetas.php?IdLoteTarjetasCredito=' + IdLoteTarjetasCredito + '&LoteNumero=' + LoteNumero + '&IdTarjeta=' + IdTarjeta + '&IdCuentaContable_presentacion='+ IdCuentaContable_presentacion + '&IdCierreTurno=' + id, function(data) {
                            $(this).html("<span class='btn btn-default btn-xs graba' id='actualizaLote_"+IdLoteTarjetasCredito+"'>Graba</span>").removeClass().fadeIn();
                          });
                        });
                        $('#turno_'+id).html("<td class='verTurno' id='"+id+"'><span class='viendo label label-success'>VER TURNO</span></td>").removeClass().fadeIn();
                    });
                });
            }
            });
            </script>
	</body>
</html>
