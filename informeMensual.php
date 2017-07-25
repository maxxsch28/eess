<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo="Prepara informe mensual";
// acomoda Movistar
//include('func/acomodaProMovistar.php');

//select Fecha, Anio, IdTipoMovimientoProveedor, puntoventa, numero, razonsocial, netoNoGravado, NetoMercaderias, NetoCombustibles, NetoLubricantes, NetoGastos, NetoFletes, Total, idasiento, dbo.cuentasgastos.Descripcion as CuentaGasto, dbo.MovimientosDetallePro.Descripcion  from dbo.MovimientosPro, dbo.CuentasGastos, dbo.MovimientosDetallePro where Fecha>='2015-04-01' and Fecha<'2015-05-01' and dbo.MovimientosDetallePro.IdCuentaGastos=dbo.CuentasGastos.IdCuentaGastos and dbo.MovimientosPro.IdMovimientoPro=dbo.MovimientosDetallePro.IdMovimientoPro and (IdTipoMovimientoProveedor<>'RV' AND IdTipoMovimientoProveedor<>'VP') and dbo.movimientosdetallepro.IdCuentaGastos<>43 order by CuentaGasto asc, RazonSocial asc

?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
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
                    <input type="hidden" name='muestraSoloExternos' value='0' id='muestraSoloExternos'/>
                    <input type="hidden" name='muestraComprimido' value='0' id='muestraComprimido'/>
                    <div class="form-group">
                        <label for='periodo' class="control-label">Informe mensual de: <select name='periodo' id='periodo' class='input-sm '>
                             <?php 
                            for ($abc = 11; $abc >= 0; $abc--) {
                                $mes = date("F Y", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                                $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                                echo "<option value='$valorMes' ".(($abc==1)?' selected="selected"':'').">$mes</option>";
                            }?>
                            </select></label><div style='float:right'><div id='comprimir' class='btn btn-success no2'>Ver detallado</div>
                            <!--<select name='filtroTipoViaje' id='filtroTipoViaje' class='btn btn-danger'>
                                <option value='0' selected="selected">Todos los viajes</option>
                                <?php foreach($_SESSION['transporte_tipos_comisiones'] as $key => $nombre){
                                    echo "<option value='$key'>$nombre</option>";
                                }?>
                                </select>-->
                                
                        </div>
                        </div>
                </form>	
                <div class='row'>
                    <div class="col-md-5">
                    <table class='table table-condensed' id='listaGastos'>
                        <thead><tr><th class='nombre'>Rubro</th><th>Neto</th></tr></thead>
                        <tbody></tbody>
                    </table>
                    </div>
                    <div class="col-md-5">
                    <table class='table table-condensed' id='listaCompras'>
                        <thead><tr><th class='nombre'>Rubro</th><th>Neto</th></tr></thead>
                        <tbody></tbody>
                    </table>
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
      $.post('func/listaInformeCompras.php', { mes: $('#periodo').val() }, function(data) {
        $('#listaGastos tbody').html(data);
        if($('#muestraComprimido').val() == 1){
          $('#comprimir').click();
        }
        //$('#listaGastos').dataTable();
      });
      $.post('func/listaInformeOtrosMovimientos.php', { mes: $('#periodo').val() }, function(data) {
        $('#listaCompras tbody').html(data);
        //$('#listaGastos').dataTable();
      });
      $('#periodo').change(function(){
        $.post('func/listaInformeCompras.php', { mes: $(this).val() }, function(data) {
          $('#listaGastos tbody').html(data);
          if($('#muestraComprimido').val() == 1){
            $('#comprimir').click();
          }
          //$('#listaGastos').dataTable();
        });
        $.post('func/listaInformeOtrosMovimientos.php', { mes: $(this).val() }, function(data) {
          $('#listaCompras tbody').html(data);
          //$('#listaGastos').dataTable();
        });
      });
       $('#comprimir').click(function(){
        if ( $('.viaje').is(":visible") === true ) {
          $( ".viaje" ).hide();
          $('.comisionEncabezado').removeClass('info');
          $('#comisionesSocios').removeClass('table-striped');
          $('#muestraComprimido').val(1);
        } else {
          $( ".viaje" ).show();
          $('.comisionEncabezado').addClass('info');
          $('#comisionesSocios').addClass('table-striped');
          $('#muestraComprimido').val(0);
        }
        //$('.viaje').toggle();
      });
    });
  </script>
</body>
</html>
