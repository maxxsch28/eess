<?php
$nivelRequerido = 3;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_ALL, 'es_ES.utf-8');
$titulo="Órdenes descontadas a fleteros por mes";
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
      <h2>Listado de órdenes de servicio descontadas a fleteros por mes</h2>
      <form class='form-horizontal'>
        <div class="col-md-6">
          <input type="hidden" name='muestraSoloExternos' value='0' id='muestraSoloExternos'/>
          <input type="hidden" name='muestraComprimido' value='0' id='muestraComprimido'/>
          <div class="form-group">
            <div class="controls">
                        
                    <input type="hidden" name='muestraSoloExternos' value='0' id='muestraSoloExternos'/>
                    <input type="hidden" name='muestraComprimido' value='0' id='muestraComprimido'/>
                        <label for='periodo' class="control-label">Mostrar  <select name='periodo' id='periodo' class='input-sm '>
                             <?php 
                            for ($abc = 11; $abc >= 0; $abc--) {
                                $mes = date("F Y", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                                $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                                echo "<option value='$valorMes' ".(($abc==1)?' selected="selected"':'').">$mes</option>";
                            }?>
                            </select></label><span class='glyphicon glyphicon-refresh' id='refresh'></span>
                            
            </div>
          </div>
        </div>
        <div class='col-md-5 col-md-offset-1'>
          <div style='float:right'><div id='comprimir' class='btn btn-success no2'>Ver comprimido</div>
          </div>
        </div>
      </form>
    </div>
    <div class='row'>
      <div id='viajes'>
        <table class='table table-striped table-condensed' id='productosFleteros'>
          <thead><tr><th width='5%'>Cargada</th>
          <th width='5%'>Descontada</th>
          <th width='8%'>Nº orden</th>
          <th width='30%'>Detalle</th>
          <th width='25%'>Importe</th>
          <th width='5%'>Ret IIBB</th>
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
    $.post('func/setupAjaxOrdenesDescontadasMensuales.php', { periodo: $('#periodo').val()}, function(data) {
      $('#productosFleteros tbody').html(data);
    });

    /* contextMenu */
    $.contextMenu({
      selector: '.x', 
      callback: function(key, options) {
        var n = (this).html();
        if(key == 'buscar'){
          $('#importe').val(coma(n));
          $('#enviar').click();
        } else {
          $('#importe').val(coma(n));
          $('#fuzzy').prop('checked', false);
          $('#buscaLeyenda').prop('checked', false);
          $('#cuentaEESS').prop('checked', false);
          $('#cuentaTransporte').prop('checked', false);
          $('#enviar').click();
        }
      },
      items: {
        "buscar": {name: "Buscar este importe", icon: "paste"},
        "viaje": {name: "Buscar datos de este viaje", icon: "copy"},
      }
    });
    $('.x').on('click', function(e){
      alert('hola');
      console.log('clicked', this);
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
    });
    $('#periodo').change(function(){
      $('#productosFleteros tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
      $.post('func/setupAjaxOrdenesDescontadasMensuales.php', { periodo: $('#periodo').val() }, function(data) {
        $('#productosFleteros tbody').html(data);
        if($('#muestraComprimido').val() == 1){
          $('#comprimir').click();
        }
      });
    });
  });
  </script>
</body>
</html>
