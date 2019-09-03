<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo = "Control devoluciones YER";

$sqlVentas = "select a.fecha, IdTipoMovimiento, IdArticulo, Cantidad from dbo.movimientosfac a, dbo.MovimientosDetalleFac b where a.IdMovimientoFac=b.IdMovimientoFac and a.IdCliente=1283 order by a.fecha desc";


?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
</head>
<body>
      <?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
      <div class="container">
          <div class='row'>&nbsp;<br/>&nbsp;</div>
          <div class='row' id='fila1'>
          <h2>Control devoluciones YPF En Ruta</h2>
          <div class="col-md-10">
            <ul class="nav nav-tabs" role="tablist" id="myTab" style='height:3.1em;'>
              <li>
                <form class='form-horizontal'><select name='periodo' id='periodo'>
                <option value='2016'>2016</option>
                <?php 
                for ($abc = 11; $abc >= 0; $abc--) {
                    $mes = date("F y", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                    $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                    echo "<option value='$valorMes' ".(($abc==0)?' selected="selected"':'').">$mes</option>";
                }?>
                </select></form>
              </li><li>&nbsp;  &nbsp;</li>
              <li>Despachos YER y devoluciones</li>
            </ul>
            <div  id="tracking">
              <table id='yer' class='table'>
                <thead><tr><th>Fecha</th><th>RV</th><th>NS</th><th>NI</th><th>UD</th><th>ID</th><th>Conciliado</th></tr>
                <tbody></tbody>
              </table>
            </div>
          </div>

      </div>
      <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
  </div> <!-- /container -->
<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
<script>
$(document).ready(function() {
  $('#tracking tbody').html("<tr><td colspan=7><img src='img/ajax-loader.gif'/></td></tr>").fadeIn();
  $.post('func/listaYER.php', { mes: $('#periodo').val() }, function(data) {
    $('#tracking tbody').html(data);
//     $.post('func/listaTrackVentas.php', { resumen: $('#periodo').val() }, function(data) {
//       $('#resumen').html(data);
//     });
  });
  $('#periodo').change(function(){
    $('#tracking').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
    $.post('func/listaYER.php', { mes: $('#periodo').val() }, function(data) {
      $('#tracking tbody').html(data);
//       $.post('func/listaTrackVentas.php', { resumen: $('#periodo').val() }, function(data) {
//         $('#resumen').html(data);
//       });
    });
  });
});
</script>
</body>
</html>
