<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

// acomoda Movistar
//include('func/acomodaProMovistar.php');

//select Fecha, Anio, IdTipoMovimientoProveedor, puntoventa, numero, razonsocial, netoNoGravado, NetoMercaderias, NetoCombustibles, NetoLubricantes, NetoGastos, NetoFletes, Total, idasiento, dbo.cuentasgastos.Descripcion as CuentaGasto, dbo.MovimientosDetallePro.Descripcion  from dbo.MovimientosPro, dbo.CuentasGastos, dbo.MovimientosDetallePro where Fecha>='2015-04-01' and Fecha<'2015-05-01' and dbo.MovimientosDetallePro.IdCuentaGastos=dbo.CuentasGastos.IdCuentaGastos and dbo.MovimientosPro.IdMovimientoPro=dbo.MovimientosDetallePro.IdMovimientoPro and (IdTipoMovimientoProveedor<>'RV' AND IdTipoMovimientoProveedor<>'VP') and dbo.movimientosdetallepro.IdCuentaGastos<>43 order by CuentaGasto asc, RazonSocial asc

?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Prepara informe mensual</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .newspaper {
        -webkit-column-count: 3; /* Chrome, Safari, Opera */
        -moz-column-count: 3; /* Firefox */
        column-count: 3;
        -webkit-column-gap: 40px; /* Chrome, Safari, Opera */
        -moz-column-gap: 40px; /* Firefox */
        column-gap: 40px;
      }
    </style>
  </head>

  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
	<div class="container">
		<div class='row'>
			<div class="col-md-12">
                <form class='form-horizontal'>
					<div class="form-group">
                        <label for='periodo' class="control-label">Informe mes de: </label>
                        <div class="controls">
                        <div class="input-group">
                        <select name='periodo' id='periodo' class='input-sm col-md-10'>
                            <?php 
                            for ($abc = 11; $abc >= 0; $abc--) {
                                $mes = date("F Y", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                                $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                                echo "<option value='$valorMes' ".(($abc==1)?' selected="selected"':'').">$mes</option>";
                            }?>
                        </select>
                    </div></div></div>
                </form>
                <div class='row'>
                    <div class="col-md5">
                    <table class='table table-condensed' id='listaGastos'>
                        <thead><tr><th class='nombre'>Rubro</th><th>Mercaderias</th><th>Gastos</th><th>No Gravado</th><th>Lubricantes</th><th>Fletes</th></tr></thead><tfoot><tr><th class='nombre'></th><th>Mercaderias</th><th>Gastos</th><th>No Gravado</th><th>Lubricantes</th><th>Fletes</th></tr></tfoot>
                        <tbody></tbody>
                    </table>
                    </div>
                    <div class="newspaper" id='listaCompras'>
                    </div>
                </div>
			</div>
		</div>
      
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
        

    </div> <!-- /container -->
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
  <!--<script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
  <script src="//cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.js"></script>-->
  <script>
    $(document).ready(function() {
      $.post('func/listaInformeGastos.php', { mes: $('#periodo').val() }, function(data) {
        $('#listaGastos tbody').html(data);
        //$('#listaGastos').dataTable();
      });
      $.post('func/listaInformeOtrosMovimientos.php', { mes: $('#periodo').val() }, function(data) {
        $('#listaCompras').html(data);
        //$('#listaGastos').dataTable();
      });
      $('#periodo').change(function(){
        $.post('func/listaInformeGastos.php', { mes: $(this).val() }, function(data) {
          $('#listaGastos tbody').html(data);
          //$('#listaGastos').dataTable();
        });
        $.post('func/listaInformeOtrosMovimientos.php', { mes: $(this).val() }, function(data) {
          $('#listaCompras').html(data);
          //$('#listaGastos').dataTable();
        });
      });
    });
  </script>
</body>
</html>
