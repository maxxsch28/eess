<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$titulo = "Nuevo asociado";

//ChromePhp::log($arrayYER);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
      <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php')?>
      <style>
          .table th{text-align: center}
          .container {
                width: 1301px;
            }
          body {
          padding-top: 60px;
          padding-bottom: 40px;
        }
        input {
              text-align:right;
          }
    </style>
    <link href="css/print.css" rel="stylesheet" type="text/css" media="print"/><meta name="viewport" content="width=device-width, initial-scale=1.0">
   </head>
  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
    <div class="container">
        <div class="row">
              <?php if(isset($_POST['idSocio'])){
                echo "<h2>Modificación asociado</h2>";
              } else {
                echo "<h2>Nuevo asociado</h2>";
              }?>
              <div class='col-md-5 pull-right'>
                <legend>Últimos eventos</legend>
                <ul><li>Aún sin movimientos</li></ul>
                <p>Acá van listados los últimos cambios que se hayan hecho del socio.<bR>Obviamente al momento del alta está vacío</p>
                <ul><li><?php $d1='28-09-1977'; $d2 = date("Y-m-d"); $diff = date_diff(date_create($d1), date_create($d2)); echo date("d/m/Y")." - Cumple ".$diff->format('%a')." días";?></li>
                <li>28/09/1997 - Cumple 20 años</li>
                <li>28/09/1977 - Nacimiento</li></ul>
              </div>
              <div class='col-md-7'>
              <form name='ingresoSocio' id='ingresoSocio' class="form-horizontal">
                <?php if(isset($_POST['idSocio'])){
                  echo "<input type='hidden' name='idSocio' value='$_POST[idSocio]'/>";
                }?>
                <fieldset>
                <legend>Datos personales</legend>
                  <div class="form-group">
                    <label for="idFletero" class="col-sm-3 control-label">Código Setup</label>
                    <div class="col-sm-3 input-group">
                      <input type="text" class="form-control" name='idFletero' id="idFletero" placeholder="Fletero Setup" required='required' data-plus-as-tab='true' /><span class="input-group-addon glyphicon glyphicon-barcode"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="nombre" class="col-sm-3 control-label">Nombre</label>
                    <div class="col-sm-7 input-group">
                      <input type="text" class="form-control" name='nombre' id="nombre" placeholder="Nombre y Apellido" required='required' data-plus-as-tab='true' ><span class="input-group-addon glyphicon glyphicon-user"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="domicilio" class="col-sm-3 control-label">Domicilio</label>
                    <div class="col-sm-7 input-group">
                      <input type="text" class="form-control" name='domicilio' id="domicilio" placeholder="Domicilio" required='required' data-plus-as-tab='true' ><span class="input-group-addon glyphicon glyphicon-home"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="celular" class="col-sm-3 control-label">Teléfono</label>
                    <div class="col-sm-7  input-group">
                      <input type="text" class="form-control" name='celular' id="celular" placeholder="Número celular" required data-date-format="dd/mm/yy" data-plus-as-tab='true' required='required'/><span class="input-group-addon glyphicon glyphicon-phone"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="email" class="col-sm-3 control-label">E-mail</label>
                    <div class="col-sm-7 input-group">
                      <input type="email" class="form-control" id="email" name='email' placeholder="Correo electrónico" data-plus-as-tab='true' ><span class="input-group-addon glyphicon glyphicon-envelope"></span>
                    </div>
                  </div>
  
              </fieldset>
              <fieldset>
                <legend>Datos sociales</legend>
              
                  <div class="form-group">
                    <label for="fechaIngreso" class="col-sm-3 control-label">Ingreso</label>
                    <div class="col-sm-3 input-group">
                      <input type="text" class="form-control" id="fechaIngreso" name="fechaIngreso" placeholder="Fecha de ingreso"  required="required" data-date-format="dd/mm/yyyy" data-plus-as-tab='true'/><span class="input-group-addon glyphicon glyphicon-calendar"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="fechaIngreso" class="col-sm-3 control-label">Acta</label>
                    <div class="col-sm-7 input-group">
                      <input type="text" class="form-control input-sm" id="libro" name="libro" placeholder="Libro"  required="required" data-plus-as-tab='true'/><span class='input-group-addon'></span>
                      <input type="text" class="form-control input-sm" id="foja" name="foja" placeholder="Foja"  required="required" data-plus-as-tab='true'/><span class='input-group-addon'></span>
                      <input type="text" class="form-control input-sm" id="acta" name="acta" placeholder="Acta"  required="required" data-plus-as-tab='true'/>
                      <span class="input-group-addon glyphicon glyphicon-number"></span>
                    </div> 
              </fieldset>
              <fieldset>
                <legend>Datos fiscales</legend>  
                <div class="form-group">
                  <label for="razonsocial" class="col-sm-3 control-label">Razón Social</label>
                  <div class="col-sm-7 input-group">
                    <input type="text" class="form-control" id="razonsocial" name='razonsocial' ="Razón social"><span class="input-group-addon glyphicon glyphicon-sunglasses"></span>
                  </div>
                </div>
                  <div class="form-group">
                    <label for="domicilio2" class="col-sm-3 control-label">Domicilio fiscal</label>
                    <div class="col-sm-7 input-group">
                      <input type="text" class="form-control" id="domicilio2" name='domicilio2' placeholder="Domicilio" data-plus-as-tab='true' ><span class="input-group-addon glyphicon glyphicon-home"></span>
                    </div>
                  </div>
                <div class="form-group">
                  <label for="cuit" class="col-sm-3 control-label">CUIT</label>
                  <div class="col-sm-3 input-group">
                    <input type="text" class="form-control" id="cuit" name='cuit' placeholder="xx-xxxxxxxx-x" pattern='^[0-9]{2}-[0-9]{8}-[0-9]$'><span class="input-group-addon glyphicon glyphicon-barcode"></span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="iva" class="col-sm-3 control-label">Condición IVA</label>
                  <div class="col-sm-7 input-group">
                    <label class="radio-inline">

                      <input type="radio" name="iva" class='iva' id="ri" value="ri" checked='checked'> Responsable Inscripto
                    </label>

                    <label class="radio-inline">

                      <input type="radio" name="iva" class='iva'  id="mo" value="mo"> Monotributo
                    </label>

                    <label class="radio-inline">

                      <input type="radio" name="iva" class='iva'  id="ot" value="ot"> Otro
                    </label>
                  </div>
                </div>
              </fieldset>
              <div class="modal-footer">
                <div id='msgbox' class='pull-left'></div>
                <button type="submit" class="btn btn-primary" id='cargaLectura'>Graba</button>
              </div>
            </form>
            </div>
          </div>
          <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
    <script>
    $(document).ready(function() {
       $("#idFletero").autocomplete({
        source: "func/ajaxBuscaSocio.php",
        minLength: 2,
        select: function( event, ui ) {
          $(this).value=ui.item.label;
          $(this).val(ui.item.label);
          $("#nombre").val(ui.item.label);
          $("#razonsocial").val(ui.item.label);
          $("#domicilio").val(ui.item.domicilio);
          $("#cuit").val(ui.item.cuit);
          $('.iva').attr('checked', false);
          if(ui.item.iva === '1'){
            // responsable inscripto
            $('#ri').attr('checked', true);
          } else if(ui.item.iva === '6'){
            // responsable inscripto
            $('#mo').attr('checked', true);
          } else {
            $('#ot').attr('checked', true);
          }
        }
      });

      $('#fechaIngreso').datepicker({
        autoclose: true
      });
 
      $('#cargaLectura').click(function() {
            var opciones= {
              success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
              //beforeSubmit: validate,
              url:       'func/ajaxSociosABM.php',         // override for form's 'action' attribute 
              type:      'post',       // 'get' or 'post', override for form's 'method' attribute 
              dataType: 'json'
          };
          $('#ingresoSocio').ajaxForm(opciones);    
      });

      function mostrarRespuesta(responseText){
        $('#botonEnviando').hide();
        $('#cargaLectura').fadeIn();
        $('#msgbox').removeClass('alert alert-danger');
        $('#msgbox').removeClass('alert alert-success');
        if(responseText.status==='error'){
          $('#msgbox').addClass('alert alert-danger');
          $('#msgbox').fadeIn('slow').html(responseText.msg);
        } else {
          $('#msgbox').addClass('alert alert-success');
          $('#msgbox').fadeIn('slow').html(responseText.msg);
          $("form").trigger("reset");
        }
      }
    });
    </script>
  </body>
</html>
