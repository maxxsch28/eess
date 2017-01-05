<?php
include('include/inicia.php');

class Tanques{
	public function Tanques(){
		
	}


}


// Despachos extraidos de la tabla de despachos, contiene todo lo que salió de los surtidores y no solo lo facturado
$sqlDespachos = "select IdArticulo, SUM(Cantidad) from dbo.Despachos WHERE Fecha>='".date("Y-m-d")."' GROUP BY IdArticulo;";
$stmt = sqlsrv_query( $mssql, $sqlDespachos);
while($row = sqlsrv_fetch_array($stmt)){
	$despachos[$row[0]]=$row[1];
}


// sql para promedio histórico
$fechaParaTraerPromedio = date("Y-m-d",strtotime( '-1 day' ));
$sqlRevisaPromedioHistorico = "SELECT promedio, idArticulo FROM promedios WHERE fecha='$fechaParaTraerPromedio' ORDER BY idArticulo";
$result = $mysqli->query($sqlRevisaPromedioHistorico);
if($result&&$result->num_rows>0){
	while($rowPromedioHistorico = $result->fetch_assoc()){
		$estadoComb[$rowPromedioHistorico['idArticulo']]['promedio']=sprintf("%01.2f",$rowPromedioHistorico['promedio']);
	}
} else {
	$sqlPromedioHistorico = "SELECT SUM(dbo.MovimientosDetalleFac.Cantidad) as ventas, IdArticulo, COUNT(distinct(dateadd(dd,0, datediff(dd,0,Fecha)))) as dias, (SUM(dbo.MovimientosDetalleFac.Cantidad)/COUNT(distinct(dateadd(dd,0, datediff(dd,0,Fecha))))) as promedio FROM dbo.MovimientosDetalleFac, dbo.MovimientosFac WHERE IdArticulo in (2068,2069,2078,2076) AND dbo.MovimientosFac.DocumentoCancelado=0 AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND getdate()-fecha>30 AND fecha<'".date("Y-m-d")."' GROUP BY IdArticulo";
	$stmt = sqlsrv_query( $mssql, $sqlPromedioHistorico);
	while($rowPromedioHistorico = sqlsrv_fetch_array($stmt)){
		$estadoComb[$rowPromedioHistorico[1]]['promedio']=sprintf("%01.2f",$rowPromedioHistorico[3]);
		$mysqli->query("INSERT INTO promedios SET fecha='$fechaParaTraerPromedio', promedio='$rowPromedioHistorico[3]', idArticulo='$rowPromedioHistorico[1]'");
	}
}

// sql para cantidad de dias con ventas para cada producto
$sqlDiasConVentas = "SELECT distinct(dateadd(dd,0, datediff(dd,0,Fecha))) as fecha, datepart(dw, Fecha) as dia, IdArticulo FROM dbo.MovimientosDetalleFac, dbo.MovimientosFac WHERE dbo.MovimientosDetalleFac.IdArticulo in (2068,2069,2078,2076) AND dbo.MovimientosFac.DocumentoCancelado=0 AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND getdate()-fecha>30 AND fecha<'".date("Y-m-d")."' GROUP BY IdArticulo, fecha order by idArticulo, dia, fecha";
$stmt = sqlsrv_query( $mssql, $sqlDiasConVentas);
while($rowDiasConVentas = sqlsrv_fetch_array($stmt)){
	//Array ( [0] => DateTime Object ( [date] => 2011-10-16 00:00:00 [timezone_type] => 3 [timezone] => America/Argentina/Buenos_Aires ) [fecha] => DateTime Object ( [date] => 2011-10-16 00:00:00 [timezone_type] => 3 [timezone] => America/Argentina/Buenos_Aires ) [1] => 1 [dia] => 1 [2] => 2068 [IdArticulo] => 2068 ) 
	if(!isset($diasConVentas[$rowDiasConVentas[2]][$rowDiasConVentas[1]]))
		$diasConVentas[$rowDiasConVentas[2]][$rowDiasConVentas[1]]=0;
	$diasConVentas[$rowDiasConVentas[2]][$rowDiasConVentas[1]]++;
}

// sql para promedio histórico por días
$sqlPromedioDiaSemana = "SELECT datepart(dw, Fecha) as dia, SUM(dbo.MovimientosDetalleFac.Cantidad), IdArticulo, COUNT(IdArticulo) FROM dbo.MovimientosDetalleFac, dbo.MovimientosFac WHERE  dbo.MovimientosDetalleFac.IdArticulo in (2068,2069,2078,2076) AND dbo.MovimientosFac.DocumentoCancelado=0 AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND dbo.MovimientosDetalleFac.Cantidad>0 AND Fecha<'".date("Y-m-d")."' GROUP BY datepart(dw, Fecha), IdArticulo order by dia;";
$stmt = sqlsrv_query( $mssql, $sqlPromedioDiaSemana);


