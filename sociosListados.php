<?php
$nivelRequerido = 3;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_ALL, 'es_ES.utf-8');
$titulo="Lista de asociados";
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
      #listadoAsociados td, #listadoAsociados th{
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

    </style>
  </head>

  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
	<div class="container">
		<div class='row'>
			<div class="col-md-12">
        <h1>Listado de asociados</h1>
				<table class='table table-striped table-condensed' id='listadoAsociados'>
					<thead><tr><th class='nombre no2' width='5%'>Codigo</th>
					<th >Socio</th>
          <th>Celular</th>
					<th>Ingreso</th>
          <th>Estado</th>
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
      $('#listadoAsociados tbody').html("<tr><td colspan='5'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
        $.post('func/setupListadoSocios.php', { mes: $('#periodo').val()}, function(data) {
          $('#listadoAsociados tbody').html(data);
          if($('#muestraComprimido').val() == 1){
            $('#comprimir').click();
          }
        });
        $('#periodo').change(function(){
          $('#listadoAsociados tbody').html("<tr><td colspan='5'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
            $.post('func/setupListadoSocios.php', { mes: $(this).val(), soloExternos: $('#filtroTipoViaje').val() }, function(data) {
              $('#listadoAsociados tbody').html(data);
              if($('#muestraComprimido').val() == 1){
                $('#comprimir').click();
              }
            });
          });
      $('#comprimir').click(function(){
        if ( $('.viaje').is(":visible") === true ) {
          $( ".viaje" ).hide();
          $('.comisionEncabezado').removeClass('info');
          $('#listadoAsociados').removeClass('table-striped');
          $('#muestraComprimido').val(1);
        } else {
          $( ".viaje" ).show();
          $('.comisionEncabezado').addClass('info');
          $('#listadoAsociados').addClass('table-striped');
          $('#muestraComprimido').val(0);
        }
      });
      $('#filtroTipoViaje').change(function(){
        $('#listadoAsociados tbody').html("<tr><td colspan='5'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
        $.post('func/setupListadoSocios.php', { mes: $('#periodo').val(), soloExternos: $(this).val() }, function(data) {
          $('#listadoAsociados tbody').html(data);
          if($('#muestraComprimido').val() == 1){
            $('#comprimir').click();
          }
        });
      });
    });
  </script>
</body>
</html>
