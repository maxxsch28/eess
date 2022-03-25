<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo="Descarga camión | YPF";

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
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
      <div class='row'>
        <div class="col-md-6">
          <h2>Ingreso de Remitos</h2>
          <form class="form-horizontal well formDescarga" role="form" id='formDescarga' name='formDescarga'>
          <input type='hidden' name='tipo' value='remito'/>
          <div class="modal-body" id='formulario'>
            <div class="form-group">
              <label for="fecha" class="col-sm-3 control-label">Recepción</label>
              <div class="col-sm-7 input-group">
                <input type="text" class="form-control" id="fecha" name="fecha" required="required" data-date-format="dd/mm/yyyy" data-plus-as-tab='true'><span class="input-group-addon glyphicon glyphicon-calendar"></span>
              </div> 
            </div>
            <div class="form-group">
              <label for="remito" class="col-sm-3 control-label">Remito</label>
              <div class="col-sm-7 input-group">
                <input type="text" class="form-control" name="remito1" required="required" data-plus-as-tab='true' placeholder='PV'><span class="input-group-addon"></span>
                <input type="text" class="form-control" name="remito2" required="required" data-plus-as-tab='true' placeholder='Numero'>
              </div>
            </div>
            <div class="form-group">
              <label for="remito" class="col-sm-3 control-label">OP</label>
              <div class="col-sm-7 input-group">
                <input type="text" class="form-control" name="op" required="required" data-plus-as-tab='true' placeholder='OP'>
              </div>
            </div>
            <div class="form-group">
              <label for="inputUD" class="col-sm-3 control-label">Ultra Diesel</label>
              <div class="col-sm-7 input-group">
                <input type="number" class="form-control litros" name="inputTq6" placeholder="Tanque 6" data-plus-as-tab='true' min='1000' max='40000'>
              </div>
            </div>
            <div class="form-group">
              <label for="totalNS" class="col-sm-3 control-label">Nafta Super</label>
              <div class="col-sm-7 input-group">
                <input type="number" class="form-control litros ns" id='totalNS' name="totalNS" placeholder="Total" data-plus-as-tab='true' min='1000' max='30000'><span class="input-group-addon" id='inputTq5'>Tanque 5</span>
                <input type="number" class="form-control litros ns" id='inputTq3' name="inputTq3" placeholder="Tanque 3" data-plus-as-tab='true' max='20000'>
              </div>
            </div>
            <div class="form-group">
              <label for="inputNP" class="col-sm-3 control-label">Nafta Infinia</label>
              <div class="col-sm-7 input-group">
                <input type="number" class="form-control litros" name="inputTq2" placeholder="Tanque 2" data-plus-as-tab='true' min='1000' max='20000'>
              </div>
            </div>
            <div class="form-group">
              <label for="totalEd" class="col-sm-3 control-label">Infinia Diesel</label>
              <div class="col-sm-7 input-group">
                <input type="number" class="form-control litros ed" id='totalEd' name="totalEd" placeholder="Total" data-plus-as-tab='true' min='1000' max='20000'><span class="input-group-addon" id='inputTq4'>Tanque 4</span>
                <input type="number" class="form-control litros ed" id='inputTq1' name="inputTq1" placeholder="Tanque 1" data-plus-as-tab='true'  max='31000'>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div id='msgboxremito'></div>
            <button type="submit" class="btn btn-primary" id='graba'>Graba</button>
          </div>
          </form>
        </div>
        
        
        <div class="col-md-6 pull-right" id='yer'>
          <h2>YPF en Ruta</h2>
          <form class="form-horizontal formDescarga well" role="form" id='formYER' name='formYER'>
          <input type='hidden' name='tipo' value='yer'/>
          <div class="modal-body" id='formulario2'>
            <div class="form-group">
              <label for="fecha" class="col-sm-3 control-label">Salida</label>
              <div class="col-sm-7 input-group">
                <input type="text" class="form-control" id="fecha2" name="fecha" required="required" data-date-format="dd/mm/yyyy" data-plus-as-tab='true'><span class="input-group-addon glyphicon glyphicon-calendar"></span>
              </div> 
            </div>
            <div class="form-group">
              <label for="remito" class="col-sm-3 control-label">RV</label>
              <div class="col-sm-7 input-group">
                <input type="number" class="form-control" name="rv" required="required" data-plus-as-tab='true' placeholder='RV' max='29999999'>
              </div>
            </div>
            <div class="form-group">
              <label for="totalUD" class="col-sm-3 control-label">Ultra Diesel</label>
              <div class="col-sm-7 input-group">
                <input type="number" class="form-control litros ud" id='yerUD' name="yerUD" placeholder="Ultra" data-plus-as-tab='true' min='0' max='10000' step="0.01">
              </div>
            </div>
            <div class="form-group">
              <label for="inputNS" class="col-sm-3 control-label">Nafta Super</label>
              <div class="col-sm-7 input-group">
                <input type="number" class="form-control litros" name="yerNS" placeholder="Super" data-plus-as-tab='true' min='0' max='20000' step="0.01">
              </div>
            </div>
            <div class="form-group">
              <label for="inputNP" class="col-sm-3 control-label">Nafta Infinia</label>
              <div class="col-sm-7 input-group">
                <input type="number" class="form-control litros" name="yerNI" placeholder="Infinia" data-plus-as-tab='true' min='0' max='10000' step="0.01">
              </div>
            </div>
            <div class="form-group">
              <label for="totalEd" class="col-sm-3 control-label">Infinia Diesel</label>
              <div class="col-sm-7 input-group">
                <input type="number" class="form-control litros ed" id='yerID' name="yerID" placeholder="Infinia Diesel" data-plus-as-tab='true' min='0' max='20000' step="0.01">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div id='msgboxyer'></div>
            <button type="submit" class="btn btn-primary" id='grabaYER'>Graba</button>
          </div>
          </form>
        </div>
      </div>
    <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>

    </div> <!-- /container -->
