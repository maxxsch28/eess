<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$titulo = "Empleados y Clientes";
?>
<!DOCTYPE html>
<html lang="es">
  <head>
      <?php include ('/include/header.php')?>
      <!-- lugar para incorporar cosas específicas -->
      <style>
        .listNav { margin:0 0 10px; }

        .ln-letters { overflow:hidden; }
        .ln-letters a { font-size:0.9em; display:block; float:left; padding:2px 6px; border:1px solid silver; border-right:none; text-decoration:none; }
        .ln-letters a.ln-last { border-right:1px solid silver; }
        .ln-letters a:hover,
        .ln-letters a.ln-selected { background-color:#eaeaea; }
        .ln-letters a.ln-disabled { color:#ccc; }
        .ln-letter-count { text-align:center; font-size:0.8em; line-height:1; margin-bottom:3px; color:#336699; }
      </style>
  </head>
  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
    <div class="container">
		<div class="row">
            <div id='letras' class="ln-letters"><a class='let' id="_" href="#">0-9</a><a class='let' id="a" href="#">A</a><a class='let' id="b" href="#">B</a><a class='let' id="c" href="#">C</a><a class='let' id="d" href="#">D</a><a class='let' id="e" href="#">E</a><a class='let' id="f" href="#">F</a><a class='let' id="g" href="#">G</a><a class='let' id="h" href="#">H</a><a class='let' id="i" href="#">I</a><a class='let' id="j" href="#">J</a><a class='let' id="k" href="#">K</a><a class='let' id="l" href="#">L</a><a class='let' id="m" href="#">M</a><a class='let' id="n" href="#">N</a><a class='let' id="o" href="#">O</a><a class='let' id="p" href="#">P</a><a class='let' id="q" href="#">Q</a><a class='let' id="r" href="#">R</a><a class='let' id="s" href="#">S</a><a class='let' id="t" href="#">T</a><a class='let' id="u" href="#">U</a><a class='let' id="v" href="#">V</a><a class='let' id="w" href="#">W</a><a class='let' id="x" href="#">X</a><a class='let' id="y" href="#">Y</a><a class='let' id="z ln-last" href="#">Z</a></div>
            <table class="table table-striped" id="listaClientes">
                <tbody>
                </tbody>
            </table>
		</div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
         $(document).ready(function() {
            $('.let').click(function(){
                $( "#listaClientes tbody" ).html("<tr><td colspan=2><center><img src='img/ajax-loader.gif'/></center></td></tr>");
                 $.post('func/obtieneEmpleadosPorCliente.php', { letra: $(this).attr("id") }, function( data ) {
                     $( "#listaClientes tbody" ).html( data );
                 });
            });
        });       
	</script>
  </body>
</html>
