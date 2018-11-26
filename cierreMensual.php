<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

$titulo="Presenta cierre mensual de Tesorería";
// acomoda Movistar
//include('func/acomodaProMovistar.php');

//select Fecha, Anio, IdTipoMovimientoProveedor, puntoventa, numero, razonsocial, netoNoGravado, NetoMercaderias, NetoCombustibles, NetoLubricantes, NetoGastos, NetoFletes, Total, idasiento, dbo.cuentasgastos.Descripcion as CuentaGasto, dbo.MovimientosDetallePro.Descripcion  from dbo.MovimientosPro, dbo.CuentasGastos, dbo.MovimientosDetallePro where Fecha>='2015-04-01' and Fecha<'2015-05-01' and dbo.MovimientosDetallePro.IdCuentaGastos=dbo.CuentasGastos.IdCuentaGastos and dbo.MovimientosPro.IdMovimientoPro=dbo.MovimientosDetallePro.IdMovimientoPro and (IdTipoMovimientoProveedor<>'RV' AND IdTipoMovimientoProveedor<>'VP') and dbo.movimientosdetallepro.IdCuentaGastos<>43 order by CuentaGasto asc, RazonSocial asc
setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish');
if(!isset($_SESSION['ultimoDiaMes'])){
  $_SESSION['ultimoDiaMes']='';
  $currentMonth = date('m');
  for($x = $currentMonth; $x > $currentMonth-12; $x--) {
    $dia = new DateTime(date("Y-m-01", strtotime("-$x months")));
    $ultimoDia = new DateTime(date("Y-m-01", strtotime("-$x months")));
    $ultimoDia->modify("last day of previous month");
    $_SESSION['ultimoDiaMes'] .= "<option value='".$ultimoDia->format('d-m-Y')."'>".$dia->format('F')."</option>";
  }
}


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
                    <input type="hidden" name='fechaCierre' value='<?php echo date('d/m/Y')?>' id='fechaCierre'/>
                    <?php if(!isset($_GET['saldoCaja'])){?>
                    <div class="form-group" id='rop'> 
                      <div class="col-md-6 form-group">
                      <label class="control-label" for="rangoFechas">Rango de fechas</label>
                      <div class="controls">
                      <div class="input-group" id='rop'>
                        <input type='text' name='activaMes' id='activaMes' class="input-sm form-control col-md-5" placeholder='Click para activar mensual'/>
                          <select name='mes' id='mes' class='input-sm form-control col-md-5 selector' style='display:none' disabled='disabled'>
                                <option value='' selected='selected'>Filtrar por mes</option>
                                 <?php echo $_SESSION['ultimoDiaMes']; ?>
                          </select>
                        <span class="input-group-addon">o</span>
                        <input type='text' name='activaDia' id='activaDia' class="input-sm form-control col-md-5" data-plus-as-tab='true' placeholder='Click para activar dia' style='display:none'/>
                        <input type='text' name='dia' id='dia' class="input-sm form-control col-md-5"  value="<?php echo date("d/m/Y");?>" data-date-format="dd/mm/yyyy"/><span class='input-group-addon glyphicon glyphicon-refresh' id='refresh'></span>
                      </div></div></div>
                  </div>
                  <?php } ?>
                </form>	
                <div class='row'>
                    <div class="col-md-6">
                    <h2>Efectivo</h2>
                    <table class='table table-condensed' id='listaEfectivo'>
                        <thead><tr><th class='nombre'>Detalle</th><th>Monto</th></tr></thead>
                        <tbody></tbody>
                    </table>
                    </div>
                    <div class="col-md-6">
                    <h2>Cheques</h2>
                    <table class='table table-condensed' id='listaCheques'>
                        <thead><tr><th class='nombre'>Detalle</th><th>Monto</th></tr></thead>
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
      $('#activaMes').click(function() {
        $('#activaMes').hide();
        $('#activaDia').show();
        $('#mes').show();
        $('#dia').val('');
        $('#dia').hide();
        $('#dia').attr('disabled', true);
        $('#mes').attr('disabled', false);
      });
      $('#activaDia').click(function() {
        $('#activaDia').hide();
        $('#activaMes').show();
        $('#mes').val('Filtrar por mes');
        $('#dia').val('<?php echo date('d/m/Y')?>');
        $('#dia').show();
        $('#dia').attr('disabled', false);
        $('#mes').hide();
        $('#mes').attr('disabled', true);
      });
      
      $('#dia').datepicker({
        format: "dd/mm/yyyy",
        autoclose: true
      });
      
      muestraCierre();
      
      $('#mes').change(function(){
        $('#fechaCierre').val($(this).val());
        muestraCierre($(this).val());
      });
      $('#dia').change(function(){
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
        $.post('func/listaCierreTesoreriaEfectivo.php', { fechaCierre: $('#fechaCierre').val(), saldoCaja: <?php if(isset($_GET['saldoCaja']))echo "1"; else echo "0";?> }, function(data) {
          // comienza magia json
          $('#listaEfectivo tbody tr:has(td)').remove();
          $('#listaCheques tbody tr:has(td)').remove();
          $.each(data, function (i, item) {
            var trHTML = '';
            if(item.clase == 'neg'){
              var neg = " class='neg'";
              var signo = '-';
            } else {
              var neg = '';
              var signo = '';
            }
            // cambiar para que de acuerdo a si la variable T es Efectivo o Cheque agregue la fila donde corresponda.
            trHTML = '<tr'+ neg +'><td>' + item.txt + '</td><td>'+signo+'$ ' + item.importe + '</td></tr>';
            $('#lista'+item.t+' tbody').append(trHTML); 
          });
          //$('#listaEfectivo tbody').html(data);
        }, "json");
      
      }
      $('#refresh').click(function(){
        muestraCierre();
      })

      function mostrarLoader(){
        $('#listaEfectivo tbody').html("<tr><td colspan=2><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
        $('#listaCheques tbody').html("<tr><td colspan=2><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
      };
    });
  </script>
</body>
</html>
