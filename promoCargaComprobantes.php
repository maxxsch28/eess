<?php
$nivelRequerido = 6;

include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
/*
  - Primero debe ingresarse el proveedor, buscador como el de movistar para elegir por codigo, cuit o por nombre
  - si es Coopetrans luego selecciona el punto de venta, si es 7 levanta los datos desde Setup, si es 8 o 9 desde Calden. No deja modificar los importes.
  
*/
$titulo = "Carga cupones promociones internas";

$modifica = false;
$fechaFactura = false;
setlocale(LC_ALL, 'es_ES');
?>
<!DOCTYPE html>
<html lang="es">
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
      <!-- Main hero unit for a primary marketing message or call to action -->
        <div class='row'>
          <h2>Carga de comprobantes</h2>
            <form name='nuevaOP' id='nuevaOP' class='form-horizontal well'>
            <div class='row'>
              <div class='col-md-9'>
                <table id='cupones' class='table'>
                  <thead>
                    <tr><th width='20%'>Empleado</th><th width='18%' colspan='2'>Factura</th><th width='10%'>Fecha canje</th><th>Datos factura</th><th>Mes asignado</th><th><input type='checkbox' name='viejos' id='viejos' value='1'/></th></tr>
                    <tr><td><select name='empleado' id='empleado' data-plus-as-tab='true' class='input-sm form-control' method='POST'>
                    <?php
                    asort($empleado[1]);
                    foreach($empleado[1] as $idEmpleado => $empleado) {
                      if(substr($empleado,0,2)<>'ZZ'){
                      echo "<option value='$idEmpleado'>$empleado</option>";}
                    }?>
                    </select></td>
                    <td><input type='hidden' name='IdMovimientoFac' id='IdMovimientoFac'/><input type='text' name='pv' id='pv' data-plus-as-tab='true'  placeholder='PV' class='input-sm form-control' disabled='disabled'/></td>
                    <td><input type='text' name='ticket' id='ticket' data-plus-as-tab='true'  placeholder='Nº Ticket' class='input-sm form-control'/></td>
                    <td><input type='hidden' name='FechaTicket' id='FechaTicket'/><input type='text' name='fcanje' id='fcanje' data-plus-as-tab='true'  placeholder='Canje' class='input-sm form-control' <?php if(isset($_SESSION['fCanje']))echo "value='$_SESSION[fCanje]'";?>/></td>
                    <td><span id='datosTicket'></span><span id="grabaLoading2" style='display:none'><img src='img/ajax-loader-chico.gif'/></span></td>
                    <td><select name='mesAsignado' id='mesAsignado' data-plus-as-tab='true' >
                      <?php 
                      for ($abc = 9; $abc >= 0; $abc--) {
                          $mes = date("m/y", mktime(0, 0, 0, date("m")-$abc, date("d")-1, date("Y")));
                          $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$abc, date("d")-1, date("Y")));
                          echo "<option value='$valorMes'".(((date('d')>10&&$abc==0)||(date('d')<10&&$abc==1))?' selected="selected"':'').">$mes</option>";
                      }?>
                    </select></td>
                    <td ><span id='graba'><button class='button btn btn-default btn-xs graba' >Graba</button></span><span id="grabaLoading" style='display:none'><img src='img/ajax-loader-chico.gif'/></span></td>
                    </tr>
                  </thead>
                </table>
                <table id='ultimosTickets' class='table table-condensed'><thead><tr><th>Empleado</th><th>Mes</th><th>Ticket</th><th>Canje</th><th>Combustible</th><th>Despacho</th></tr></thead><tbody></tbody></table>
              </div>
              <div class='col-md-3'>
                <table  id='resultados' class='table table-condensed'>
                <thead><tr><th>Empleado</th><th>q</th><th>Nafta</th><th>Manual</th></thead>
                <tbody></tbody>
                </table>
              </div>
            </div>
            </form>                     
        </div>
        <div class='row'>
          
        </div>
      <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>

  </div> <!-- /container -->
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
  <script>
    function zeroFill( number, width ) {
      width -= number.toString().length;
      if ( width > 0 )  {
        return new Array( width + (/\./.test( number ) ? 2 : 1) ).join( '0' ) + number;
      }
      return number + ""; // always return a string
    }
    $(document).ready(function() {
      $("#nuevaOP").submit(function(e){
          return false;
      });
      $.post('func/ajaxBuscaDatosTicketGraba.php', {soloResultados: 1}, function(data3) {
        $('#resultados tbody').html(data3.resultados);
      },"json");
      $('#empleado').focus();
      $("#ticket").focusout(function() {
        $('#datosTicket').html('');
        $('#grabaLoading2').show();
        $.post('func/ajaxBuscaDatosTicket.php', { ticket: $(this).val(), idEmpleado: $('#empleado').val(), viejos: $('#viejos:checked').val()}, function(data) {
          if(data.status === 'single'){
            $('#grabaLoading2').hide();
            $('#datosTicket').html(data.message).removeClass('alert alert-danger');
            $('#pv').val(data.pv);
            $('#IdMovimientoFac').val(data.IdMovimientoFac);
            $('#FechaTicket').val(data.FechaTicket);
            if(data.fCanje){
              $('#fcanje').val(data.fCanje);
            } else {
              $('#fcanje').val(data.fecha);
            }
            //var debug=0;
            $('#fcanje').focusout(function(){
              
              var d = new Date();
              if($('#fcanje').val().length<3){
                // solo el día, el mes es el actual
                var dia = $('#fcanje').val()+'/'+zeroFill((d.getMonth()+1),2)+'/'+d.getFullYear();
                $('#fcanje').val(dia);
              } else if($('#fcanje').val().length<6){
                // solo el día, el mes es el actual
                var dia = $('#fcanje').val()+'/'+d.getFullYear();
                $('#fcanje').val(dia);
              }
              //mesAsignado( data.FechaTicket, $('#fcanje').val());
              //debug = debug +1 ;
              //alert (debug);
              $('#mesAsignado').val(mesAsignado( data.FechaTicket, $('#fcanje').val())).change();
            });
            $('#fcanje').dblclick(function(){
              $('#fcanje').val('');
            });
          } else if(data.status ==='multiple'){
          
            $('#grabaLoading2').hide();
            $('#datosTicket').html(data.message).removeClass('alert alert-danger');
            $('.multi').click(function(){
              $.post('func/ajaxBuscaDatosTicket.php', { IdMovimientoFac: $(this).val(), viejos: $('#viejos:checked').val() }, function(data) {
                $('#datosTicket').html(data.message).removeClass('alert alert-danger');
                if(data.fCanje){
                  $('#fcanje').val(data.fCanje);
                } else {
                  $('#fcanje').val(data.fecha);
                }
                $('#pv').val(data.pv);
                $('#IdMovimientoFac').val(data.IdMovimientoFac);
                $('#FechaTicket').val(data.FechaTicket);
                $('#fcanje').focusout(function(){
                  var d = new Date();
                  if($('#fcanje').val().length<3){
                    // solo el día, el mes es el actual
                    var dia = $('#fcanje').val()+'/'+(d.getMonth()+1)+'/'+d.getFullYear();
                    $('#fcanje').val(dia);
                  } else if($('#fcanje').val().length<6){
                    // solo el día, el mes es el actual
                    var dia = $('#fcanje').val()+'/'+d.getFullYear();
                    $('#fcanje').val(dia);
                  }
                  $('#mesAsignado').val(mesAsignado( data.FechaTicket, $('#fcanje').val())).change();
                  //$.post('func/ajaxBuscaDatosTicketMesAsignado.php', { fechaTicket: data.FechaTicket, fechaCanje: $(this).val() }, function(data2) {
                  //  $('#mesAsignado').val(data2.mesAsignado).change();
                 // }, "json");
                });
                $('#fcanje').dblclick(function(){
                  $('#fcanje').val('');
                });
              },"json");
            });
          } else {
          
            $('#grabaLoading2').hide();
            $('#datosTicket').html(data.message).addClass('alert alert-danger');
          }
        }, "json");
      });
      $('.graba').click(function(){
        $('#graba').hide();
        $('#grabaLoading').show();
        var idEmpleado = $('#empleado').val();
        var ticket = $('#ticket').val();var pv = $('#pv').val();
        var fcanje = $('#fcanje').val();var mesAsignado = $('#mesAsignado').val();
        var IdMovimientoFac = $('#IdMovimientoFac').val();
        $.post('func/ajaxBuscaDatosTicketGraba.php', {IdEmpleado: idEmpleado, pv: pv, ticket: ticket, fcanje: fcanje, mesAsignado: mesAsignado, IdMovimientoFac: IdMovimientoFac}, function(data3) {
          if(data3.status==="yes"){
            $('#ticket').val("");
            $('#fcanje').val("");
            $('#pv').val("");
            $('#IdMovimientoFac').val("");
            $('#datosTicket').html("");
            $('#ultimosTickets tbody').prepend(data3.ultimoTicket);
            $('#resultados tbody').html(data3.resultados);
            $('#empleado').focus();
            $('#grabaLoading').hide();
            $('#graba').show();
          }
        },"json");
      });
    });
    function mesAsignado(emision, canje){
      var d1 = emision.split('/');
      var d2 = canje.split('/');
      var datetime1 = new Date(d1[2], d1[1], d1[0]);
      var datetime2 = new Date(d2[2], d2[1], d2[0]);
      var interval = new Date(datetime2.getTime() - datetime1.getTime());
      //ChromePhp::log($interval->days);
      if((interval.getUTCDate()-1)<=15){
        // mes de ticket
        var mesAsignado= d1[2]+d1[1];
      } else {
        var mesAsignado= d2[2]+d2[1];
      }
      return(mesAsignado);
    }
  </script>
  </body>
</html>