$tablaPromedioDiaSemana="<table class='table'><thead><tr><th></th><th>$articulo[2068]</th><th>$articulo[2069]</th><th>$articulo[2076]</th><th>$articulo[2078]</th></tr></thead><tbody>";
while($rowPromedioDiaSemana = sqlsrv_fetch_array($stmt)){
	//Tengo que sumar en un array para cada tipo de combustible, contar y luego hacer promedio
	//Array ( [0] => Wednesday [] => 1084 [1] => 76340.9271 [2] => 2068 [IdArticulo] => 2068 [3] => 1084 )
	$ventasPorDiaSemana[$rowPromedioDiaSemana[2]][$rowPromedioDiaSemana[0]]=$rowPromedioDiaSemana[1];
	$promedioVentasPorDiaSemana=sprintf("%01.2f",$rowPromedioDiaSemana[1]/$diasConVentas[$rowPromedioDiaSemana[2]][$rowPromedioDiaSemana[0]]);
	//$promedioVentasPorDiaSemana[$rowPromedioDiaSemana[2]][$rowPromedioDiaSemana[0]]=$rowPromedioDiaSemana[1]/$rowPromedioDiaSemana[3];
	//Sumo lo mismo pero sin incluir los valores menores a cierto rango por tipo de combustible (tratando de depurar los días que no hubo combustible
	if(isset($encabezadoFilaDia)&&is_array($encabezadoFilaDia)&&!isset($encabezadoFilaDia[$rowPromedioDiaSemana[0]]))$tablaPromedioDiaSemana.="</tr>";
	if(!isset($encabezadoFilaDia[$rowPromedioDiaSemana[0]])){
		if((date("N")+1==$rowPromedioDiaSemana[0])||(date("N")==0&&$rowPromedioDiaSemana[7]))
			$tablaPromedioDiaSemana.="<tr class='btn-warning'><td>".$date2[$rowPromedioDiaSemana[0]]."</td>";
		else
			$tablaPromedioDiaSemana.="<tr><td>".$date2[$rowPromedioDiaSemana[0]]."</td>";
		$encabezadoFilaDia[$rowPromedioDiaSemana[0]]=$rowPromedioDiaSemana[0];
	}
	$tablaPromedioDiaSemana.="<td>$promedioVentasPorDiaSemana</td>";
	//Array ( [0] => 1 [dia] => 1 [1] => 24693.9700 [] => 554 [2] => 2068 [IdArticulo] => 2068 [3] => 554 )
}
$tablaPromedioDiaSemana.="<tr class='label label-info'><td>General</td><td>".$estadoComb[2068]['promedio']."</td><td>".$estadoComb[2069]['promedio']."</td><td>".$estadoComb[2076]['promedio']."</td><td>".$estadoComb[2078]['promedio']."</td></tr></tbody></table>";


// fecha y hora del ultimo cierre
$sqlUltimoCierre = "select top 1 dbo.CierresTurno.Fecha from dbo.CierresTurno order by IdCierreTurno DESC";
$stmt = sqlsrv_query( $mssql, $sqlUltimoCierre);
$rowUltimoCierre = sqlsrv_fetch_array($stmt);

 // print_r($rowUltimoCierre);
$ultimoCierre=$rowUltimoCierre['Fecha']->format('Y-m-d H:i:s');


// despachos por tanque desde ultimo cierre
$sqlDespachosDesdeUltimoCierre = "select IdTanque, SUM(Cantidad) from dbo.Despachos, dbo.Mangueras WHERE Fecha>=(select top 1 dbo.CierresTurno.Fecha from dbo.CierresTurno order by IdCierreTurno DESC) AND dbo.Despachos.IdManguera=dbo.Mangueras.IdManguera GROUP BY Idtanque order by IdTanque;";
//echo $sqlDespachosDesdeUltimoCierre;
$stmt = sqlsrv_query( $mssql, $sqlDespachosDesdeUltimoCierre);
while($rowDespachosDesdeUltimoCierre = sqlsrv_fetch_array($stmt)){
	$despachosDesdeUltimoCierre[$rowDespachosDesdeUltimoCierre[0]] = $rowDespachosDesdeUltimoCierre[1];
}

