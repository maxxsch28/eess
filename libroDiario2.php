<?php
$nivelRequerido = 5;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo="Libro diario comprimido";
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$imprimeTotalAsiento=true;
$imprimeDetalleAsiento=true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
  <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
  <style type="text/css">
  .containerIntegral{
    width:1400px;
  }
  </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
<div class="container" id='container'>
  <!-- Example row of columns -->
  <div class="row">
    <div class="col-md-12">
      <h2></h2>
    </div>
  </div>
  <div class='row'>
    <form name='diario' id='diario'>
    <input type='hidden' name='fechaCierre' id='fechaCierre' value='<?php echo date('Y')-1;?>'/>
    <h2 class='hidden-print'>Libro diario de <select name='mes' id='mes' class=''>
      <?php
      for ($i = 12; $i >= 0; $i--) {
          $mes = date("Y", mktime(0, 0, 0, 1, 1,   date("Y")-$i));
          $valorMes = date("Y", mktime(0, 0, 0, 1, 1,   date("Y")-$i));
          echo "<option value='$valorMes' ".((($i==1&&!isset($_GET['m']))||(isset($_GET['m'])&&$_GET['m']==$valorMes))?' selected="selected"':'').">$mes</option>";
      }?>
      </select> <button type="button" class="btn btn-default" aria-label="Left Align" id='actualizar'><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button>
      <div style='float:right'>
        <div class="checkbox">
          <label>
            <input id='periodicidad' data-toggle="toggle" type="checkbox" data-on="Mensual" data-off="Diario" checked value='mensual'>
          </label>
        </div>
      </h2>
    <div class='col-md-12'>
      <table id='libroDiario' class='table'>
        <thead><tr><th>Concepto</th><th>Debe</th><th>Haber</th></tr></thead>
        <tbody>
      </tbody></table>
    </div>
    </form>
  </div>
  <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
</div> <!-- /container -->
<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<script>
  $(document).ready(function() {
    
    $('#actualizar').click(function(){
      muestraCierre();
    });
    //muestraCierre();
    
    $('#mes').change(function(){
      $('#fechaCierre').val($(this).val());
      muestraCierre($(this).val());
    });
    
    function muestraCierre(fecha){
      mostrarLoader();
      $.post('func/es_LibroDiario.php', { anio: $('#mes').val(), periodicidad: $('#periodicidad').is(':checked') }, function(data) {
        // comienza magia json
        $('#libroDiario tbody tr:has(td)').remove();
        $.each(data, function (i, item) {
          var trHTML = '';
          switch(item.clase){
            case 'abreAsiento':
              trHTML = '<tr class="encabezaAsiento"><td><b>' + item.fecha + ' - ' + item.txt + '</b></td><td></td><td></td></tr>';
            break;
            case 'asiento':
              if(item.debe>item.haber){
                trHTML = '<tr class="asiento fila"><td class="cuentaD"> ' + item.txt + '</td><td class="x debe"> $' + item.debe + '</td><td class="x haber"> $' + item.haber + '</td></tr>';
              } else {
                trHTML = '<tr class="asiento fila"><td class="cuentaH"> ' + item.txt + '</td><td class="x debe"> $' + item.debe + '</td><td class="x haber"> $' + item.haber + '</td></tr>';
              }
            break;
            case 'cierreAsiento':
              if(item.error == 'error'){
                trHTML = '<tr class="cierreAsiento alert alert-warning"><td class="x cierre"> ' + item.txt + '</td><td class="x debe cierre""> $' + item.debe + '</td><td class="x haber cierre"> $' + item.haber + '</td></tr>';
              } else {
                trHTML = '<tr class="cierreAsiento"><td> ' + item.txt + '</td class="x cierre"><td class="x debe cierre""> $' + item.debe + '</td><td class="x haber cierre"> $' + item.haber + '</td></tr>';
              }
            break;
            case 'cierreAsientoTotal':
              trHTML = '<tr class="cierreAsientoTotal"><td class="x cierre"> ' + item.txt + '</td><td class="x debe cierre""> $' + item.debe + '</td><td class="x haber cierre""> $' + item.haber + '</td></tr>';
            break;
          }
          $('#libroDiario tbody').append(trHTML); 
        });
        //$('#listaEfectivo tbody').html(data);
      }, "json");
    
    }
    $('#refresh').click(function(){
      muestraCierre();
    })

    function mostrarLoader(){
      $('#libroDiario tbody').html("<tr><td colspan=4><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
    };
  });
</script>
</body>
</html>
