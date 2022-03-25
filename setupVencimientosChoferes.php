<?php
$nivelRequerido = 6;

include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
/*
  - Primero debe ingresarse el proveedor, buscador como el de movistar para elegir por codigo, cuit o por nombre
  - si es Coopetrans luego selecciona el punto de venta, si es 7 levanta los datos desde Setup, si es 8 o 9 desde Calden. No deja modificar los importes.
  
*/

$titulo = "Carga vencimientos choferes";
$modifica = false;
$fechaFactura = false;
setlocale(LC_ALL, 'es_ES');



header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");


?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      #listado {
        height: 774px;
        overflow-y:scroll;
      }
      #listaSocios {
        
      }
      #listaSocios tbody td, td {
        border: 0 0 1px 0;
        
      }
    </style>
  
  </head>

  <body>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
    <div class="container">
    <!-- Main hero unit for a primary marketing message or call to action -->
      <div class='row'>
        <form name='vencimientos' id='vencimientos' class='form-horizontal'>
        <div class='row'>
          <div class='col-md-5'> 
            <fieldset>
              <div class="form-group ">
                <legend>Chofer</legend> 
                <div class="controls">
                  <div class="input-group"> 
                    <input type='text' name='busca' id='busca' maxlength="20" class='input-sm form-control'/>
                    <span class="input-group-addon btn" id='addCliente'>-></span>
                    <input type='text' name='socio' id='socio' maxlength="255" class='input-sm form-control ui-widget' disabled='disabled'/>
                  </div>
                </div>
              </div>
            </fieldset>
            <fieldset>
              <legend>Datos chofer</legend>
              <div class="form-group">
                <label for="vtocarnet" class="col-sm-5 control-label">Carnet</label>
                <div class="col-sm-3 input-group">
                  <input type="date" class="form-control d" name='vtocarnet' id="vtocarnet" placeholder="" min="1900-01-01" data-plus-as-tab='true'>
                  <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                </div>
              </div>
              <div class="form-group">
                <label for="vtopsicofi" class="col-sm-5 control-label">Psicofísico</label>
                <div class="col-sm-3 input-group">
                  <input type="date" class="form-control d" name='vtopsicofi' id="vtopsicofi" placeholder="" min="1900-01-01" data-plus-as-tab='true'>
                  <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                </div>
              </div>
              <div class="form-group">
                <label for="vtocharla" class="col-sm-5 control-label">Charla</label>
                <div class="col-sm-3 input-group">
                  <input type="date" class="form-control d" name='vtocharla' id="vtocharla" placeholder="" min="1900-01-01" data-plus-as-tab='true'>
                  <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                </div>
              </div>
              <div class="form-group">
                <label for="segurovida" class="col-sm-5 control-label">Seguro acc. personales</label>
                <div class="col-sm-3 input-group">
                  <input type="date" class="form-control d" name='segurovida' id="segurovida" placeholder="" min="1900-01-01" data-plus-as-tab='true'>
                  <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                </div>
              </div>
            </fieldset>

            <fieldset>
              <legend>Chasis <span id='chasis'></span></legend>
              <input type='hidden' name='equipo6' id='equipo6'/>
              <div class="form-group">
                <label for="vtoveritec6" class="col-sm-5 control-label">Verificación RTO</label>
                <div class="col-sm-3 input-group">
                  <input type="date" class="form-control d" name='vtoveritec6' id="vtoveritec6" placeholder="" min="1900-01-01" data-plus-as-tab='true'>
                  <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                </div>
              </div>
              <div class="form-group">
                <label for="ruta6" class="col-sm-5 control-label">RUTA</label>
                <div class="col-sm-3 input-group">
                  <input type="date" class="form-control d" name='ruta6' id="ruta6" placeholder="" min="1900-01-01" data-plus-as-tab='true'>
                  <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                </div>
              </div>
              <div class="form-group">
                <label for="vtopoliza6" class="col-sm-5 control-label">Seguro contra terceros</label>
                <div class="col-sm-3 input-group">
                  <input type="date" class="form-control d" name='vtopoliza6' id="vtopoliza6" placeholder="" min="1900-01-01" data-plus-as-tab='true'>
                  <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                </div>
              </div>
              <div class="form-group">
                <label for="segurocarg6" class="col-sm-5 control-label">Seguro de carga</label>
                <div class="col-sm-3 input-group">
                  <input type="date" class="form-control d" name='segurocarg6' id="segurocarg6" placeholder="" min="1900-01-01" data-plus-as-tab='true'>
                  <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                </div>
              </div>
            </fieldset>

            <fieldset>
              <legend>Acoplado <span id='acoplado'></span></legend>
              <input type='hidden' name='equipo2' id='equipo2'/>
              <div class="form-group">
                <label for="vtoveritec2" class="col-sm-5 control-label">Verificación RTO</label>
                <div class="col-sm-3 input-group">
                  <input type="date" class="form-control d" name='vtoveritec2' id="vtoveritec2" placeholder="" min="1900-01-01" data-plus-as-tab='true'>
                  <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                </div>
              </div>
              <div class="form-group">
                <label for="ruta2" class="col-sm-5 control-label">RUTA</label>
                <div class="col-sm-3 input-group">
                  <input type="date" class="form-control d" name='ruta2' id="ruta2" placeholder="" min="1900-01-01" data-plus-as-tab='true'>
                  <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                </div>
              </div>
              <div class="form-group">
                <label for="vtopoliza2" class="col-sm-5 control-label">Seguro contra terceros</label>
                <div class="col-sm-3 input-group">
                  <input type="date" class="form-control d" name='vtopoliza2' id="vtopoliza2" placeholder="" min="1900-01-01" data-plus-as-tab='true'>
                  <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                </div>
              </div>
              <div class="form-group">
                <label for="segurocarg2" class="col-sm-5 control-label">Seguro de carga</label>
                <div class="col-sm-3 input-group">
                  <input type="date" class="form-control d" name='segurocarg2' id="segurocarg2" placeholder="" min="1900-01-01" data-plus-as-tab='true'>
                  <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                </div>
              </div>
            </fieldset>
            <button id='actualiza' class='btn btn-primary btn-block' disabled>Actualizar</button>
          </div>  
          <div class='col-md-6'>
            <legend>Asociados</legend>
            <div  id='listado'>
            <table class='table' id='listaSocios'>
              <tbody>

              </tbody>
            </table>
          </div>
          </div>
        </div>
        </form>   
                        
      </div>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
  <script>
    $(document).ready(function() {
      var fechaAmarilla = addMonths(new Date(), +1);
      var hoy = new Date();
      $("#busca").autocomplete({
        source: "func/ajaxBuscaChofer.php",
        minLength: 2,
        select: function( event, ui ) {
          $(this).value=ui.item.label;
          $(this).val(ui.item.label);
          $('#busca').val(ui.item.value);
          $("#socio").val(ui.item.value+' - '+ui.item.label);
          cargaVencimientos(ui.item.value);
        }
      });

      $('#busca').focus();

      function cargaVencimientos(idChofer){
        console.debug('cargaVencimientos');
        $.post('func/setupAxVencimientosChoferes.php', { idChofer: idChofer }, function(data) {
            //console.log(data);
            //console.log(data.vtopsicofi);
            $('#chasis').html("");
            $('#acoplado').html("");
            populate('#vencimientos', data);
            $('#chasis').html(data.patente6);
            $('#acoplado').html(data.patente2);

          },'json');
      }
      
      $('#vencimientos').submit(function() {
        var opciones= {
          beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
          success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
          url:       'func/setupAxVencimientosChoferes.php',         // override for form's 'action' attribute 
          type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
        };
        
        $(this).ajaxSubmit(opciones); 
        return false; 
      });

      $('#actualiza').click(function(){
        console.log('Actualiza datos');
      });

      function mostrarLoader(){
        $('.glyphicon').removeClass('glyphicon-calendar');
        $('.glyphicon').addClass('glyphicon-refresh');
      }

      $('.form-control').click(function(){
        $('#actualiza').prop('disabled', false);
        $('#actualiza').html('Actualiza');
      });

      function mostrarRespuesta(responseText){
        if(responseText === 'ok'){
          $('#actualiza').prop('disabled', true);
          $('#actualiza').html('Actualizado');
          $('.glyphicon').removeClass('glyphicon-refresh');
          $('.glyphicon').addClass('glyphicon-calendar');
        } else {

        }
      }

      function muestraSocios(){
        console.debug('muestraSocios');
        $.get("http://cooptransporte.ddns.net:26126/socios", function(data) {
          var vencido = '';
          var hoy = new Date();
          $.each(data.recordset, function(key, value){
            var d1 = new Date(value.vtopsicofi);
            var d2 = new Date(value.vtocarnet);
            var d3 = new Date(value.vtocharla);
            var d4 = new Date(value.segurovida);
            var d5 = new Date(value.vtoveritec2);
            var d6 = new Date(value.ruta2);
            var d7 = new Date(value.vtopoliza2);
            var d8 = new Date(value.segurocarg2);
            var d9 = new Date(value.vtoveritec6);
            var d10= new Date(value.ruta6);
            var d11= new Date(value.vtopoliza6);
            var d12= new Date(value.segurocarg6);
            if(d1 <= hoy || d2 <= hoy || d3 <= hoy || d4 <= hoy || d5 <= hoy || d6 <= hoy || d7 <= hoy || d8 <= hoy || d9 <= hoy || d10 <= hoy || d11 <= hoy || d12 <= hoy){
              vencido = "<span class='badge badge-danger'>VENCIDO</span>";
            } else {
              vencido = "";
            }
            var fletero = "";
            if(value.cuit !== value.cuil){
              // es chofer
              fletero = " ("+value.nombreFletero+")";
            }
            $('#listaSocios tbody').append("<tr><td><a href='#' class='actualizaSocio' name='"+ value.nombreChofer + fletero +"' id='" + value.idChofer +"'>" + value.nombreChofer + fletero + "</a></td><td>" + vencido  +"</td></tr>");

          });
          $('.actualizaSocio').click(function(){
            var idChofer = $(this).attr('id');
            var chofer = $(this).attr('name');
            cargaVencimientos(idChofer);
            $('#busca').val(idChofer);
            $("#socio").val(idChofer+' - '+chofer);
          });

        }, 'json');
      }

      muestraSocios();

      function populate(frm, data) {
        $('#actualiza').prop('disabled', false);
        console.debug('populate');
        $('.form-control').removeClass('alert alert-danger alert-warning');
        $('.d').val('');
        $.each(data, function(key, value){
          var compara = new Date(value);
          if(compara > hoy && compara < fechaAmarilla){
            $('#'+key).addClass('alert alert-warning');
          } else if (compara <= hoy){
            $('#'+key).addClass('alert alert-danger');
          } else {
            $('#'+key).removeClass('alert alert-danger alert-warning');
          }
          $('#'+key).val(value);
        });

        //$('.glyphicon').removeClass('glyphicon-refresh');
        //$('.glyphicon').addClass('glyphicon-calendar');
      }

      
      function addMonths(date, months) {
        var d = date.getDate();
        date.setMonth(date.getMonth() + +months);
        if (date.getDate() != d) {
          date.setDate(0);
        }
        return date;
      }

    });
  </script>
</body>
</html>