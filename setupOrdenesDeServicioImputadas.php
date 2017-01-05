<?php
$nivelRequerido = 6;

include('include/inicia.php');
/*
  - Primero debe ingresarse el proveedor, buscador como el de movistar para elegir por codigo, cuit o por nombre
  - si es Coopetrans luego selecciona el punto de venta, si es 7 levanta los datos desde Setup, si es 8 o 9 desde Calden. No deja modificar los importes.
  
*/

$titulo = "Órdenes de servicio imputadas por Socio";
$modifica = false;
$fechaFactura = false;
setlocale(LC_ALL, 'es_ES');
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include ('/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
  
  </head>

  <body>
    <?php include('include/menuSuperior.php') ?>
    <div class="container">
    <!-- Main hero unit for a primary marketing message or call to action -->
      <div class='row'>
        <h2>Órdenes de servicio imputadas en pago a Socios</h2>
        <form name='nuevaOP' id='nuevaOP' class='form-horizontal '>
        <div class='row'>
          <div class='col-md-5 well'>
            <div class="form-group">  
              <label class="control-label" for="busca">Socio</label>
              <div class="controls">
                <div class="input-group"> 
                  <input type='text' name='busca' id='busca' maxlength="11" class='input-sm form-control'/>
                  <span class="input-group-addon btn" id='addCliente'>-></span>
                  <input type='text' name='socio' id='socio' maxlength="255" class='input-sm form-control ui-widget' disabled='disabled'/>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="controls">
                <div class="input-group"> 
                    <span class="input-group-addon" >Mes</span>
                    <select name='periodo' id='periodo' data-plus-as-tab='true' class='input-lg form-control'>
                      <?php
                      for ($i = 2; $i >= 0; $i--) {
                        $mes = date("F Y", mktime(0, 0, 0, date("m")-$i, date("d"),   date("Y")));
                        $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$i, date("d"),   date("Y")));
                        echo "<option value='$valorMes' ".(((!$modifica&&$i==1)||$modifica&&$valorMes==$factura['periodo'])?' selected="selected"':'').">$mes</option>";
                      }?>
                    </select>
                  <span class="input-group-addon"><button class="btn btn-primary btn-sm" id='enviar'>Cargar socio &raquo;</button></span>
                </div>
              </div>
            </div>
          </div>
          <div class='col-md-6' style="height: 500px; overflow-y: scroll">
            <table id='ultimasFacturasSocio' class='table table-striped table-condensed'>
            <thead><th>Fecha</th><th>Orden de servicio</th><th>Factura</th><th>Importe</th><th>Retenciones</th></tr></thead><tbody></tbody></table>
          </div>  
        </div>
        </form>   
                        
      </div>
    <?php include ('include/footer.php')?>
    </div> <!-- /container -->
  <?php include('include/termina.php');?>
  <script>
    $(document).ready(function() {
      $("#busca").autocomplete({
        source: "func/ajaxBuscaSocio.php",
        minLength: 2,
        select: function( event, ui ) {
          $(this).value=ui.item.label;
          $(this).val(ui.item.label);
          //$("#socio").value=ui.item.value;
          $("#socio").val(ui.item.value+' - '+ui.item.label);
          $('#ultimasFacturasSocio tbody').html("<tr><td colspan=12><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
          $.post('func/setupOrdenesServicioImputadas.php', { idSocio: ui.item.value, periodo: $('#periodo').val() }, function(data) {
            $('#ultimasFacturasSocio tbody').html(data);
            $('#botonesBusqueda').show();
          });
        }
      });
      $("#cuit").autocomplete({
        source: "func/ajaxBuscaTercero.php",
        minLength: 2,
        select: function( event, ui ) {
          //$(this).value=ui.item.cuit;
          //$(this).val(ui.item.cuit);
          $("#cuit").value=ui.item.cuit;
          $("#razonsocial").val(ui.item.cuit+' - '+ui.item.label);
        }
      });
      $("#periodo").change(function(){
        $('#ultimasFacturasSocio tbody').html("<tr><td colspan=12><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
        $.post('func/setupOrdenesServicioImputadas.php', { idSocio: $('#busca').val(), periodo: $(this).val() }, function(data) {
          $('#ultimasFacturasSocio tbody').html(data);
        });
      });
      $("#buscaComprobantes").click(function(){
        $('#ultimasFacturasSocio tbody').html("<tr><td colspan=12><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
        $.post('func/ajaxCargaFacturasSocios.php', { idSocio: $('#busca').val(), periodo: $('#periodo').val(), compras:1 }, function(data) {
          $.post('func/setupOrdenesServicioImputadas.php', { idSocio: $('#busca').val(), periodo: $('#periodo').val() }, function(data) {
            $('#ultimasFacturasSocio tbody').html(data);
          });
        });
      });
      $("#buscaVentas").click(function(){
        $('#ultimasFacturasSocio tbody').html("<tr><td colspan=12><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
        $.post('func/ajaxCargaFacturasSocios.php', { idSocio: $('#busca').val(), periodo: $('#periodo').val(), ventas:1 }, function(data) {
          $.post('func/setupOrdenesServicioImputadas.php', { idSocio: $('#busca').val(), periodo: $('#periodo').val() }, function(data) {
            $('#ultimasFacturasSocio tbody').html(data);
          });
        });
      });
      
      
      $('#busca').focus();
      $("#facturas input").keypress(function(event) {
        if ( event.which == 13 ) {
                event.preventDefault();
        }
        alert(event.which);
      });
    });
    function calculaIVA(){
      var myArray = $(this).attr('id').split('_');
      var thisIva = '#IVA'+myArray[1]+'_'+myArray[2];
      if(myArray[1]==0||myArray[1]==4)
              var alicuota= .21;
      else
              var alicuota= .27;
      var IVA = $(this).val() * alicuota;
      $(thisIva).html(IVA.toFixed(2));
    }
  </script>
</body>
</html>
