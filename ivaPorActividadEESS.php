<?php
$nivelRequerido = 3;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_ALL, 'es_ES.utf-8');
$titulo="Detalle IVA por Actividad";
setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish');
if(!isset($_SESSION['ultimoDiaMes'])||1){
  $_SESSION['ultimoDiaMes']='';
  $currentMonth = date('m');
  for($x = $currentMonth; $x > $currentMonth-12; $x--) {
    $dia = new DateTime(date("Y-m-01", strtotime("-$x months")));
    $ultimoDia = new DateTime(date("Y-m-01", strtotime("-$x months")));
    $ultimoDia->modify("last day of month");
    $_SESSION['ultimoDiaMes'] .= "<option value='".$ultimoDia->format('d-m-Y')."'>".$dia->format('F Y')."</option>";
  }
}
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
          <input type="hidden" name='fechaCierre' value='<?php echo date('d/m/Y')?>' id='fechaCierre'/>
          <div class="form-group">
            <label for='mes' class="control-label">Detalle de: <select name='mes' id='mes' class=''>
            <?php echo $_SESSION['ultimoDiaMes'];?>
            </select></label>
            <div style='float:right'><div id='comprimir' class='btn btn-success no2'>Ver comprimido</div>
            </div>
          </div>
        </form>
        <table class='table table-striped table-condensed' id='ivaEESS'>
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
    muestraCierre();
      
    $('#mes').change(function(){
      $('#fechaCierre').val($(this).val());
      muestraCierre($(this).val());
    });
    
    function muestraCierre(fecha){
      mostrarLoader();
      if(fecha != null){
        $('#fechaCierre').val(fecha);
      } else if($('#mes').is(':disabled')){
        $('#fechaCierre').val($('#dia').val());
      } else {
        $('#fechaCierre').val($('#mes').val());
      }
      $.post('func/ajaxIVAporCategoria.php', { fechaCierre: $('#fechaCierre').val() }, function(data) {
        // comienza magia json
        $('#ivaEESS tbody tr:has(td)').remove();
        $('#ivaTransporte tbody tr:has(td)').remove();
        $.each(data, function (i, item) {
          var trHTML = '';
          if(item.clase == 'h2'){
            trHTML = '<tr class="h2"><th>' + item.txt + '</th><th>' + item.alicuota + '</th><th>'+ item.neto + '</th><th>' + item.iva + '</th></tr>';
          } else {
            trHTML = '<tr><td>' + item.txt + '</td><td>' + item.alicuota + '%</td><td>$ ' + item.neto + '</td><td>$ ' + item.iva + '</td></tr>';
          // cambiar para que de acuerdo a si la variable T es Efectivo o Cheque agregue la fila donde corresponda.
          }
          $('#iva'+item.t+' tbody').append(trHTML); 
        });
      }, "json");
    
    }
    $('#refresh').click(function(){
      muestraCierre();
    })

    function mostrarLoader(){
      $('#ivaEESS tbody').html("<tr><td colspan=2><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
      $('#ivaTransporte tbody').html("<tr><td colspan=2><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
    };
    
    
    
  });
  </script>
</body>
</html>
