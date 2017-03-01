<?php
// calculaPromedios.php
//include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$tabla=$tabla2="";
$ventasPorDia = array();
$promedioVentasPorDia = array();

// sql para promedio hist�rico
$sqlPromedioHistorico = "SELECT  SUM(dbo.MovimientosDetalleFac.Cantidad) as ventas, IdArticulo, COUNT(distinct(dateadd(dd,0, datediff(dd,0,Fecha)))) as dias, (SUM(dbo.MovimientosDetalleFac.Cantidad)/COUNT(distinct(dateadd(dd,0, datediff(dd,0,Fecha))))) as promedio FROM dbo.MovimientosDetalleFac, dbo.MovimientosFac WHERE IdArticulo in (2068,2069,2078,2076) AND dbo.MovimientosFac.DocumentoCancelado=0 AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND fecha<'".date("Y-m-d")."' GROUP BY IdArticulo";
$stmt = odbc_exec( $mssql, $sqlPromedioHistorico);
if( $stmt === false ){
     echo "Error in executing query.</br>";
     die( print_r( sqlsrv_errors(), true));
}
while($rowPromedioHistorico = odbc_fetch_array($stmt)){
	$promedioHistorico[$rowPromedioHistorico[1]]=sprintf("%01.2f",$rowPromedioHistorico[3]);
}

// sql para cantidad de dias con ventas para cada producto
$sqlDiasConVentas = "SELECT distinct(dateadd(dd,0, datediff(dd,0,Fecha))) as fecha, datepart(dw, Fecha) as dia, IdArticulo FROM dbo.MovimientosDetalleFac, dbo.MovimientosFac WHERE dbo.MovimientosDetalleFac.IdArticulo in (2068,2069,2078,2076) AND dbo.MovimientosFac.DocumentoCancelado=0 AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND fecha<'".date("Y-m-d")."' GROUP BY IdArticulo, fecha order by idArticulo, dia, fecha";
$stmt = odbc_exec( $mssql, $sqlDiasConVentas);
while($rowDiasConVentas = odbc_fetch_array($stmt)){
	//Array ( [0] => DateTime Object ( [date] => 2011-10-16 00:00:00 [timezone_type] => 3 [timezone] => America/Argentina/Buenos_Aires ) [fecha] => DateTime Object ( [date] => 2011-10-16 00:00:00 [timezone_type] => 3 [timezone] => America/Argentina/Buenos_Aires ) [1] => 1 [dia] => 1 [2] => 2068 [IdArticulo] => 2068 ) 
	if(!isset($diasConVentas[$rowDiasConVentas[2]][$rowDiasConVentas[1]]))
		$diasConVentas[$rowDiasConVentas[2]][$rowDiasConVentas[1]]=0;
	$diasConVentas[$rowDiasConVentas[2]][$rowDiasConVentas[1]]++;
}

// sql para promedio hist�rico por d�as
$sqlPromedioDiaSemana = "SELECT datepart(dw, Fecha) as dia, SUM(dbo.MovimientosDetalleFac.Cantidad), IdArticulo, COUNT(IdArticulo) FROM dbo.MovimientosDetalleFac, dbo.MovimientosFac WHERE  dbo.MovimientosDetalleFac.IdArticulo in (2068,2069,2078,2076) AND dbo.MovimientosFac.DocumentoCancelado=0 AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND dbo.MovimientosDetalleFac.Cantidad>0 AND Fecha<'".date("Y-m-d")."' GROUP BY datepart(dw, Fecha), IdArticulo order by dia;";
$stmt = odbc_exec( $mssql, $sqlPromedioDiaSemana);
if( $stmt === false ){
     echo "Error in executing query.</br>";
     die( print_r( sqlsrv_errors(), true));
}

$tablaPromedioDiaSemana="<table class='table'><thead><tr><th></th><th>$articulo[2068]</th><th>$articulo[2069]</th><th>$articulo[2076]</th><th>$articulo[2078]</th></tr></thead><tbody>";
while($rowPromedioDiaSemana = odbc_fetch_array($stmt)){
	//Tengo que sumar en un array para cada tipo de combustible, contar y luego hacer promedio
	//Array ( [0] => Wednesday [] => 1084 [1] => 76340.9271 [2] => 2068 [IdArticulo] => 2068 [3] => 1084 )
	$ventasPorDiaSemana[$rowPromedioDiaSemana[2]][$rowPromedioDiaSemana[0]]=$rowPromedioDiaSemana[1];
	$promedioVentasPorDiaSemana=sprintf("%01.2f",$rowPromedioDiaSemana[1]/$diasConVentas[$rowPromedioDiaSemana[2]][$rowPromedioDiaSemana[0]]);
	//$promedioVentasPorDiaSemana[$rowPromedioDiaSemana[2]][$rowPromedioDiaSemana[0]]=$rowPromedioDiaSemana[1]/$rowPromedioDiaSemana[3];
	//Sumo lo mismo pero sin incluir los valores menores a cierto rango por tipo de combustible (tratando de depurar los d�as que no hubo combustible
	if(isset($encabezadoFilaDia)&&is_array($encabezadoFilaDia)&&!isset($encabezadoFilaDia[$rowPromedioDiaSemana[0]]))$tablaPromedioDiaSemana.="</tr>";
	if(!isset($encabezadoFilaDia[$rowPromedioDiaSemana[0]])){
		if((date("N")+1==$rowPromedioDiaSemana[0]))
			$tablaPromedioDiaSemana.="<tr class='hoy'><td>".$date2[$rowPromedioDiaSemana[0]]."</td>";
		else
			$tablaPromedioDiaSemana.="<tr><td>".$date2[$rowPromedioDiaSemana[0]]."</td>";
		$encabezadoFilaDia[$rowPromedioDiaSemana[0]]=$rowPromedioDiaSemana[0];
	}
	$tablaPromedioDiaSemana.="<td>$promedioVentasPorDiaSemana</td>";
	//Array ( [0] => 1 [dia] => 1 [1] => 24693.9700 [] => 554 [2] => 2068 [IdArticulo] => 2068 [3] => 554 )
}
echo $tablaPromedioDiaSemana."<tr class='promGeneral'><td>General</td><td>$promedioHistorico[2068]</td><td>$promedioHistorico[2069]</td><td>$promedioHistorico[2076]</td><td>$promedioHistorico[2078]</td></tr></tbody></table>";
?>