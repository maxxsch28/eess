<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

//include('func/acomodaProMovistar.php');

//select Fecha, Anio, IdTipoMovimientoProveedor, puntoventa, numero, razonsocial, netoNoGravado, NetoMercaderias, NetoCombustibles, NetoLubricantes, NetoGastos, NetoFletes, Total, idasiento, dbo.cuentasgastos.Descripcion as CuentaGasto, dbo.MovimientosDetallePro.Descripcion  from dbo.MovimientosPro, dbo.CuentasGastos, dbo.MovimientosDetallePro where Fecha>='2015-04-01' and Fecha<'2015-05-01' and dbo.MovimientosDetallePro.IdCuentaGastos=dbo.CuentasGastos.IdCuentaGastos and dbo.MovimientosPro.IdMovimientoPro=dbo.MovimientosDetallePro.IdMovimientoPro and (IdTipoMovimientoProveedor<>'RV' AND IdTipoMovimientoProveedor<>'VP') and dbo.movimientosdetallepro.IdCuentaGastos<>43 order by CuentaGasto asc, RazonSocial asc

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Tracking ventas CEM/CaldenOil</title>
  <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
  <link rel="stylesheet" href="css/jquery.modal.css" type="text/css" media="screen" />
  <style type="text/css">
    body {
      padding-top: 60px;
      padding-bottom: 40px;
    }
    .neg {
        color: red;
    }
    .pos {
        color: green;
    }
    @media print {
          #fila1 {page-break-after: always;}
      }
  </style>
</head>
<body>
      <?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
      <div class="container">
          <div class='row' id='fila1'>
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
              <li>Tracking ventas por CEM y CaldenOil</li>
            </ul>
            <div  id="tracking"></div>
          </div>

      </div>
      <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
  </div> <!-- /container -->
<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
<script src="js/jquery.modal.min.js" type="text/javascript" charset="utf-8"></script>
<script>
$(document).ready(function() {
  $('#tracking').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
  $.post('func/listaTrackVentas.php', { mes: $('#periodo').val() }, function(data) {
    $('#tracking').html(data);
//     $.post('func/listaTrackVentas.php', { resumen: $('#periodo').val() }, function(data) {
//       $('#resumen').html(data);
//     });
  });
  $('#periodo').change(function(){
    $('#tracking').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
    $.post('func/listaTrackVentas.php', { mes: $('#periodo').val() }, function(data) {
      $('#tracking').html(data);
//       $.post('func/listaTrackVentas.php', { resumen: $('#periodo').val() }, function(data) {
//         $('#resumen').html(data);
//       });
    });
  });
});
</script>
</body>
</html>
