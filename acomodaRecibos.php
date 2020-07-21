<?php
$nivelRequerido = 2;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo = "Acomoda recibos donde no desmarcamos la casillita";

if(!isset($_SESSION['ultimosMeses'])){
	$_SESSION['ultimosMeses']='';
	$currentMonth = (int)date('m');
	for($x = $currentMonth; $x > $currentMonth-6; $x--) {
		$_SESSION['ultimosMeses'] .= "<option value='".date('Y-m-01', mktime(0, 0, 0, $x, 1))."'>".date('F, Y', mktime(0, 0, 0, $x, 1))."</option>";
	}
}
if(!isset($_SESSION['ultimosCierresTesoreria'])){
	// carga los datos de esta orden
	$sqlCajas = "SELECT IdCierreCajaTesoreria, FechaCierre FROM dbo.CierresCajaTesoreria WHERE FechaCierre>=DATEADD(month, -1, GETDATE()) ORDER BY FechaCierre desc;";
	$stmt = odbc_exec( $mssql, $sqlCajas);
	$_SESSION['ultimosCierresTesoreria']='';
	while($rowCuentas = odbc_fetch_array($stmt)){
		$_SESSION['ultimosCierresTesoreria'].="<option value='$rowCuentas[IdCierreCajaTesoreria]'>".date_format($rowCuentas['FechaCierre'], "d/m/Y H:i:s")."</option>";
	}
}
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
        <div class="col-md-10">
          <h2>Últimos recibos PV98 cargados en Tesorería</h2>
          <div style='height:80%;overflow-y: scroll;'>
          <table id='libroDiario' class='table'>
          </table>
          </div>
        </div>
      </div>
    <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>

    </div> <!-- /container -->
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
    <script>
      $(document).ready(function() {
        // definimos las opciones del plugin AJAX FORM
        $.get('func/listaUltimosRecibos.php', function(data) {
          $('#libroDiario').html(data).slideDown('slow');
          $('.cambiaRecibo').click(function(){
            // graba cambios en los lotes de tarjetas
            $(this).html("<center><img src='img/ajax-loader-chico.gif'/></center>").removeClass().fadeIn();
            // elimino el "actualizaLote_" del id para obtener el idLote correspondiente
            var id = $(this).attr('id').split("_");
            var IdRecibo = id[1];
            $.get('func/ajaxReimputaReciboCajaAdministracion.php?IdRecibo=' + IdRecibo, function(data) {
              $('#t'+IdRecibo).html("<tr><td colspan='5' align='center'><span class='btn btn-default btn-xs graba' >Recibo cambiado a CAJA COBRANZAS ADMINISTRATIVAS</span></td></tr>").removeClass().fadeIn();
            });
          });
        });
        
      });
    </script>
  </body>
</html>