// Obtengo descargas de YPF efectuadas en el día
$sqlDespachosCALDEN = "SELECT IdTanque, IdArticulo, Descarga FROM dbo.CierresDetalleTanques WHERE Descarga>0 AND IdCierreTurno IN (SELECT idCierreTurno FROM  dbo.CierresTurno WHERE Fecha>='".date("Y-m-d")."')";
$stmt = sqlsrv_query($mssql, $sqlDespachosCALDEN);
// verifico si hubo descargas en Calden, si las hubo las cotejo contra las OP cargadas en mysql, si no las hubo reviso si hay alguna descarga en mysql en este día para incorporarlas
while($descarga = sqlsrv_fetch_array($stmt)){
	// hay descargas en Calden
	//$estadoComb[$descarga['IdArticulo']]['descarga'] = $descarga['Descarga'];
}
// Obtengo descargas de YPF basadas en mi sistema
$sqlDespachosMAXI = "SELECT idTanque, litrosDespachados FROM `ordenes`, `recepcion` where `ordenes`.idOrden=`recepcion`.idOrden AND `ordenes`.entregado=1 AND fechaEntregada>='$ultimoCierre'";
$resDespachosMAXI = $mysqli->query($sqlDespachosMAXI);
// verifico si hubo descargas en Calden, si las hubo las cotejo contra las OP cargadas en mysql, si no las hubo reviso si hay alguna descarga en mysql en este día para incorporarlas
if($resDespachosMAXI&&$resDespachosMAXI->num_rows>0)
while($descarga = $resDespachosMAXI->fetch_array()){
	// hay descargas en Calden
	if($descarga[0]==1||$descarga[0]==4)$idArt='2068';
	elseif($descarga[0]==2||$descarga[0]==6)$idArt='2069';
	elseif($descarga[0]==3)$idArt='2078';
	else $idArt='2076';
	$estadoComb[$idArt]['descarga'] = ((isset($estadoComb[$idArt]['descarga']))?$estadoComb[$idArt]['descarga']:0)+$descarga['litrosDespachados'];
	$recepcionCombustibleEnTanque[$descarga[0]] = $descarga['litrosDespachados'];
}