<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
<script>
$(document).ready(function() {
  $('#fecha').datepicker({
    autoclose: true
  });
   $('#fecha2').datepicker({
    autoclose: true
  });
  $('.ns').change(function(){
    if(isNaN($('#inputTq3').val())){
      var tq3 = 0;
    } else {
      var tq3 = $('#inputTq3').val();
    }
    var tq5 = parseInt($('#totalNS').val()-tq3);
    $('#inputTq5').html(tq5);
  });
  
  $('.ed').change(function(){
    if(isNaN($('#inputTq1').val())){
      var tq1 = 0;
    } else {
      var tq1 = $('#inputTq1').val();
    }
    var tq4 = parseInt($('#totalEd').val()-tq1);
    $('#inputTq4').html(tq4);
  });
  var opciones= {
    beforeSubmit: validate, //funcion que se ejecuta antes de enviar el form
    success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
    url:       'func/asignaTanquesRemito.php',         // override for form's 'action' attribute 
    type:      'post',       // 'get' or 'post', override for form's 'method' attribute
    dataType:  'json'
  };
  //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
  $('.formDescarga').ajaxForm(opciones) ; 

  //lugar donde defino las funciones que utilizo dentro de "opciones"
  function mostrarLoader(){
      $('#loader_gif').fadeIn("slow"); //muestro el loader de ajax
  };
              
  function validate(formData, jqForm, options) { 
    // valido que todos los litros despachados se hayan asignado a tanques.
    return true;
  }

  function mostrarRespuesta(responseText){
    //console.log(responseText);
    $("#loader_gif").fadeOut("slow"); // Hago desaparecer el loader de ajax
      
      if(responseText.status==='success'){
        $('#formulario').html(responseText.message).fadeIn('slow');
      } else {
        $('#msgbox'+responseText.tipo).html(responseText.message).fadeIn('slow');
      }

      // eliminar bloque en box izquierdo
      // mejorar efecto de feedback
      // eliminar opcion dedistribuir el mismo camion de nuevo
  };
});
  </script>
  </body>
</html>
