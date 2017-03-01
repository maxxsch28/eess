<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');



$articulo = 'mas.oreo duo ';
// $articulo = '%TANG';
$articulo2 = "";//" AND Descripcion like ('%70G%')";
//$articulo2 = " AND Descripcion   like ('%women%')";// AND Descripcion NOT LIKE ('%duo%') AND Descripcion NOT LIKE ('%relle%')";// AND Descripcion  like ('%ent%')";//AND Descripcion not like ('%leche%')";//AND Descripcion not like ('%varios%')";// ";// AND Descripcion NOT LIKE ('%LECH%')";

$idArticuloReemplazo='1800';

$select = "SELECT * FROM dbo.Articulos WHERE Descripcion LIKE ('$articulo%')$articulo2<br>";

$updateFacturasConArticulo = "UPDATE dbo.MovimientosDetalleFac SET IdArticulo='$idArticuloReemplazo'  WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('$articulo%')$articulo2);<BR/>";

$updateMovimientoStock	= "UPDATE dbo.PedidosStockDetalle SET IdArticulo='$idArticuloReemplazo'  WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('$articulo%')$articulo2);<BR/>";

$updateArticulosNoAjustados = "UPDATE dbo.ArticulosNoAjustados SET IdArticulo='$idArticuloReemplazo'  WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('$articulo%')$articulo2);<BR/>";

$updateCambiosPrecios	= "UPDATE dbo.CambiosPrecio SET IdArticulo='$idArticuloReemplazo'  WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('$articulo%')$articulo2);<BR/>";

$updateCierreControlStock	= "UPDATE dbo.CierresDetalleControlStock SET IdArticulo='$idArticuloReemplazo'  WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('$articulo%')$articulo2);<BR/>";

$updateRecepcionMercaderia = "UPDATE dbo.ProformasRecepcionMercaderiasDetalle SET IdArticulo='$idArticuloReemplazo'  WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('$articulo%')$articulo2);<BR/>";

$updateMovimientosStock		= "UPDATE dbo.MovimientosStock SET IdArticulo='$idArticuloReemplazo' WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('$articulo%')$articulo2);<BR/>";

$updateMovimientosCtaProDetalle		= "UPDATE dbo.MovimientosDetallePro SET IdArticulo='$idArticuloReemplazo' WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('$articulo%')$articulo2);<BR/>";

echo $select.'<br/>'.$updateFacturasConArticulo.'<br/>'.$updateMovimientoStock.'<br/>'.$updateArticulosNoAjustados.'<br/>'.$updateCambiosPrecios.'<br/>'.$updateCierreControlStock.'<br/>'.$updateRecepcionMercaderia.'<br/>'.$updateMovimientosStock."<br/>$updateMovimientosCtaProDetalle<br/><br/>";

$sqlArticulos = "select * from dbo.Articulos where Descripcion like ('$articulo%')$articulo2 ;";
//echo $sqlArticulos;
$stmt = odbc_exec( $mssql, $sqlArticulos);
$A=0;
while($row = odbc_fetch_array($stmt)){
	//print_r($row);
	echo "$row[IdArticulo] ($row[Codigo])- $row[Descripcion]<br/>";
	$sqlFacturasConArticulo = "SELECT * FROM dbo.MovimientosDetalleFac WHERE idArticulo='$row[IdArticulo]'";
	//echo $sqlFacturasConArticulo;
	$stmt2 = odbc_exec( $mssql, $sqlFacturasConArticulo);
	if($stmt2)
	while($row2 = odbc_fetch_array($stmt2)){
		echo "Fc $row2[IdMovimientoFac] - $row2[Cantidad] - $row2[Precio]<br/>";
		$A++;
	}
	$sqlMovimientoStock = "select * from dbo.PedidosStockDetalle WHERE idArticulo='$row[IdArticulo]'";
	//echo $sqlFacturasConArticulo;
	$stmt3 = odbc_exec( $mssql, $sqlMovimientoStock);
	if($stmt3)
	while($row3 = odbc_fetch_array($stmt3)){
		echo "St $row3[IdPedidoStock] - $row3[CantidadPedida] - $row3[CantidadRepuesta]<br/>";
		$A++;
	}
	
	//$sqlFacturasConArticulo 	= "SELECT * FROM dbo.MovimientosDetalleFac WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('%$articulo%'))";
		
	//$sqlMovimientoStock		= "SELECT * FROM dbo.PedidosStockDetalle WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('%$articulo%'))";
		
	//$sqlrticulosNoAjustados 	= "SELECT * FROM dbo.ArticulosNoAjustados WHERE IdArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('%$articulo%'))";
		
	//$sqlCambioPrecios 		= "SELECT * FROM dbo.CambiosPrecio WHERE IdArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('%PAST.MENTHO%'))";
		
	//$sqlCierreControlStock 	= "SELECT * FROM dbo.CierresDetalleControlStock WHERE IdArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('%PAST.MENTHO%'))";
		
	//$sqlRecepcionMercaderia 	= "SELECT * FROM dbo.ProformasRecepcionMercaderiasDetalle WHERE IdArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('%PAST.MENTHO%'))";

	
	
	//$updateFacturasConArticulo = "UPDATE dbo.MovimientosDetalleFac SET IdArticulo='$idArticuloReemplazo'  WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('%$articulo%'))";
	//$updateMovimientoStock	= "UPDATE dbo.PedidosStockDetalle SET IdArticulo='$idArticuloReemplazo'  WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('%$articulo%'))";
	//$updateArticulosNoAjustados = "UPDATE dbo.ArticulosNoAjustados SET IdArticulo='$idArticuloReemplazo'  WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('%$articulo%'))";
	// $updateCambiosPrecios	= "UPDATE dbo.CambiosPrecio SET IdArticulo='$idArticuloReemplazo'  WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('%$articulo%'))";
	// $updateCierreControlStock	= "UPDATE dbo.CierresDetalleControlStock SET IdArticulo='$idArticuloReemplazo'  WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('%$articulo%'))";
	// $updateRecepcionMercaderia = "UPDATE dbo.ProformasRecepcionMercaderiasDetalle SET IdArticulo='$idArticuloReemplazo'  WHERE idArticulo in (select IdArticulo from dbo.Articulos where Descripcion like ('%$articulo%'))";
	
	//$sql
	
	
	echo "<br/>";
}
echo "Total $A";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Acomoda Articulos | Pedidos YPF</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet"/>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
	<link href="css/print.css" rel="stylesheet" type="text/css" media="print"/>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

	
  </head>

  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
    <div class="container">
		<!-- Example row of columns -->
		<div class="row">
			<div class="col-md-4">
				<h2>Combustibles<span class='sh2'><?php echo date("d/m/y H:i:s")?></span></h2>
				<p>
			</p>
			<!--<p><a class="btn btn-default" href="#" id='detallesTanques'>Detalle por tanques &raquo;</a></p>-->
			</div>
		</div>
	<!--	<hr>
		<footer>
			<p>&copy; Cooperativa de Transporte 2012</p>
		</footer>-->
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
	</script>
  </body>
</html>
