<?php
$nivelRequerido = 3;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_ALL, 'es_ES.utf-8');
$titulo="Detalle productos por Fleteros";
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
      #productosFleteros td, #productosFleteros th{
          text-align: left;
      }
    @page {
        size: A4;
        margin: 0;
    }
    @media print {
      width: 21cm;
      min-height: 29.7cm;
      .page {
        margin: 0;
        border: initial;
        border-radius: initial;
        width: initial;
        min-height: initial;
        box-shadow: initial;
        background: initial;
        page-break-after: always;
      }
    }
    #productosFleteros .text-right {text-align: right} /*For right align*/
    #productosFleteros .text-left {text-align: left} /*For left align*/
    #productosFleteros .text-center {text-align: center} /*For center align*/
    </style>
  </head>
  <body>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
    <div class="container">
    <div class='row'>
      <div class="col-md-12">
        <form class='form-horizontal'>
          <input type="hidden" name='muestraSoloExternos' value='0' id='muestraSoloExternos'/>
          <input type="hidden" name='muestraComprimido' value='0' id='muestraComprimido'/>
          <div class="form-group">
            <label for='periodo' class="control-label">Detalle de: <select name='periodo' id='periodo' class='input-sm '>
            <?php 
            for ($abc = 12; $abc >= 0; $abc--) {
              $mes = date("F Y", mktime(0, 0, 0, date("m")-$abc, 1, date("Y")));
              $valorMes = date("Y-m", mktime(0, 0, 0, date("m")-$abc, 1, date("Y")));
              if(mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y"))>=mktime(0,0,0,7,1,2015))
                echo "<option value='$valorMes' ".(($abc==1)?' selected="selected"':'').">$mes</option>";
            }?>
            </select></label>
            <div style='float:right'><div id='comprimir' class='btn btn-success no2'>Ver comprimido</div>
            <select name='filtroTipoViaje' id='filtroTipoViaje' class='btn btn-danger'>
              <option value='0' selected="selected">Todos los clientes</option>
              <option value='1' >Solo Fleteros</option>
              <option value='2' >Solo Clientes</option>
            </select>
            </div>
          </div>
        </form>
        <table class='table table-striped table-condensed' id='productosFleteros'>
          <thead><tr><th class='nombre no2' width='30%'>Producto / Socio</th>
          <th width='5%'>Fecha</th>
          <th width='15%'>Comprobante</th>
          <th width='10%'>Cantidad</th>
          <th width='10%'>Precio</th>
          <th width='10%'>Neto Gravado</th>
          <th width='10%'>IVA</th>
          <th >Perc IIBB</th>
          </tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
  </div> <!-- /container -->
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
  <script>
  $(document).ready(function() {
    $('#productosFleteros tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
    $.post('func/setupProductosPorClientes.php', { mes: $('#periodo').val()}, function(data) {
      $('#productosFleteros tbody').html(data);
    });
    $('#periodo').change(function(){
      $('#productosFleteros tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
        $.post('func/setupProductosPorClientes.php', { mes: $(this).val(), soloExternos: $('#filtroTipoViaje').val() }, function(data) {
          $('#productosFleteros tbody').html(data);
          if($('#muestraComprimido').val() == 1){
            $('#comprimir').click();
          }
        });
      });
    $('#comprimir').click(function(){
      if($('.viaje').is(":visible") === true ) {
        $('#productosFleteros').removeClass('table-striped');
        $(".viaje" ).hide();
        $('.comisionEncabezado').removeClass('info');
        $('#muestraComprimido').val(1);
      } else {
        $(".viaje").show();
        $('.comisionEncabezado').addClass('info');
        $('#productosFleteros').addClass('table-striped');
        $('#muestraComprimido').val(0);
      }
      //$('.viaje').toggle();
    });
    $('#filtroTipoViaje').change(function(){
      $('#productosFleteros tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
      $.post('func/setupProductosPorClientes.php', { mes: $('#periodo').val(), soloExternos: $(this).val() }, function(data) {
        $('#productosFleteros tbody').html(data);
        if($('#muestraComprimido').val() == 1){
          $('#comprimir').click();
        }
      });
      //$('.viaje').toggle();
    });
  });
  </script>
</body>
</html>
