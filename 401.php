<?php
	/*
		UserCake Version: 1.0
		http://usercake.com
		

	*/

$titulo = "401 - Ingreso no autorizado";
require_once("include/inicia.php");
require_once("include/config.php"); ?>

<!DOCTYPE html>
<html lang="es">
  <head>
      <?php include ('/include/head.php')?>
  </head>
  <body>
	<?php include('include/menuSuperior.php') ?>
    <div class="container">
		<!-- Example row of columns -->
		<div class="row">
            &nbsp;<br/><br/>&nbsp;<br/><br/>&nbsp;<br/><br/>&nbsp;<br/><br/>
			<center><img src="/assets/img/logo.png"></center><br/>
            <center>
                <h1>Error 401</h1>
                <br>
                <p class="lead">Su usuario no está autorizado a ingresar en esta página.</p>
            </center>
		</div>
        <?php include ('include/footer.php')?>
    </div> <!-- /container -->
	<?php include('include/termina.php');?>
	<script>
	</script>
  </body>
</html>
