<?php
$nivelRequerido = 3;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_ALL, 'es_ES.utf-8');
$titulo="Detalle Cargadora";
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
      #detalleCargadora td, #detalleCargadora th{
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
    #detalleCargadora .text-right {text-align: right} /*For right align*/
    #detalleCargadora .text-left {text-align: left} /*For left align*/
    #detalleCargadora .text-center {text-align: center} /*For center align*/
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
              echo "<option value='".date('Y')."'  selected=selected>".date('Y')."</option>";
              echo "<option value='".(date('Y')-1)."' >".(date('Y')-1)."</option>";
              ?>
              <?php 
              for ($abc = 11; $abc >= 0; $abc--) {
                  $mes = date("F y", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                  $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                  //echo "<option value='$valorMes' ".(($abc==0)?' selected="selected"':'').">$mes</option>";
                  echo "<option value='$valorMes' >$mes</option>";
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
        <div class='col-md-6'>
        <h2>Gastos</h2>
        <table class='table table-striped table-condensed' id='cargadoraGastos'>
          <thead><tr><th width='2%'></th>
          <th class='nombre no2' width='50%'>Concepto</th>
          <th width='8%'>Importe</th>
          </tr></thead>
          <tbody></tbody>
        </table>
        <h2>Sueldos</h2>
        <table class='table table-striped table-condensed' id='cargadoraSueldos'>
          <thead><tr><th width='2%'></th>
          <th class='nombre no2' width='50%'>Concepto</th>
          <th width='8%'>Importe</th>
          </tr></thead>
          <tbody></tbody>
        </table>
        </div>
        <div class='col-md-6'>
        <h2>Ingresos</h2>
        <table class='table table-striped table-condensed' id='cargadoraIngresos'>
          <thead><tr><th width='2%'></th>
          <th class='nombre no2' width='50%'>Concepto</th>
          <th width='8%'>Importe</th>
          </tr></thead>
          <tbody></tbody>
        </table>
        <h2>Resultado<span class='pull-right glyphicon glyphicon-refresh' id='refreshResultado'></span></h2>
        <table class='table table-striped table-condensed' id='cargadoraResultado'>
          <thead><tr><th width='2%'></th>
          <th class='nombre no2' width='50%'>Concepto</th>
          <th width='8%'>Importe</th>
          </tr></thead>
          <tbody></tbody>
        </table>
        </div>
      </div>
    </div>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
  </div> <!-- /container -->
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
  <script>
  $(document).ready(function() {
    $('#cargadoraGastos tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
    $.post('func/setupResultadoCargadora.php', { mes: $('#periodo').val(), que:'gastos'}, function(data) {
      $('#cargadoraGastos tbody').html(data);
    });
    $('#cargadoraIngresos tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
    $.post('func/setupResultadoCargadora.php', { mes: $('#periodo').val(), que:'ingresos'}, function(data) {
      $('#cargadoraIngresos tbody').html(data);
    });
    $('#cargadoraSueldos tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
    $.post('func/setupResultadoCargadora.php', { mes: $('#periodo').val(), que:'sueldos'}, function(data) {
      $('#cargadoraSueldos tbody').html(data);
    });
    $('#cargadoraResultado tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
    $.post('func/setupResultadoCargadora.php', { mes: $('#periodo').val(), que:'resultado'}, function(data) {
      $('#cargadoraResultado tbody').html(data);
    });
    $('#periodo').change(function(){
      $('#cargadoraSueldos tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
      $.post('func/setupResultadoCargadora.php', { mes: $(this).val(), soloExternos: $('#filtroTipoViaje').val(), que:'sueldos' }, function(data) {
        $('#cargadoraSueldos tbody').html(data);
      });
      $('#cargadoraIngresos tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
      $.post('func/setupResultadoCargadora.php', { mes: $(this).val(), soloExternos: $('#filtroTipoViaje').val(), que:'ingresos' }, function(data) {
        $('#cargadoraIngresos tbody').html(data);
      });
      $('#cargadoraGastos tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
      $.post('func/setupResultadoCargadora.php', { mes: $(this).val(), soloExternos: $('#filtroTipoViaje').val(), que:'gastos' }, function(data) {
        $('#cargadoraGastos tbody').html(data);
      });
      $('#cargadoraResultado tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
      $.post('func/setupResultadoCargadora.php', { mes: $(this).val(), soloExternos: $('#filtroTipoViaje').val(), que:'resultado' }, function(data) {
        $('#cargadoraResultado tbody').html(data);
      });
      if($('#muestraComprimido').val() == 1){
        $('#comprimir').click();
      }
    });
    $('#refreshResultado').click(function(){
      $('#cargadoraResultado tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
      $.post('func/setupResultadoCargadora.php', { mes: $(this).val(), que:'resultado' }, function(data) {
        $('#cargadoraResultado tbody').html(data);
      });
    })
    $('#comprimir').click(function(){
      if($('.viaje').is(":visible") === true ) {
        $('#cargadoraGastos').removeClass('table-striped');
        $('#cargadoraIngresos').removeClass('table-striped');
        $('#cargadoraSueldos').removeClass('table-striped');
        $(".viaje" ).hide();
        $('.comisionEncabezado').removeClass('info');
        $('#muestraComprimido').val(1);
      } else {
        $(".viaje").show();
        $('.comisionEncabezado').addClass('info');
        $('#cargadoraGastos').addClass('table-striped');
        $('#cargadoraIngresos').addClass('table-striped');
        $('#cargadoraSueldos').addClass('table-striped');
        $(".viaje" ).hide();
        $('#muestraComprimido').val(0);
      }
      //$('.viaje').toggle();
    });
    $('#filtroTipoViaje').change(function(){
      $('#detalleCargadora tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
      $.post('func/setupResultadoCargadora.php', { mes: $('#periodo').val(), soloExternos: $(this).val() }, function(data) {
        $('#detalleCargadora tbody').html(data);
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
