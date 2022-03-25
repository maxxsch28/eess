<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$titulo = "Nueva foja de libros";
//ChromePhp::log($arrayYER);
if(isset($_GET['idFoja'])&&is_numeric($_GET['idFoja'])){
  $idFoja = $_GET['idFoja'];
  // levanto datos de la foja y de las actas
  $sql1 = "SELECT idFoja, libro, foja FROM dbo.[socios.libros] WHERE idFoja=$idFoja";
  $stmt = odbc_exec2( $mssql4, $sql1, __LINE__, __FILE__);
  $datosFoja = sqlsrv_fetch_array($stmt);
  
  $sql2 = "SELECT idActa, idFoja, acta, fecha, altas, bajas, detalle FROM dbo.[socios.actas] WHERE idFoja=$idFoja";
  $stmt2 = odbc_exec2( $mssql4, $sql2, __LINE__, __FILE__);
  ChromePhp::log($sql2);
  
} else if (isset($_GET['idFoja'])&&!is_integer($_GET['idFoja'])){
  unset($_GET['idFoja']);
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
      <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php')?>
      <style>
          .table th{text-align: center}
          .container {
                width: 1301px;
            }
          body {
          padding-top: 60px;
          padding-bottom: 40px;
        }
        input {
              text-align:right;
          }
    </style>
    <link href="css/print.css" rel="stylesheet" type="text/css" media="print"/><meta name="viewport" content="width=device-width, initial-scale=1.0">
   </head>
  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
    <div class="container">
        <div class="row">
              <h2>Libros cargados</h2>
              <div class='col-md-5 pull-right'>
                <legend>Últimas fojas</legend>
                <ul id='ultimas'></ul>
              </div>
              <div class='col-md-7'>
                <legend>Libros y fojas cargados</legend>
                <ul id='todos'></ul>
            </div>
          </div>
          <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
    <script>
    $(document).ready(function() {
      function listaUltimos(){
        $.post('func/ajaxListaUltimasFojas.php', function(data) {
          $('#ultimas').html(data);
        });
      }
      function listaTodos(){
        $.post('func/ajaxListaUltimasFojas.php?listaTodos=1', function(data) {
          $('#todos').html(data);
        });
      }
      listaUltimos();
      listaTodos();
    });
    </script>
  </body>
</html>
