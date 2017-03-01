<?php
//xdebug_disable();
$nivelRequerido = 5;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo="Busca asiento 2.0";
$ambito['activo']['eess'] = $ambito['activo']['transporte'] = $ambito['activo']['integral'] = "";
$ambito['checked']['eess'] = $ambito['checked']['transporte'] = $ambito['checked']['integral'] = "";
if(isset($_GET['id'])&&is_numeric($_GET['id'])){
  $sqlBusqueda = "SELECT * FROM tmpbuscaasientos WHERE id=$_GET[id]";
  //fb($sqlBusqueda);
  $result = $mysqli->query($sqlBusqueda);
  if($result){
    $mysqli->query("UPDATE tmpbuscaasientos SET cantidadusos=cantidadusos+1 WHERE id=$_GET[id]");
    $rowHistorico = $result->fetch_assoc();
    $ambito['activo'][$rowHistorico['ambito']] = " active";
    $ambito['checked'][$rowHistorico['ambito']] = " checked";
  }
} else {
  // está vacio, para definir el ambito debo saber como se llama el alias
  $request = explode('.php', $_SERVER['REQUEST_URI']);
  switch($request[0]){
    case "/buscaAsientoIntegral2":
    $ambito['activo']['integral']=" active";
    $ambito['checked']['integral']=" checked";
    break;
    case "/buscaAsientoTransporte2":
    $ambito['activo']['transporte']=" active";
    $ambito['checked']['transporte']=" checked";
    break;
    default:
    $ambito['activo']['eess']=" active";
    $ambito['checked']['eess']=" checked";
    break;
  }
  if(isset($_GET['transporte'])){
    $ambito['activo']['transporte']=" active";
    $ambito['checked']['transporte']=" checked";
    $ambito['activo']['eess']="";
    $ambito['checked']['eess']="";
  }
  
}

unset($selectedTransporte, $selectedEESS);

