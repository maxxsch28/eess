<?php
$nivelRequerido = 3;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_ALL, 'es_ES.utf-8');
$titulo="Detalle de cheques diferidos entre fechas";
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
      <h2>Listado de cheques diferidos emitidos entre fechas</h2>
      <form class='form-horizontal'>
        <div class="col-md-6">
          <input type="hidden" name='muestraSoloExternos' value='0' id='muestraSoloExternos'/>
          <input type="hidden" name='muestraComprimido' value='0' id='muestraComprimido'/>
          <div class="form-group">
            <div class="controls">
              <div class="input-group" id='rop'> <!--2015-12-31 => 12-31-2015-->
                <span class="input-group-addon">Emitidos desde </span>
                <input type='text' name='rangoInicio' id='rangoInicio' class="input-sm form-control"  value="<?php if(isset($rowHistorico)&&$rowHistorico['rangoInicio']<>0){echo substr($rowHistorico['rangoInicio'], 8,2).'/'.substr($rowHistorico['rangoInicio'], 5,2).'/'.substr($rowHistorico['rangoInicio'], 2,2);} else {echo "01/01/".date('Y', strtotime("-1 year"));}?>" data-date-format="dd/mm/yyyy" data-plus-as-tab='true'/>
                <span class="input-group-addon">Hasta el </span>
                <input type='text' name='rangoFin' id='rangoFin' class="input-sm form-control"  value="<?php if(isset($rowHistorico)&&$rowHistorico['rangofin']<>0){echo substr($rowHistorico['rangofin'], 8,2).'/'.substr($rowHistorico['rangofin'], 5,2).'/'.substr($rowHistorico['rangofin'], 2,2);} else {echo "31/12/".date("Y", strtotime("-1 year"));}?>" data-date-format="dd/mm/yyyy" data-plus-as-tab='true'/>
                <span class="input-group-addon presetAnio btn" id="1000">Hoy</span>
                <span class="input-group-addon presetAnio btn" id="<?php echo date('Y', strtotime("-1 year"))?>"><?php echo date('y', strtotime("-1 year"))?></span>
                <span class="input-group-addon presetAnio btn<?php if(!isset($_GET['id']))echo" label-success";?>" id="<?php echo date('Y')?>"><?php echo date('y')?></span>
              </div>
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
          <thead><tr><th width='5%'>Emisión</th>
          <th width='5%'>Fecha de pago</th>
          <th width='5%'>Nº Cheque</th>
          <th width='30%'>Detalle</th>
          <th width='25%'>Importe</th>
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
    $.post('func/setupAjaxChequesDiferidos.php', { rangoFin: $('#rangoFin').val(), rangoInicio: $('#rangoInicio').val()}, function(data) {
      $('#productosFleteros tbody').html(data);
    });
    $('#rangoInicio').datepicker({
      dateFormat: "dd/mm/yy"
    });
    $('#rangoFin').datepicker({
      dateFormat: "dd/mm/yy"
    });
    $('.presetAnio').click(function(){
      var year = $(this).attr('id');
      $('.presetAnio').removeClass('label-success');
      $(this).addClass('label-success');
      if(year==='1000'){
        $('#rangoFin').val('<?php echo date('d/m/Y')?>');
      } else {
        $('#rangoFin').val('31/12/'+year);
        $('#rangoInicio').val('01/01/'+year);
      }
      $('#productosFleteros tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
      $.post('func/setupAjaxChequesDiferidos.php', { rangoFin: $('#rangoFin').val(), rangoInicio: $('#rangoInicio').val()}, function(data) {
        $('#productosFleteros tbody').html(data);
      });
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
    $('#rangoFin').change(function(){
      $('#productosFleteros tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
      $.post('func/setupAjaxChequesDiferidos.php', { rangoFin: $(this).val(),  rangoInicio: $('#rangoInicio').val() }, function(data) {
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
