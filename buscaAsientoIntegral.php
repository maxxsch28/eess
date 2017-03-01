<?php
$nivelRequerido = 5;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');


if(!isset($_SESSION['cuentasContablesTransporte'])){
  // carga los datos de esta orden
  $sqlCuentas = "SELECT orden, nombre FROM [sqlcoop_dbshared].[dbo].[plancuen] WHERE imputable='S' ORDER BY Nombre;";
  $stmt = odbc_exec( $mssql2, $sqlCuentas);
  $_SESSION['cuentasContablesTransporte']='';
  while($rowCuentas = odbc_fetch_array($stmt)){
    $_SESSION['cuentasContablesTransporte'].="<option value='$rowCuentas[orden]'>".utf8_encode($rowCuentas['nombre'])."</option>";
  }
}
if(!isset($_SESSION['cuentasContables'])){
  // carga los datos de esta orden
  $sqlCuentas = "SELECT IdCuentaContable, Descripcion FROM dbo.CuentasContables WHERE Imputable=1 ORDER BY Descripcion;";
  $stmt = odbc_exec( $mssql, $sqlCuentas);
  $_SESSION['cuentasContables']='';
  while($rowCuentas = odbc_fetch_array($stmt)){
    $_SESSION['cuentasContables'].="<option value='$rowCuentas[IdCuentaContable]'>$rowCuentas[Descripcion]</option>";
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Busca asiento COMPLETO</title>
  <?php include ('/include/head.php');?>
  <style type="text/css">
  </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
<div class="container" style='width:1400px'>
  <div class='row'>
    <h2></h2>
    <div class="col-md-4">
      <h2>Busca asiento <b>INTEGRAL</b></h2>
      <form name='nuevaOP' id='nuevaOP' class='form-horizontal well'>
        <input type='hidden' id='ambito' name='ambito' value='integral'/>
        <fieldset>
          <div class="form-group" id='rop'>  
            <label class="control-label" for="importe">Importe</label>
            <div class="controls">
              <div class="input-group">
                <input type='text' name='importe' id='importe' class="input-sm form-control col-md-5" pattern="[0-9\.]{1,}" maxlength="12" data-plus-as-tab='true'/><span class="input-group-addon" id='errorFactura'>$</span>
              </div>
            </div>
          </div>
          <div class="form-group">  
            <label class="control-label" for="rangoFechas">Rango de fechas</label>
            <div class="controls">
              <div class="input-group" id='rop'>
                <input type='text' name='rangoInicio' id='rangoInicio' class="input-sm form-control" value="01/01/<?php echo date("Y")?>" data-date-format="mm/dd/yy"  data-plus-as-tab='true'/>
                <span class="input-group-addon">a</span>
                <input type='text' name='rangoFin' id='rangoFin' class="input-sm form-control"  value="12/31/<?php echo date("Y")?>" data-date-format="mm/dd/yy" data-plus-as-tab='true'/>
                <span class="input-group-addon presetAnio btn" id="1000">&infin;</span>
                <span class="input-group-addon presetAnio btn" id="<?php echo date('Y', strtotime("-1 year"))?>"><?php echo date('Y', strtotime("-1 year"))?></span>
                <span class="input-group-addon presetAnio btn" id="<?php echo date('Y')?>"><?php echo date('Y')?></span>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label" for="fuzzy">Fuzzy +/-</label>
            <div class="controls">
              <div class="input-group" id='rop'>
                <input type='text' name='fuzziness' id='fuzziness' class="input-sm form-control" value="1" data-plus-as-tab='true' disabled='disabled'/>
                <span class="input-group-addon"><input type="checkbox" value="1" name='fuzzy' id='fuzzy'></span>
              </div>
            </div>
          </div>
          <div class="form-group">  
            <label class="control-label" for="leyenda">Leyenda</label>
            <div class="controls">
              <div class="input-group" id='rleyenda'>
                <input type='text' name='leyenda' id='leyenda' class="input-sm form-control" data-plus-as-tab='true' disabled='disabled'/>
                <span class="input-group-addon"><input type="checkbox" value="1" name='buscaLeyenda' id='buscaLeyenda'></span>
              </div>
            </div>
          </div>
          <!--<div class="form-group">  
            <label class="control-label" for="activaCuentas">Cuenta contable</label>
            <div class="controls">
              <div class="input-group" id='ractivaCuentas'>
                <select name='cuenta' id='cuenta' class='input-sm form-control' disabled>
                  <option value='' selected='selected'>Filtrar por cuenta</option>
                  <?php echo $_SESSION['cuentasContablesTransporte'] ?>
                </select><span class="input-group-addon"><input type="checkbox" value="1" name='filtraCuenta' id='filtraCuenta'></span>	
              </div>
            </div>
          </div>-->
        </fieldset>
        <div class="form-group" id='botonEnvio'>
          <label for='enviar' class="control-label"></label>
          <div class="controls"> 
            <button class="btn btn-primary btn-lg enviar" id='enviar'>Buscar &raquo;</button>
          </div>
        </div>   
        <div class="form-group" id='botonEnviando' style="display:none">
          <label for='enviandor' class="control-label"></label>
          <div class="controls"> 
            <button class="btn btn-primary btn-lg" >Buscando....</button>
          </div>
        </div>
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
    <div class="col-md-8">
      <h2>Listado asientos</h2>
      <div style='height:80%;overflow-y: scroll;max-height:600px'>
        <div class='col-md-6'>
          <table id='libroDiario' class='table'>
          </table>
        </div>
        <div class='col-md-6'>
          <table id='libroDiarioTransporte' class='table'>
          </table>
        </div>
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
        $('#cuenta').removeAttr('disabled').focus();
      } else {
        $('#cuenta').attr('disabled', true);
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

    $('#nuevaOP').submit(function() {
      var opciones= {
        beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
        success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
        url:       'func/ajaxBuscaAsientoPorImporte.php',         // override for form's 'action' attribute 
        type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
      };
      // inside event callbacks 'this' is the DOM element so we first 
      // wrap it in a jQuery object and then invoke ajaxSubmit 
      $(this).ajaxSubmit(opciones); 
      var opcionesTransporte= {
        beforeSubmit: mostrarLoaderTransporte, //funcion que se ejecuta antes de enviar el form
        success: mostrarRespuestaTransporte, //funcion que se ejecuta una vez enviado el formulario
        url:       'func/ajaxBuscaAsientoPorImporteTransporte.php',         // override for form's 'action' attribute 
        type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
      };$(this).ajaxSubmit(opcionesTransporte); 
      // !!! Important !!! 
      // always return false to prevent standard browser submit and page navigation 
      return false; 
    }); 
    $('#enviarDeposito').click(function() {
      var opciones= {
        beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
        success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
        url:       'func/ajaxBuscaDepositoPorChequeTransporte.php',         // override for form's 'action' attribute 
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
    function mostrarLoaderTransporte(){
      $('#botonEnvio').hide();
      $('#botonEnviando').show();
      $('#libroDiarioTransporte').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
    };
    function mostrarRespuestaTransporte(responseText){
      $('#botonEnviando').hide();
      $('#botonEnvio').fadeIn();
      $('#libroDiarioTransporte').html(responseText).slideDown('slow');
      $('.asientoTransporte').click(function() {
        $('.asientoTransporte').removeClass('act');
        $('.reciboTransporte').removeClass('act');
        $(this).addClass('act');
        $('#detalle').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
        $.get('func/muestraDetalleMovimientoTransporte.php?idAsiento='+$(this).attr('id')+'&monto='+$('#importe').val()+'&fuzzy='+$('#fuzzy').val()+'&fuzziness='+$('#fuzziness').val(), function(data) {
          $('#detalle').html(data).fadeIn();
        });
      });
      $('.reciboTransporte').click(function() {
        $('.reciboTransporte').removeClass('act');
        $('.asientoTransporte').removeClass('act');
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