// COMBUSTIBLES
$sqlInfoUltimoCierre = "select top 6 dbo.CierresTurno.idCierreTurno as idT, CONVERT(VARCHAR(5), dbo.CierresTurno.Fecha,4) AS Fecha, CONVERT(VARCHAR(8), dbo.CierresTurno.Fecha, 108), Descarga, Medicion, Vendido, StockActual, Capacidad, CAST(round(Medicion/Capacidad*100,2) AS decimal(4, 2)) as Ocupado, (Capacidad-Medicion) as Disponible, dbo.CierresDetalleTanques.IdTanque,  dbo.CierresDetalleTanques.IdArticulo as idArticulo, dbo.CierresTurno.Fecha as fechaCierre from dbo.Tanques, dbo.CierresDetalleTanques, dbo.Articulos, dbo.CierresTurno WHERE dbo.CierresDetalleTanques.IdArticulo=dbo.Articulos.IdArticulo AND dbo.CierresTurno.IdCierreTurno=dbo.CierresDetalleTanques.IdCierreTurno AND dbo.Tanques.idTanque=dbo.CierresDetalleTanques.idTanque order by dbo.CierresDetalleTanques.IdCierreTurno DESC, idArticulo ;";
$stmt = sqlsrv_query( $mssql, $sqlInfoUltimoCierre);
/* Retrieve and display the results of the query. */
while($tanque = sqlsrv_fetch_array($stmt)){
	$idCierre = $tanque[0];
	$dia = $tanque[1];
	$hora = $tanque[2];
	$fechaCierre = date_format($tanque['fechaCierre'], 'Y-m-d H:i:s');
	if(!isset($combustible[$tanque['idArticulo']])){
		$combustible[$tanque['idArticulo']]=$tanque;
		$combustible[$tanque['idArticulo']]['despachos']=(isset($despachosDesdeUltimoCierre[$tanque[10]]))?$despachosDesdeUltimoCierre[$tanque[10]]:0;
	} else {
		$combustible[$tanque['idArticulo']][4]+=$tanque[4];
		$combustible[$tanque['idArticulo']][7]+=$tanque[7];
		$combustible[$tanque['idArticulo']][9]+=$tanque[9];
		$combustible[$tanque['idArticulo']]['despachos']+=(isset($despachosDesdeUltimoCierre[$tanque[10]]))?$despachosDesdeUltimoCierre[$tanque[10]]:0;
	}	
}
$dd=$li='';$a=0;
foreach($combustible as $key => $cb){
	$stockCierre 			= sprintf("%01.2f", $cb[4]);
	//print_r($despachos[$key]);echo"<br>";
	//if(isset($despachos[$cb['idArticulo']])){
	//if(isset($despachos[$cb['idArticulo']])){
	if(isset($despachos[$key])){
		$estadoComb[$key]['vtasDdeCierre']	= sprintf("%01.2f", $despachos[$key]);
		//$estadoComb[$key]['stock']			= sprintf("%01.2f", ($stockCierre+(($cb['Descarga']>1000)?$cb['Descarga']:0)-$cb['despachos'])); 
		$estadoComb[$key]['stock']			= sprintf("%01.0f", ($stockCierre+((isset($estadoComb[$key]['descarga']))?$estadoComb[$key]['descarga']:0)-$cb['despachos'])); //$despachos[$key]));
		$ocupado							= round((($estadoComb[$key]['stock'])/$cb[7])*100,2);
		$porcentajeOcupado					= sprintf("%01.2f", $ocupado);
		$estadoComb[$key]['stockInicial']	= sprintf("%01.2f", ($stockCierre-$cb['despachos']));
		$estadoComb[$key]['disponible']		= sprintf("%01.0f", round($cb[7]-($stockCierre-$cb['despachos']),2));
		$classNoVentas		= "";
	} else {
		$estadoComb[$key]['vtasDdeCierre']	= sprintf("%01.2f", 0);
		$classNoVentas						= " class='noVentas'";
		$estadoComb[$key]['stockInicial']	= sprintf("%01.2f", $stockCierre);
		// $estadoComb[$key]['stock']			= sprintf("%01.2f", $stockCierre+(($cb['Descarga']>1000)?$cb['Descarga']:0));
		$estadoComb[$key]['stock']			= sprintf("%01.0f", ($stockCierre+((isset($estadoComb[$key]['descarga']))?$estadoComb[$key]['descarga']:0))); //$despachos[$key]));
		$ocupado							= round(($estadoComb[$key]['stock']/$cb[7])*100,2);
		$porcentajeOcupado					= sprintf("%01.2f", $ocupado);
		$estadoComb[$key]['disponible'] 	= sprintf("%01.0f", round($cb[7]-($stockCierre),2));
	}
	// modificacion para que el color del porcentaje varíe de acuerdo a cantidad de días de producto
	// echo $key;
	$limiteParaQuiebre	= ($key=='2069'||$key=='2068')?2400:1000;
	$classQuiebre		= ($estadoComb[$key]['stock']<$limiteParaQuiebre)?' class="noVentas"':'';
	$promedio			= ($estadoComb[$key]['stock']<$limiteParaQuiebre)?'----':"(".round($estadoComb[$key]['stock']/$estadoComb[$key]['promedio'],1)." d.)";
	if((($estadoComb[$key]['stock']/$estadoComb[$key]['promedio'])<1)||$estadoComb[$key]['stock']<$limiteParaQuiebre)$classAlerta='btn-danger';
	elseif(($estadoComb[$key]['stock']/$estadoComb[$key]['promedio'])<2)$classAlerta='btn-warning';
	elseif(($estadoComb[$key]['stock']/$estadoComb[$key]['promedio'])<3)$classAlerta='btn-info';
	else $classAlerta='btn-success';
        $a++;
        $li.="<li$classQuiebre".(($a==4)?' style="margin-right:0"':'')."><a><span class='disp'>{$estadoComb[$key]['disponible']} lts</span><span class='label2'>$articulo[$key]<br/>".$estadoComb[$key]['vtasDdeCierre']."</span><span class='count $classAlerta' style='height: $porcentajeOcupado%'>{$estadoComb[$key]['stock']} lts<br/>$promedio</span></a></li>";

}

?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Estado tanques | YPF</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
     body{margin-top:10px;} 
    
    /* TIMELINE CHARTS */
    .timeline { 
      font-size: 0.75em; 
      height: 30%; 
      width:100%;
    }
    .timeline li { 
      position: relative;
      float: left;
      width: 20%; 
      margin: 0 5% 0 0;
      height: 60%;
      background-color: #ddd;
    }
    
    .timeline li.noVentas{
        background-color: #f11;
    }
    .timeline li a { 
      display: block;
      height: 100%;
      text-align:center;
    }
    .timeline li .label2 { 
      display: block; 
      position: absolute; 
      bottom: -23%; 
      left: 0; 
      background: #fff; 
      width: 100%; 
      height: 20%; 
      line-height: 100%; 
      text-align: center;
    }
    .timeline li a .count { 
      display: block; 
      position: absolute; 
      bottom: 0; 
      left: 0; 
      height: 0; 
      width: 100%; 
      text-align: center; 
      overflow: hidden; 
    }
    .timeline li:hover { 
      background: #EFEFEF; 
    }
    .timeline li a:hover .count { 
      background: #2D7BB2; 
    }
    
    
    
    </style>
  </head>

  <body>
        <div id='combustibles'>
                <ul class="timeline">
                       <?php echo $li?>
                </ul>

        </div>
	<?php
// termina
/* Free statement and connection resources. */
if(isset($stmt))
	sqlsrv_free_stmt($stmt);
sqlsrv_close($mssql);
$mysqli->close();
?>
  </body>
</html>