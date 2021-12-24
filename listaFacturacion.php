<?php
$nivelRequerido = 3;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo="Lista facturacion | Movistar";
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
    </style>
  </head>

  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
	<div class="container">
		<div class='row'>
			<div class="col-md-12">
                <form class='form-horizontal'>
					<div class="form-group">
                        <label for='periodo' class="control-label">Facturas mes de: </label>
                        <div class="controls">
                        <div class="input-group">
                        <select name='periodo' id='periodo' class='input-sm col-md-10'>
                            <?php 
                            for ($abc = 72; $abc >= 0; $abc--) {
                                $mes = date("F Y", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                                $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                                echo "<option value='$valorMes' ".(($abc==1)?' selected="selected"':'').">$mes</option>";
                            }?>
                        </select>
                    </div></div></div>
					<div class="form-group">
                        <label for='socios' class='no control-label'>Comisiones socios | no Socios:</label>
                        <div class="controls">
                        <div class="input-group">
                            <input type='text' name='socios' id='comSocio' value='15' class='input-sm col-md-1'/> | <input type='text' name='noSocios' id='comNoSocio' value='20' class='input-sm col-md-1'/>
                        </div></div></div>
                </form>
				<table class='table' id='ultimasFacturas'>
					<thead><tr><th width='30%' colspan='2' class='nombre'>Cliente</th>
					<th width='6%' class='no'>Factura</th>
					<th width='6%'>598</th>
					<th width='6%'>1</th>
					<th width='6%'>435</th>
					<th width='6%'>2245</th>
					<th width='3%'>2634</th>
					<th width='1%'>2296</th>
					<th width='1%'>2243</th>
					<th width='4%' class='no'>IVA 27%</th>
					<th width='4%' class='no'>IVA 21%</th>
					<th width='4%'>Facturas</th>
					<th width='4%'>A facturar</th></tr></thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
      
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
		$(document).ready(function() {
			$.post('func/listaUltimasFacturas.php', { mes: $('#periodo').val(), comSocio:$('#comSocio').val(), comNoSocio:$('#comNoSocio').val() }, function(data) {
				$('#ultimasFacturas tbody').html(data);
				$('#ultimasFacturas tbody tr:even').addClass('zebra');
			});
			$('#periodo').change(function(){
				$.post('func/listaUltimasFacturas.php', { mes: $(this).val(), comSocio:$('#comSocio').val(), comNoSocio:$('#comNoSocio').val() }, function(data) {
					$('#ultimasFacturas tbody').html(data);
					$('#ultimasFacturas tbody tr:even').addClass('zebra');
				});
			});
		});
	</script>
</body>
</html>
