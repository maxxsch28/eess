<?php
$nivelRequerido = 2;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo = "Conciliar Cuenta Corriente YPF - CaldenOil (conciliatutti)";
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
      html {
    
      }
      @page {
        size: A4;
        margin: 10px 0 10px;
      }
      @media print {
      html, body {
        width: 210mm;
        height: 297mm;
      }
      .mitad{
        
      }
      }
      .flash {
        -moz-animation: flash 1s ease-out;
        -moz-animation-iteration-count: 10;

        -webkit-animation: flash 1s ease-out;
        -webkit-animation-iteration-count: 10;

        -ms-animation: flash 1s ease-out;
        -ms-animation-iteration-count: 10;
      }

      @-webkit-keyframes flash {
        0% { background-color: none; }
        50% { background-color: #fbf8b2; }
        100% { background-color: none; }
      }

      @-moz-keyframes flash {
        0% { background-color: none; }
        50% { background-color: #ff0000; }
        100% { background-color: #dd0000; }
      }

      @-ms-keyframes flash {
        0% { background-color: none; }
        50% { background-color: #fbf8b2; }
        100% { background-color: none; }
      }
    </style>
   
  </head>

  <body>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
    <div class="container">
      <div class='row'>
        <form name='nuevaOP' id='nuevaOP' class=''>
          <div class="col-md-12">
            <h2>YPF - CaldenOil</h2>
            <table id='cuentaYPF' class='table table-condensed'>
            </table>
          </div>
          </form>
      </div>
    <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
  </div> <!-- /container -->
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
  <script>
    $(document).ready(function() {
      $('#cuentaYPF').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
     
     
      $.post( "func/ajaxVerificaConciliaYPF.php", function( data ) {
        $("#cuentaYPF").html( data );
      });
    
    });
  </script>
</body>
</html>
