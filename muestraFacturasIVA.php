<?php
$nivelRequerido = 5;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

// asignaFacturasSocios.php
// lista las facturas emitidas sin turno asociado y permite asignarlas al turno actual

$titulo = "Muestra facturas de socios";
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
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
	<div class="container">
          <div class='row'>
            <div class="">
              <h2>Facturas socios</h2>
              <div class='col-md-12' id='detalle'>
              </div>
            </div>
            </div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
          $(document).ready(function() {
            $('#detalle').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
            $.get('func/ajaxMuestraFacturasIVA.php', function(data) {
              $('#detalle').html(data).fadeIn();
            });
          });
      </script>
  </body>
</html>
