<?php
$nivelRequerido = 3;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo="Controla documentos de compra informados por AFIP";
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
      .table td {
          font-size: 10pt;
          height: 1.7em;
      }
      .table {
          width:100%;
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
                        <h2>Facturas de <select name='periodo' id='periodo' class='input-sm btn '>
                            <?php 
                            for ($abc = 12; $abc >= 0; $abc--) {
                                $mes = date("F Y", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                                $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                                echo "<option value='$valorMes' ".(($abc==1)?' selected="selected"':'').">$mes</option>";
                            }?>
                            <?php
                            echo "<option value='".date("Y")."'>".date("Y")." anual</option><option value='".date("Y",strtotime("-1 year"))."'>".date("Y",strtotime("-1 year"))." anual</option>";
                            ?>
                        </select>
                        <div class='pull-right'>
                            <select name='doc' id='doc' class='btn-bg btn primary'>
                                <option value='todos' <?php echo ($_GET['status']!=='faltantes')?" selected='selected'":"";?>>Todos</option>
                                <option value='faltantes' <?php echo ($_GET['status']=='faltantes')?" selected='selected'":"";?> >Faltantes</option>
                            </select>&nbsp;&nbsp;&nbsp;
                            <select name='tipo' id='tipo' class='btn-bg btn '>
                                <option value='afip' selected='selected'>AFIP vs Sistemas</option>
                                <option value='calden' >Calden vs AFIP</option>
                                <option value='setup' >Setup vs AFIP</option>
                            </select>&nbsp;&nbsp;&nbsp;
                            <span id='refresh' class='glyphicon glyphicon-refresh gly-spin'></span>&nbsp;&nbsp;
                        </div>
                         </h2>
                        
                </form>
				<table class='table' id='ultimasFacturas'>
					<thead>
                    <tr>
                    <th width='20%'class='nombre'>Razon social</th>
                    <th width='3%'></th>
					<th width='12%'>PV-Numero</th>
					<th width='4%'>Neto</th>
					<th width='4%'></th>
					<th width='4%'>IVA</th>
					<th width='4%'>Total</th>
                    <th width='4%'>Ret / Imp. Internos</th>
					</tr></thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
      
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
		$(document).ready(function() {
            $('#refresh').addClass('gly-spin');
			$.post('func/listaDocumentosComprasAFIP.php', { mes: $('#periodo').val(), tipo:$('#tipo').val(),  status: $('#doc').val() }, function(data) {
                $('#ultimasFacturas tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>");
				$('#ultimasFacturas tbody').html(data);
				$('#ultimasFacturas tbody tr.doc').addClass('zebra');
                $('#refresh').removeClass('gly-spin');
			});
			$('#periodo').change(function(){
                $('#refresh').addClass('gly-spin');
				$.post('func/listaDocumentosComprasAFIP.php', { mes: $(this).val(), tipo:$('#tipo').val(), status: $('#doc').val() }, function(data) {
                    $('#ultimasFacturas tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>");
					$('#ultimasFacturas tbody').html(data);
					$('#ultimasFacturas tbody tr.doc').addClass('zebra');
                    $('#refresh').removeClass('gly-spin');
				});
			});
			$('#doc').change(function(){
                $('#refresh').addClass('gly-spin');
				$.post('func/listaDocumentosComprasAFIP.php', { mes: $('#periodo').val(), tipo:$('#tipo').val(), status: $(this).val() }, function(data) {
                    $('#ultimasFacturas tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>");
					$('#ultimasFacturas tbody').html(data);
					$('#ultimasFacturas tbody tr.doc').addClass('zebra');
                    $('#refresh').removeClass('gly-spin');
				});
			});
            $('#refresh').click(function(){
                $('#refresh').addClass('gly-spin');
                $.post('func/listaDocumentosComprasAFIP.php', { mes: $('#periodo').val(), tipo:$('#tipo').val(), status: $('#doc').val() }, function(data) {
                    $('#ultimasFacturas tbody').html("<tr><td colspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>");
                    $('#ultimasFacturas tbody').html(data);
                    $('#ultimasFacturas tbody tr.doc').addClass('zebra');
                    $('#refresh').removeClass('gly-spin');
                });
            });
		});
	</script>
</body>
</html>