if((!isset($_SESSION['cuentasContablesTransporte'])||(isset($rowHistorico['cuentaTransporte'])&&$rowHistorico['cuentaTransporte']>0))){
  // carga los datos de esta orden
  $sqlCuentas = "SELECT orden, nombre FROM dbo.plancuen WHERE imputable='S' ORDER BY Nombre;";
  fb($sqlCuentas);
  $stmt = odbc_exec2($mssql3, $sqlCuentas, __LINE__, __FILE__);
  $_SESSION['cuentasContablesTransporte']='';
  /*if(isset($rowHistorico)){
    fb("SELECT orden, nombre FROM [sqlcoop_dbshared].[dbo].[plancuen] WHERE imputable='S' AND orden=$rowHistorico[cuentaTransporte] ORDER BY Nombre;");
  }*/
  while($rowCuentas = odbc_fetch_array($stmt)){
    $selectedTransporte = (isset($rowHistorico['cuentaTransporte'])&&$rowHistorico['cuentaTransporte']==trim($rowCuentas['orden']))?" selected='selected'":"";
    $_SESSION['cuentasContablesTransporte'].="<option value='".trim($rowCuentas['orden'])."'$selectedTransporte>".utf8_encode($rowCuentas['nombre'])."</option>";
    /*if((isset($rowHistorico['cuentaTransporte'])&&$rowHistorico['cuentaTransporte']==trim($rowCuentas['orden']))){
      fb($rowCuentas['orden']);
    }*/
  }
}
if((!isset($_SESSION['cuentasContables'])||(isset($rowHistorico['cuentaEESS'])&&$rowHistorico['cuentaEESS']>0))){
  // carga los datos de esta orden
  $sqlCuentas = "SELECT IdCuentaContable, Descripcion FROM dbo.CuentasContables WHERE Imputable=1 ORDER BY Descripcion;";
  $stmt = odbc_exec2( $mssql, $sqlCuentas, __LINE__, __FILE__);
  $_SESSION['cuentasContables']='';
  while($rowCuentas = odbc_fetch_array($stmt)){
    $selectedEESS = (isset($rowHistorico['cuentaEESS'])&&$rowHistorico['cuentaEESS']==$rowCuentas['IdCuentaContable'])?" selected='selected'":"";
    $_SESSION['cuentasContables'].="<option value='$rowCuentas[IdCuentaContable]'$selectedEESS>$rowCuentas[Descripcion]</option>";
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
  <style type="text/css">
  .containerIntegral{
    width:1400px;
  }
  </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
<div class="container<?php if($ambito['activo']['eess']<>""||$ambito['activo']['transporte']<>""){echo "";}else{echo " containerIntegral";}?>" id='container'>
  <div class='row'>
    <h2></h2>
    <div class="col-md-<?php if($ambito['activo']['eess']<>""||$ambito['activo']['transporte']<>""){echo "6";}else{echo "4";}?>" style='border-right:1px solid #e5e5e5' id='ladoIzquierdo'>
      <form name='nuevaOP' id='nuevaOP' class='form-horizontal'>
      <h2>Busca en 
        <div class="btn-group">
          <button type="button" value="eess" class="ambito btn btn-info <?php echo $ambito['activo']['eess']?>" <?php echo $ambito['checked']['eess']?>>EESS</button>
          <button type="button" value="transporte" class="ambito btn btn-info <?php echo $ambito['activo']['transporte']?>"<?php echo $ambito['checked']['transporte']?>>Transporte</button>
          <button type="button" value="integral" class="ambito btn btn-info <?php echo $ambito['activo']['integral']?>"<?php echo $ambito['checked']['integral']?>>INTEGRAL</button>
        </div>
      </h2>
      <div class='bs-callout bs-callout-warning'>
        <input type='hidden' id='ambito' name='ambito' value='integral'/>
        <?php if(isset($rowHistorico)){?>
<!--         <input type='hidden' id='idBuscaAsiento' name='idBuscaAsiento' value='<?php echo $_GET['id']?>'/> -->
        <?php }?>
        <fieldset>
          <legend>Filtros</legend>
          <div class='col-md-3'>
            <div class="form-group" id='rop'> 
              <div class="controls">
                <div class="input-group">
                  <input type='text' name='importe' id='importe' class="input-sm form-control col-md-5" pattern="[0-9\.]{1,}" maxlength="12" data-plus-as-tab='true' plasceholder='Importe' <?php if(isset($rowHistorico)&&$rowHistorico['importe']<>0){echo "value='$rowHistorico[importe]'";}?>/><span class="input-group-addon" id='errorFactura'>$</span>
                </div>
              </div>
            </div>
          </div>
          <div class='col-md-8 col-md-offset-1'>
            <div class="form-group">  
              <div class="controls">
                <div class="input-group" id='rop'> <!--2015-12-31 => 12-31-2015-->
                  <input type='text' name='rangoInicio' id='rangoInicio' class="input-sm form-control" 
                  value="<?php if(isset($rowHistorico)&&$rowHistorico['rangoinicio']<>0){echo substr($rowHistorico['rangoinicio'], 8,2).'/'.substr($rowHistorico['rangoinicio'], 5,2).'/'.substr($rowHistorico['rangoinicio'], 2,2);} else {echo "01/01/".date("y");}?>" data-date-format="dd/mm/yy"  data-plus-as-tab='true'/>
                  <span class="input-group-addon"></span>
                  <input type='text' name='rangoFin' id='rangoFin' class="input-sm form-control"  value="<?php if(isset($rowHistorico)&&$rowHistorico['rangofin']<>0){echo substr($rowHistorico['rangofin'], 8,2).'/'.substr($rowHistorico['rangofin'], 5,2).'/'.substr($rowHistorico['rangofin'], 2,2);} else {echo "31/12/".date("y");}?>" data-date-format="dd/mm/yy" data-plus-as-tab='true'/>
                  <span class="input-group-addon presetAnio btn" id="1000">&#8734; </span>
                  <span class="input-group-addon presetAnio btn" id="<?php echo date('y', strtotime("-1 year"))?>"><?php echo date('y', strtotime("-1 year"))?></span>
                  <span class="input-group-addon presetAnio btn<?php if(!isset($_GET['id']))echo" label-success";?>" id="<?php echo date('y')?>"><?php echo date('y')?></span>
                </div>
              </div>
            </div>
          </div>
        </fieldset>
        <fieldset>
          <div class='col-md-3'>
            <div class="form-group" id='rfuzzy'> 
              <div class="controls">
                <div class="input-group">
                  <input type='text' name='fuzziness' id='fuzziness' class="input-sm form-control" data-plus-as-tab='true' placeholder='Fuzzy' <?php if(isset($rowHistorico)&&$rowHistorico['fuzzyness']<>0){echo "value='$rowHistorico[fuzzyness]'";} else {echo "disabled='disabled'";}?>/>
                <span class="input-group-addon"><input type="checkbox" value="1" name='fuzzy' id='fuzzy'<?php if(isset($rowHistorico)&&$rowHistorico['fuzzyness']<>0){echo "checked='checked'";}?>></span>
                </div>
              </div>
            </div>
          </div>
          <div class='col-md-8 col-md-offset-1'>
            <div class="form-group" id='rleyenda'> 
              <div class="controls">
                <div class="input-group">
                  <input type='text' name='leyenda' id='leyenda' class="input-sm form-control" data-plus-as-tab='true' placeholder='Leyenda'<?php if(isset($rowHistorico)&&$rowHistorico['leyenda']<>""){echo "value='$rowHistorico[leyenda]'";} else {echo "disabled='disabled'";}?>/>
                <span class="input-group-addon"><input type="checkbox" value="1" name='buscaLeyenda' id='buscaLeyenda'<?php if(isset($rowHistorico)&&$rowHistorico['leyenda']<>""){echo "checked='checked'";}?>></span>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group col-md-offset-1">
              <div class="controls">
              <div class='col-md-6'>
                <label class="radio" for='conciliando'>
                    <input type="checkbox" value="1" name='conciliando' id='conciliando'> Muestra checks
                </label>
              </div>
              <div class='col-md-5 col-md-offset-1'>
                <label class="radio" for='ord_imp'>
                    <input type="checkbox" value="1" name='ord_imp' id='ord_imp' checked> Ordena por importe
                </label>
              </div>
              <div class='col-md-6'>
                <label class="radio" for='excluyeAnulados'>
                    <input type="checkbox" value="1" name='excluyeAnulados' id='excluyeAnulados'> Excluye anulados
                </label>
              </div>
            </div></div>
        </fieldset>
        <fieldset>
          <div class='col-md-5'>
            <div class="form-group">  
            <div class="controls">
            <div class="input-group" id='ractivaCuentas'>
              <select name='cuentaEESS' id='cuentaEESS' class='input-sm form-control' <?php if(!isset($selectedEESS))echo "disabled"?>>
                    <option value='' <?php if(!isset($selectedEESS))echo "selected='selected'"?>>EESS</option>
                    <?php echo $_SESSION['cuentasContables'] ?>
              </select><span class="input-group-addon"><input type="checkbox" value="1" name='filtraCuentaEESS' id='filtraCuentaEESS'<?php if(isset($selectedEESS)){echo "checked='checked'";}?>></span>	
            </div></div></div>
            </div>
            <div class='col-md-6 col-md-offset-1'>
            <div class="form-group">  
            <div class="controls">
            <div class="input-group" id='ractivaCuentas'>
              <select name='cuentaTransporte' id='cuentaTransporte' class='input-sm form-control' <?php if(!isset($selectedTransporte))echo "disabled"?>>
                    <option value='' <?php if(!isset($selectedTransporte))echo "selected='selected'"?>>Transporte</option>
                    <?php echo $_SESSION['cuentasContablesTransporte'] ?>
              </select><span class="input-group-addon"><input type="checkbox" value="1" name='filtraCuentaTransporte' id='filtraCuentaTransporte'<?php if(isset($selectedTransporte)){echo "checked='checked'";}?>></span>	
            </div></div></div>
          </div>
        </fieldset>
        <fieldset>
          <div class="form-group col-md-6" id='botonEnvio'>
            <label for='enviar' class="control-label"></label>
            <div class="controls"> 
              <button class="btn btn-primary btn-lg enviar" id='enviar'>Buscar &raquo;</button>
            </div>
          </div>   
          <div class="form-group col-md-6 pull-right">
            <label for='limpiar' class="control-label"></label>
            <div class="controls"> 
              <span class="btn btn-danger limpiar pull-right" id='limpiar'>Limpiar</span>
            </div>
          </div>   
          <div class="form-group col-md-6" id='botonEnviando' style="display:none">
            <label for='enviandor' class="control-label"></label>
            <div class="controls"> 
              <button class="btn btn-primary btn-lg" >Buscando....</button>
            </div>
          </div>
        </fieldset>
        <fieldset id='detalleMovimiento'>
          
          <legend>Detalle movimiento<button type="button" class="close pull-right" aria-label="Close" id='cierraDetalle'><span aria-hidden="true">&times;</span></button></legend>
          <div class='well well-sm' id='detalle'>
          </div>
        </fieldset>
        <fieldset>
          <legend>Ultimas consultas <span id='alternoMiosTodos' class='small'><span class='label label-success'>M</span> / <span class='label label-primary'>S</span><span class='label label-danger'>J</span><span class='label label-success small'>M</span></span></legend>
          <div id='muestraHistorico' style='height:140px;overflow-y: scroll;max-height:140px'>
          <table class='highlight table table-striped table-condensed' id='historico'>
            <tbody></tbody>
          </table>
          </div>
        </fieldset>
      </form>
      </div>
    </div>
    <div class="col-md-<?php if($ambito['activo']['eess']<>""||$ambito['activo']['transporte']<>""){echo "6";}else{echo "8";}?>" id='ladoDerecho'><br/>
        <div style='height:80%;overflow-y: scroll;max-height:600px'>
        <div class='col-md-<?php if($ambito['activo']['eess']<>""){echo "12";}elseif ($ambito['activo']['integral']<>"") {echo "6";} else {echo "0' style='display:none";}?>' id='listaAsientosEESS'>
        <legend>EESS</legend>
          <table id='libroDiario' class='table'>
          </table>
        </div>
        <div class='col-md-<?php if($ambito['activo']['transporte']<>""){echo "12";}elseif ($ambito['activo']['integral']<>"") {echo "6";} else {echo "0' style='display:none";}?>' id='listaAsientosTransporte'>
        <legend>Transporte</legend>
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
    $('#rangoInicio').datepicker();
    $('#rangoFin').datepicker();
    $('#fuzzy').click(function() {
      if ($(this).attr('checked')) {
        $('#fuzziness').val('1');
        $('#fuzziness').removeAttr('disabled').focus();
      } else {
        $('#fuzziness').attr('disabled', true);
        $('#fuzziness').val('');
      }
    });
    $('#buscaLeyenda').click(function() {
      if ($(this).attr('checked')) {
        $('#leyenda').removeAttr('disabled').focus();
      } else {
        $('#leyenda').attr('disabled', true);
      }
    });
    $('#filtraCuentaEESS').click(function() {
      if ($(this).attr('checked')) {
        $('#cuentaEESS').removeAttr('disabled').focus();
      } else {
        $('#cuentaEESS').attr('disabled', true);
      }
    });
    $('#filtraCuentaTransporte').click(function() {
      if ($(this).attr('checked')) {
        $('#cuentaTransporte').removeAttr('disabled').focus();
      } else {
        $('#cuentaTransporte').attr('disabled', true);
      }
    });
    $('.presetAnio').click(function(){
      var year = $(this).attr('id');
      $('.presetAnio').removeClass('label-success');
      $(this).addClass('label-success');
      if(year==='1000'){
        $('#rangoInicio').val('01/01/11');
        $('#rangoFin').val('31/12/69');
      } else {
        $('#rangoInicio').val('01/01/'+year);
        $('#rangoFin').val('31/12/'+year);
      }
    });
    $('.ambito').click(function(){
      $(this).siblings().removeClass("active" );
      $(this).addClass("active");
      if($(this).val()==='eess'){
        $('#listaAsientosTransporte').hide();
        $('#listaAsientosEESS').show();
        $('#container').removeClass('containerIntegral');
        $('#listaAsientosEESS').removeClass('col-md-6');
        $('#listaAsientosEESS').addClass('');
        $('#listaAsientosTransporte').removeClass('col-md-6');
        $('#ladoDerecho').removeClass('col-md-8');
        $('#ladoDerecho').addClass('col-md-6');
        $('#ladoIzquierdo').removeClass('col-md-4');
        $('#ladoIzquierdo').addClass('col-md-6');
      } else if($(this).val()==='transporte'){
        $('#listaAsientosTransporte').show();
        $('#listaAsientosEESS').hide();
        $('#container').removeClass('containerIntegral');
        $('#listaAsientosTransporte').removeClass('col-md-6');
        $('#listaAsientosTransporte').addClass('');
        $('#listaAsientosEESS').removeClass('col-md-6');
        $('#ladoDerecho').removeClass('col-md-8');
        $('#ladoDerecho').addClass('col-md-6');
        $('#ladoIzquierdo').removeClass('col-md-4');
        $('#ladoIzquierdo').addClass('col-md-6');
      } else {
        $('#container').addClass('containerIntegral');
        $('#listaAsientosTransporte').show();
        $('#listaAsientosTransporte').removeClass('col-md-12');
        $('#listaAsientosTransporte').addClass('col-md-6');
        $('#listaAsientosEESS').show();
        $('#listaAsientosEESS').removeClass('col-md-12');
        $('#listaAsientosEESS').addClass('col-md-6');
        $('#ladoDerecho').removeClass('col-md-6');
        $('#ladoDerecho').addClass('col-md-8');
        $('#ladoIzquierdo').removeClass('col-md-6');
        $('#ladoIzquierdo').addClass('col-md-4');
      }
    });
    muestraHistorico();
    setInterval(function(){
      muestraHistorico();
    }, 10000);
    
    $('#alternoMiosTodos').click(function(){
      muestraHistorico(1);
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
    function muestraHistorico(alterno){
      $('#historico tbody').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
      $.ajax({ url: "/func/ajaxHistorico.php?alterno="+alterno, success: function(data){
        //Update your dashboard gauge
        $('#historico tbody').html(data).fadeIn();
        $('.histAsiento').click(function(){
          // obtengo Id
          var id=$(this).attr('id');
          var url = location.href;
          var url2 = url.split("?");
          location.href = url2[0]+"?id="+id;
        });
      }, dataType: "html"});
    }
    function mostrarLoader(){
      $('#botonEnvio').hide();
      $('#botonEnviando').show();
      $('#libroDiario').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
    };
    $('#cierraDetalle').click(function(){
      $('#detalle').html("").fadeIn();
    });
    function mostrarRespuesta(responseText){
      $('#detalle').html("").fadeIn();
      $('#botonEnviando').hide();
      $('#botonEnvio').fadeIn();
      $('#libroDiario').html(responseText).slideDown('slow');
      $('.asiento').click(function() {
        $('.asiento').removeClass('act');
        $('.recibo').removeClass('act');
        $(this).addClass('act');
        $('#detalle').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
        $.get('func/muestraDetalleMovimiento.php?idAsiento='+$(this).attr('id')+'&monto='+$('#importe').val()+'&fuzzy='+$('#fuzzy').val()+'&fuzziness='+$('#fuzziness').val(), function(data) {
          $('#detalleMovimiento').show('slow');
          $('#detalle').html(data).fadeIn();
        });
      });
      $('.recibo').click(function() {
        $('.recibo').removeClass('act');
        $('.asiento').removeClass('act');
        $(this).addClass('act');
        $('#detalle').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
        $.get('func/muestraDetalleMovimiento.php?idAsiento='+$(this).attr('id')+'&monto='+$('#importe').val()+'&fuzzy='+$('#fuzzy').val()+'&fuzziness='+$('#fuzziness').val(), function(data) {
          $('#detalleMovimiento').show('slow');
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
        $.get('func/muestraDetalleMovimientoTransporte.php?idAsiento='+$(this).attr('id'), function(data) {
          $('#detalleMovimiento').show('slow');
          $('#detalle').html(data).fadeIn();
        });
      });
      $('.recibo').click(function() {
        $('.recibo').removeClass('act');
        $('.asiento').removeClass('act');
        $(this).addClass('act');
        $('#detalle').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
        $.get('func/muestraDetalleMovimientoTransporte.php?idAsiento='+$(this).attr('id')+'&monto='+$('#importe').val()+'&fuzzy='+$('#fuzzy').val()+'&fuzziness='+$('#fuzziness').val(), function(data) {
          $('#detalleMovimiento').show('slow');
          $('#detalle').html(data).fadeIn();
        });
      });
    }
    <?php if(isset($_POST['srch-term'])){  // viene del search header ?>
      $('#importe').val(<?php echo $_POST['srch-term'];?>);
      $('#enviar').click();
    <?php }
      if(isset($_GET['id'])){?>
        $('#enviar').click();
        $('#limpiar').show();
        $('#limpiar').click(function(){
          $('#importe').val('');
          if($('#fuzzy').is(":checked")){
            $('#fuzzy').click();
          }
          if($('#buscaLeyenda').is(":checked")){
            $('#buscaLeyenda').click();
          }
          if($('#filtraCuentaEESS').is(":checked")){
            $('#filtraCuentaEESS').click();
            $('#cuentaEESS').attr('disabled', 'disabled');
          }
          if($('#filtraCuentaTransporte').is(":checked")){
            $('#filtraCuentaTransporte').click();
            $('#cuentaTransporte').attr('disabled', 'disabled');
          }
          $('#<?php echo date("y");?>').click();
        });
      <?php }
    ?>
  });
</script>
</body>
</html>
