<?php
include('include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$titulo = "Titulo";
$sql = "select 1";
$stmt = sqlsrv_query( $mssql, $sql);
while($rowMediciones = sqlsrv_fetch_array($stmt)){
	$mediciones[$rowMediciones[0]]=$rowMediciones[1];
}

?>
<!DOCTYPE html>
<html lang="es">
  <head>
      <?php include ('/include/header.php')?>
      // lugar para incorporar cosas específicas
  </head>
  <body>
	<?php include('include/menuSuperior.php') ?>
    <div class="container">
		<!-- Example row of columns -->
		<div class="row">
			
		</div>
        <?php include ('include/footer.php')?>
    </div> <!-- /container -->
	<?php include('include/termina.php');?>
	<script>
	</script>
  </body>
</html>
