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
            <div class='col-md-5'>
          <h2>Litros disponibles</h2>
            <div id='litros'>
            </div>
            </div>
          
          <div class='col-md-7'>
          <h2>Mecanica para vales por percepciones</h2>
          <p>
            Luego de terminar de hacer las distintas facturas se debe realizar un vale desde "Emisión con imputación a turno abierto".<br/>
            Se selecciona "Vale a clientes", indicando el cliente IB01, se hace un solo vale por el total de las percepciones a cobrar (en lo que va del día, $<?php echo sprintf("%.2f",$_SESSION['percepcionesFcDiferencia'])?>)<br/>
            Cuando Sarasola baja un pago de las percepciones el ingreso del efectivo se tiene que hacer como "Cancelación sin imputación a turnos" seleccionando el mismo cliente. En teoría lo cancelado debe cancelar una suma de vales exacta.
          </p>
          </div>
          </div>
          <div class='row'>
            <div class="col-md-12">
              <h2>Facturas socios emitidas hoy</h2>
              <div id='detalle'>
              </div>
            </div>
            </div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
          $(document).ready(function() {
            $('#detalle').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
              $('#litros').html("<center><br/><img src='img/ajax-loader.gif'/><br/>&nbsp;<br/>&nbsp;<br/></center>").fadeIn();
            $.get('func/ajaxMuestraFacturasIVA.php?teresa=1', function(data) {
              $('#detalle').html(data).fadeIn();
            });
            $.get('func/ajaxMuestraLitrosDisponibles.php?teresa=1', function(data) {
              $('#litros').html(data).fadeIn();
            });
            setInterval(function(){
              $('#detalle').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
              $.get('func/ajaxMuestraFacturasIVA.php?teresa=1', function(data) {
                $('#detalle').html(data).fadeIn();
              });
              $.get('func/ajaxMuestraLitrosDisponibles.php?teresa=1', function(data) {
                $('#litros').html(data).fadeIn();
              });
            }, 10000);
          });
      </script>
  </body>
</html>
