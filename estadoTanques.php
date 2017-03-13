<?php
if(substr($_SERVER['REMOTE_ADDR'], 0, 10)=='192.168.1.'){
    $nivelRequerido=5;
}
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

$titulo="Estado tanques | YPF";

// hacer ventas mensuales calculadas por diferencia aforadores ultimo cierre menos ultimo cierre mes anterior y sumar las ventas diarias.


$sqlUltimoUpdate = $mysqli->query("SELECT fecha, tipo FROM ultimaactualizacion order by id desc limit 1");
fb($sqlUltimoUpdate);
$ultimoUpdate = $sqlUltimoUpdate->fetch_array();
$datetime1 = date_create($ultimoUpdate[0]);
$datetime2 = new DateTime("now");
$interval = date_diff($datetime1, $datetime2);
       
// desarrollo 2.0
class Combustibles{
  public function Combustibles(){
    global $dbg, $mssql;
    $this->combustible = array();
    // obtengo datos de tanques de CaldenOil
    $sqlCombustibles = "SELECT dbo.FamiliasArticulos.Descripcion AS Desc3, IdArticulo, dbo.articulos.Descripcion AS Desc1, ColorARGB    FROM dbo.articulos, dbo.GruposArticulos, dbo.FamiliasArticulos WHERE dbo.articulos.IdGrupoArticulo=dbo.GruposArticulos.IdGrupoArticulo AND dbo.GruposArticulos.IdFamiliaArticulo=dbo.FamiliasArticulos.IdFamiliaArticulo AND Combustible=1 AND dbo.Articulos.activo=1 AND dbo.GruposArticulos.Activo=1 ORDER BY Desc3;";
    $stmt = odbc_exec2($mssql, $sqlCombustibles);
    while($combustible = sqlsrv_fetch_array($stmt)){
      //print_r($combustible);
      $this->combustible[$combustible[1]]['idArticulo']=$combustible[1];
      $this->combustible[$combustible[1]]['descripcion']=$combustible[2];
      $this->combustible[$combustible[1]]['color']=$combustible[3];
      $this->combustible[$combustible[1]]['tipo']=$combustible[0];

    }
  }
}

class Tanques{
  public function Tanques(){
    global $dbg, $mssql;
    $this->tanque = array();
    // obtengo datos de tanques de CaldenOil
    $sqlTanques = "SELECT numero, IdArticulo, Capacidad FROM dbo.tanques ORDER BY numero ASC;";
    $stmt = odbc_exec2($mssql, $sqlTanques);
    while($tanque = sqlsrv_fetch_array($stmt)){
      $this->tanque[$tanque['numero']]['idArticulo']=$tanque['IdArticulo'];
      $this->tanque[$tanque['numero']]['capacidad']=$tanque['Capacidad'];
    }
  }
}

class Picos{
  public function Picos(){
  
    
  }
}
       
$tanques = new Tanques();
$combustibles = new Combustibles();
fb($tanques);
fb($combustibles);
       
       
       
       
       
       
function muestraDetallesTanquesTelemedidos(){
    global $mssql, $articulo, $classArticulo, $mysqli, $CFG;
	$tablaTanques="";
    /*$sqlTanques = "SELECT m1.LastUpdated, m1.IdTanque, m1.IdArticulo, m1.Litros, m1.NivelAgua FROM dbo.tanquesmediciones m1 LEFT JOIN dbo.tanquesmediciones m2  ON (m1.IdTanque = m2.idTanque AND m1.FechaHora < m2.fechahora) WHERE m2.FechaHora IS NULL order by IdTanque asc;";
    $stmt = odbc_exec2($mssql, $sqlTanques);
	if( $stmt === false ){
		 echo "Error in executing query.</br>";
		 die( print_r( sqlsrv_errors(), true));
	}
    while($tanque = sqlsrv_fetch_array($stmt)){
        $telemedido[$tanque[1]] = $tanque;
    }*/
    //$sqlTanques = "select top 6 dbo.CierresTurno.idCierreTurno as idT, Descarga, Capacidad, dbo.CierresDetalleTanques.IdTanque as idTanque,  dbo.CierresDetalleTanques.IdArticulo as idArticulo, dbo.CierresTurno.Fecha as fechaCierre from dbo.Tanques, dbo.CierresDetalleTanques, dbo.Articulos, dbo.CierresTurno WHERE dbo.CierresDetalleTanques.IdArticulo=dbo.Articulos.IdArticulo AND dbo.CierresTurno.IdCierreTurno=dbo.CierresDetalleTanques.IdCierreTurno AND dbo.Tanques.idTanque=dbo.CierresDetalleTanques.idTanque order by dbo.CierresDetalleTanques.IdCierreTurno DESC, idTanque;"; // DEPRECATED
    
    $sqlTanques = "select Capacidad, idArticulo, numero, IdTanque from dbo.tanques order by numero;";
	$stmt = odbc_exec2($mssql, $sqlTanques);
	if( $stmt === false ){
		 echo "Error in executing query.</br>";
		 die( print_r( sqlsrv_errors(), true));
	}
    while($tanque = sqlsrv_fetch_array($stmt)){
      $sqlTelemedicion = "SELECT TOP 1 Litros, NivelAgua, Nivel from dbo.tanquesmediciones WHERE idTanque=$tanque[IdTanque] ORDER BY LastUpdated DESC";
      $stmtTelemedicion = odbc_exec2($mssql, $sqlTelemedicion);
      if( $stmt === false ){
        echo "Error in executing query.</br>";
        die( print_r( sqlsrv_errors(), true));
      }
      $telemedido[$tanque['IdTanque']] = sqlsrv_fetch_array($stmtTelemedicion);
      
      $stockActual = $telemedido[$tanque['IdTanque']]['Litros'];
      
      // arreglo para tomar tanques desde milimetros para super y euro
      // 18/8/2016
      $sqlConversion = "SELECT tq$tanque[IdTanque] FROM `cierres_tanques_equivalencias` WHERE mm=".round($telemedido[$tanque['IdTanque']]['Nivel'],0).";";
      //echo($sqlConversion);
      //fb($telemedido);
      $result = $mysqli->query($sqlConversion);
      $litrosDesdeMM = $result->fetch_assoc();
      $tq = "tq$tanque[IdTanque]";
      
      //var_dump($litrosDesdeMM[$tq]);
      if((in_array($tanque['IdTanque'], $CFG->tanquesATomarMilimetrosDesdeTablas))&&$CFG->tomaLitrosDesdeTabla){
        $telemedido[$tanque['IdTanque']][0] = $litrosDesdeMM[$tq];
        $stockActual   = $litrosDesdeMM[$tq];
      }
      // fin 18/8/2016
      
      $disponible = $tanque['Capacidad'] - $stockActual;
      $porcentajeOcupacion = $stockActual / $tanque['Capacidad'] * 100;
      if(($porcentajeOcupacion)<10){
        $classNoVentas=' class="noVentas"';}
      else {
        $classNoVentas='';}
      if($porcentajeOcupacion<25){$classAlerta='progress-bar-danger';}
      elseif($porcentajeOcupacion<50){$classAlerta='progress-bar-warning';}
      elseif($porcentajeOcupacion<75){$classAlerta='progress-bar-info';}
      else {$classAlerta='progress-bar-success';}
      
      

      $tablaTanques.="<tr$classNoVentas>"
        . "<td class='alert alert-{$classArticulo[$tanque['idArticulo']]}'>".$articulo[$tanque['idArticulo']]." <span class='badge '>$tanque[IdTanque]</span></td>"
      ."<td>".sprintf('%01.2f',$stockActual)."</td>"
      ."<td colspan='2'><div class='progress' style='margin-bottom: 0;'><div class='progress-bar $classAlerta  progress-bar-striped active' role='progressbar' aria-valuenow='$porcentajeOcupacion' aria-valuemin='0' aria-valuemax='100' style='width: $porcentajeOcupacion%;'>".round($porcentajeOcupacion,0)."%</div>".round($disponible,0)."</div></td>"
      //. "<td><span class='$classAlerta'>".round($porcentajeOcupacion,0)."%</span></td>"
      . "</tr>";
      if(!isset($combustible[$tanque['idArticulo']])){
        $combustible[$tanque['idArticulo']]=$tanque;
        $combustible[$tanque['idArticulo']]['Medicion']=$stockActual;
        $combustible[$tanque['idArticulo']]['Capacidad']=$tanque['Capacidad'];
        $combustible[$tanque['idArticulo']]['Disponible']=$disponible;
      } else {
        $combustible[$tanque['idArticulo']]['Medicion']+=$stockActual;
        $combustible[$tanque['idArticulo']]['Capacidad']+=$tanque['Capacidad'];
        $combustible[$tanque['idArticulo']]['Disponible']+=$disponible;
      }
    }
	//<span class='sh2'>Turno $dia a las $hora</span>
	$detalleTanques = "<table class='table'>
			<thead><tr><th></th><th>Actual</th><th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;                      &nbsp;</th><th>Vacío</th></tr></thead>
			<tbody>
				$tablaTanques
			</tbody>	
		</table>";
	echo trim($detalleTanques);
    //echo microtime()-$a;
}

function muestraDetalleTanques(){
	global $mssql, $articulo, $classArticulo, $despachosDesdeUltimoCierre, $recepcionCombustibleEnTanque;
	$tablaTanques="";
	$sqlTanques = "select top 6 dbo.CierresTurno.idCierreTurno as idT, CONVERT(VARCHAR(5), dbo.CierresTurno.Fecha,4) AS Fecha, CONVERT(VARCHAR(8), dbo.CierresTurno.Fecha, 108), Descarga, Medicion, Vendido, StockActual, Capacidad, CAST(round(Medicion/Capacidad*100,2) AS decimal(4, 2)) as Ocupado, (Capacidad-Medicion) as Disponible, dbo.CierresDetalleTanques.IdTanque,  dbo.CierresDetalleTanques.IdArticulo as idArticulo, dbo.CierresTurno.Fecha as fechaCierre from dbo.Tanques, dbo.CierresDetalleTanques, dbo.Articulos, dbo.CierresTurno WHERE dbo.CierresDetalleTanques.IdArticulo=dbo.Articulos.IdArticulo AND dbo.CierresTurno.IdCierreTurno=dbo.CierresDetalleTanques.IdCierreTurno AND dbo.Tanques.idTanque=dbo.CierresDetalleTanques.idTanque order by dbo.CierresDetalleTanques.IdCierreTurno DESC, idTanque;";
	$stmt = odbc_exec2($mssql, $sqlTanques);
	if( $stmt === false ){
		 echo "Error in executing query.</br>";
		 die( print_r( sqlsrv_errors(), true));
	}
	while($tanque = sqlsrv_fetch_array($stmt)){
          //print_r($tanque);echo"<br>";
          if($tanque[8]<10){$classNoVentas=' class="noVentas"';}
          else {$classNoVentas='';}
          /*if($tanque[8]<25)$classAlerta='btn-danger badge';
          elseif($tanque[8]<50)$classAlerta='btn-warning badge';
          elseif($tanque[8]<75)$classAlerta='btn-info badge';
          else $classAlerta='';*/
          $classAlerta='';
          $stockActual = $tanque[4]+((isset($recepcionCombustibleEnTanque[$tanque[10]]))?$recepcionCombustibleEnTanque[$tanque[10]]:0)-((isset($despachosDesdeUltimoCierre[$tanque['IdTanque']]))?$despachosDesdeUltimoCierre[$tanque['IdTanque']]:0);
          
          $porcentajeOcupacion = $stockActual / $tanque[7] * 100;
          
          //$tablaTanques.="<tr><td class='label-{$classArticulo[$tanque[11]]}'>".$articulo[$tanque[11]]."  <span class='badge'>$tanque[10]</span></td><td title='$tanque[4]'>".sprintf('%01.2f',$stockActual)."</td><td>".round($tanque[7]/1)."</td><td><span class='$classAlerta'>".sprintf("%01.2f", $porcentajeOcupacion)."%</span></td><td>$tanque[9]</td></tr>";
          $tablaTanques.="<tr$classNoVentas>"
          . "<td class='alert alert-{$classArticulo[$tanque[11]]}'>".$articulo[$tanque[11]]." $tanque[8] <span class='badge '>$tanque[10]</span></td>"
                  . "<td title='$tanque[4]'>".sprintf('%01.2f',$stockActual)."</td>"
                  . "<td>".round($tanque[7]/1)."</td>"
                  . "<td><span class='$classAlerta'>".round($porcentajeOcupacion,0)."%</span></td>"
                  . "<td>".round($tanque[7]-$stockActual,0)."</td>"
                  . "</tr>";
          
          $idCierre = $tanque[0];
          $dia = $tanque[1];
          $hora = $tanque[2];
          $fechaCierre = date_format($tanque['fechaCierre'], 'Y-m-d H:i:s');
          if(!isset($combustible[$tanque['idArticulo']]))
            $combustible[$tanque['idArticulo']]=$tanque;
          else {
            $combustible[$tanque['idArticulo']][4]+=$tanque[4];
            $combustible[$tanque['idArticulo']][7]+=$tanque[7];
            $combustible[$tanque['idArticulo']][9]+=$tanque[9];
          }
	}
	//<span class='sh2'>Turno $dia a las $hora</span>
	$detalleTanques = "<table class='table'>
			<thead><tr><th></th><th>Actual</th><th>Cap</th><th>Lleno</th><th>Vacío</th></tr></thead>
			<tbody>
				$tablaTanques
			</tbody>	
		</table>";
	echo trim($detalleTanques);
}


function recepcionMensual(){
    global $mysqli;
    global $articulo, $mes;
    //$sql="SELECT sum( ns ) , sum( np ) , sum( ud ) , sum( ed ) FROM `ventasdiarias` WHERE YEAR( fecha ) = YEAR( CURDATE( ) ) AND MONTH( fecha ) = MONTH( CURDATE( ) ) ";
    
    $sql1 = "SELECT month(fecha) as mes,  sum( tq3 ) as l2078, sum( tq5 ) as l2076, sum( tq2 + tq6 ) as l2069, sum( tq1 + tq4 ) as l2068, count(remito2) as cuantoscamiones FROM `recepcioncombustibles` WHERE YEAR( fecha ) = YEAR( CURDATE( ) ) OR (YEAR(fecha)=YEAR(CURDATE())-1 AND MONTH(fecha)>=MONTH(CURDATE())-1) group by year(fecha),month(fecha)";
    

    //$sqlVentasMensuales = "SELECT month(fecha) as mes,  sum( ns ) as l2078, sum( np ) as l2076, sum( ud ) as l2069, sum( ed ) as l2068 FROM `ventasdiarias` WHERE YEAR( fecha ) = YEAR( CURDATE( ) ) OR YEAR(fecha)=YEAR(CURDATE())-1 group by year(fecha),month(fecha)";
    
    $result = $mysqli->query($sql1);
    if($result&&$result->num_rows>0 &&!$_SESSION['esMovil']){
        $tablaPromedioDiaSemana="<table class='table'><thead><tr><th></th><th></th><th>$articulo[2068]</th><th>$articulo[2069]</th><th>$articulo[2076]</th><th>$articulo[2078]</th></tr></thead><tbody>";
        while($rowPromedioDiaSemana = $result->fetch_assoc()){
            //print_r($rowPromedioDiaSemana);
            //Tengo que sumar en un array para cada tipo de combustible, contar y luego hacer promedio
            //Array ( [0] => Wednesday [] => 1084 [1] => 76340.9271 [2] => 2068 [IdArticulo] => 2068 [3] => 1084 )
            $tablaPromedioDiaSemana.="<tr class='".
                    (((date("n")==$rowPromedioDiaSemana['mes'])||(date("N")==0))?'label-warning':'').
                    "'><td>".$mes[$rowPromedioDiaSemana['mes']]
                    ."</td><td>".$rowPromedioDiaSemana['cuantoscamiones']
                    ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2068'])
                    ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2069'])
                    ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2076'])
                    ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2078'])
                    ."</td></tr>";
        }
        //$tablaPromedioDiaSemana.="<tr class='label-info'><td>General</td><td>".sprintf("%01.2f",$promedioHistorico['l2068'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2069'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2076'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2078'])."</td></tr></tbody></table>";
        $tablaPromedioDiaSemana.="</tbody></table>";
    }
	echo trim($tablaPromedioDiaSemana);
}
if(isset($_GET['m'])){
	switch($_GET['m']){
		case 'detalleTanques':
			muestraDetalleTanques();
			break;
	}
	die;	
}



// Despachos extraidos de la tabla de despachos, contiene todo lo que salió de los surtidores y no solo lo facturado
if(date('H')>22&&false){
  $sqlDespachos = "select IdArticulo, SUM(Cantidad), count(IdDespacho) from dbo.Despachos WHERE Fecha>='".date("Y-m-d 22:00:00")."' GROUP BY IdArticulo;";
  $sqlDespachos = "select IdArticulo, SUM(Cantidad), count(IdDespacho) from dbo.Despachos WHERE Fecha>='".date("Y-d-m 22:00:00")."' GROUP BY IdArticulo;";
} else {
  $sqlDespachos = "select IdArticulo, SUM(Cantidad), count(IdDespacho) from dbo.Despachos WHERE Fecha>='".date("Y-m-d")."' GROUP BY IdArticulo;";
  $sqlDespachos = "select IdArticulo, SUM(Cantidad), count(IdDespacho) from dbo.Despachos WHERE Fecha>='".date("Y-d-m")."' GROUP BY IdArticulo;";
}

$stmt = odbc_exec2( $mssql, $sqlDespachos);
while($row = sqlsrv_fetch_array($stmt)){
  $despachos[$row[0]]=$row[1];
  $despachos['d'.$row[0]]=$row[2];
}


// Promedio historico
// metodo nuevo (2014-11-17), lo calcula en base a la información de los cierres de turno

$sqlPromedioHistorico = "SELECT avg(ns) as l2078, avg(np) as l2076, avg(ud) as l2069, avg(ed) as l2068 FROM `ventasdiarias` WHERE fecha>='{$CFG->fechaDesdeDondeTomoPromedioHistoricos}' AND month(fecha)=month(curdate()) ";
fb($sqlPromedioHistorico);
$resultPromedioHistorico = $mysqli->query($sqlPromedioHistorico);
$promedioHistorico = $resultPromedioHistorico->fetch_assoc();

// Promedio diario
// metodo nuevo (2014-11-17), lo calcula en base a la información de los cierres de turno
$sqlPromedioDiario = "SELECT avg(ns) as l2078 , avg(np) as l2076 , avg(ud) as l2069, avg(ed) as l2068, diaSemana FROM `ventasdiarias`  WHERE fecha>='{$CFG->fechaDesdeDondeTomoPromedioHistoricos}' AND month(fecha)=month(curdate()) GROUP BY diaSemana";
$result = $mysqli->query($sqlPromedioDiario);
if($result && $result->num_rows>0 && !$_SESSION['esMovil']){
    $tablaPromedioDiaSemana="<table class='table'><thead><tr><th></th><th>$articulo[2068]</th><th>$articulo[2069]</th><th>$articulo[2076]</th><th>$articulo[2078]</th></tr></thead><tbody>";
    while($rowPromedioDiaSemana = $result->fetch_assoc()){
        //print_r($rowPromedioDiaSemana);
        //Tengo que sumar en un array para cada tipo de combustible, contar y luego hacer promedio
        //Array ( [0] => Wednesday [] => 1084 [1] => 76340.9271 [2] => 2068 [IdArticulo] => 2068 [3] => 1084 )
        $tablaPromedioDiaSemana.="<tr class='".
                (((date("N")+1==$rowPromedioDiaSemana['diaSemana'])||(date("N")==0))?'label-warning':'').
                "'><td>".$date2[$rowPromedioDiaSemana['diaSemana']]
                ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2068'])
                ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2069'])
                ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2076'])
                ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2078'])
                ."</td></tr>";
    }
    $tablaPromedioDiaSemana.="<tr class='label-info'><td>General</td><td>".sprintf("%01.2f",$promedioHistorico['l2068'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2069'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2076'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2078'])."</td></tr></tbody></table>";
}
    

/*
// metodo anterior, lo calculaba desde el sql server apoyado en la tabla promedios de mysql
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
	$stmt = odbc_exec2( $mssql, $sqlPromedioHistorico);
	while($rowPromedioHistorico = sqlsrv_fetch_array($stmt)){
		$estadoComb[$rowPromedioHistorico[1]]['promedio']=sprintf("%01.2f",$rowPromedioHistorico[3]);
		$mysqli->query("INSERT INTO promedios SET fecha='$fechaParaTraerPromedio', promedio='$rowPromedioHistorico[3]', idArticulo='$rowPromedioHistorico[1]'");
	}
}

// sql para cantidad de dias con ventas para cada producto
$sqlDiasConVentas = "SELECT distinct(dateadd(dd,0, datediff(dd,0,Fecha))) as fecha, datepart(dw, Fecha) as dia, IdArticulo FROM dbo.MovimientosDetalleFac, dbo.MovimientosFac WHERE dbo.MovimientosDetalleFac.IdArticulo in (2068,2069,2078,2076) AND dbo.MovimientosFac.DocumentoCancelado=0 AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND getdate()-fecha>30 AND fecha<'".date("Y-m-d")."' GROUP BY IdArticulo, fecha order by idArticulo, dia, fecha";
$stmt = odbc_exec2( $mssql, $sqlDiasConVentas);
while($rowDiasConVentas = sqlsrv_fetch_array($stmt)){
	//Array ( [0] => DateTime Object ( [date] => 2011-10-16 00:00:00 [timezone_type] => 3 [timezone] => America/Argentina/Buenos_Aires ) [fecha] => DateTime Object ( [date] => 2011-10-16 00:00:00 [timezone_type] => 3 [timezone] => America/Argentina/Buenos_Aires ) [1] => 1 [dia] => 1 [2] => 2068 [IdArticulo] => 2068 ) 
	if(!isset($diasConVentas[$rowDiasConVentas[2]][$rowDiasConVentas[1]]))
		$diasConVentas[$rowDiasConVentas[2]][$rowDiasConVentas[1]]=0;
	$diasConVentas[$rowDiasConVentas[2]][$rowDiasConVentas[1]]++;
}

// sql para promedio histórico por días
$sqlPromedioDiaSemana = "SELECT datepart(dw, Fecha) as dia, SUM(dbo.MovimientosDetalleFac.Cantidad), IdArticulo, COUNT(IdArticulo) FROM dbo.MovimientosDetalleFac, dbo.MovimientosFac WHERE  dbo.MovimientosDetalleFac.IdArticulo in (2068,2069,2078,2076) AND dbo.MovimientosFac.DocumentoCancelado=0 AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND dbo.MovimientosDetalleFac.Cantidad>0 AND Fecha<'".date("Y-m-d")."' GROUP BY datepart(dw, Fecha), IdArticulo order by dia;";
$stmt = odbc_exec2( $mssql, $sqlPromedioDiaSemana);


if(!$_SESSION['esMovil']){
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
                $tablaPromedioDiaSemana.="<tr class='label-warning'><td>".$date2[$rowPromedioDiaSemana[0]]."</td>";
            else
                $tablaPromedioDiaSemana.="<tr><td>".$date2[$rowPromedioDiaSemana[0]]."</td>";
            $encabezadoFilaDia[$rowPromedioDiaSemana[0]]=$rowPromedioDiaSemana[0];
        }
        $tablaPromedioDiaSemana.="<td>$promedioVentasPorDiaSemana</td>";
        //Array ( [0] => 1 [dia] => 1 [1] => 24693.9700 [] => 554 [2] => 2068 [IdArticulo] => 2068 [3] => 554 )
    }
    $tablaPromedioDiaSemana.="<tr class='label-info'><td>General</td><td>".$estadoComb[2068]['promedio']."</td><td>".$estadoComb[2069]['promedio']."</td><td>".$estadoComb[2076]['promedio']."</td><td>".$estadoComb[2078]['promedio']."</td></tr></tbody></table>";
}
*/
// fecha y hora del ultimo cierre
$sqlUltimoCierre = "select top 1 dbo.CierresTurno.Fecha from dbo.CierresTurno order by IdCierreTurno DESC";
$stmt = odbc_exec2($mssql, $sqlUltimoCierre, __LINE__, __FILE__);
$rowUltimoCierre = sqlsrv_fetch_array($stmt);

$ultimoCierre=$rowUltimoCierre['Fecha']->format('Y-m-d H:i:s');


// despachos por tanque desde ultimo cierre
$sqlDespachosDesdeUltimoCierre = "select IdTanque, SUM(Cantidad) from dbo.Despachos, dbo.Mangueras WHERE Fecha>=(select top 1 dbo.CierresTurno.Fecha from dbo.CierresTurno order by IdCierreTurno DESC) AND dbo.Despachos.IdManguera=dbo.Mangueras.IdManguera GROUP BY Idtanque order by IdTanque;";
//echo $sqlDespachosDesdeUltimoCierre;
$stmt = odbc_exec2( $mssql, $sqlDespachosDesdeUltimoCierre, __LINE__, __FILE__);
while($rowDespachosDesdeUltimoCierre = sqlsrv_fetch_array($stmt)){
	$despachosDesdeUltimoCierre[$rowDespachosDesdeUltimoCierre[0]] = $rowDespachosDesdeUltimoCierre[1];
}

// Obtengo descargas de YPF efectuadas en el día
$sqlDespachosCALDEN = "SELECT IdTanque, IdArticulo, Descarga FROM dbo.CierresDetalleTanques WHERE Descarga>0 AND IdCierreTurno IN (SELECT idCierreTurno FROM  dbo.CierresTurno WHERE Fecha>='".date("Y-m-d")."')";
$sqlDespachosCALDEN = "SELECT IdTanque, IdArticulo, Descarga FROM dbo.CierresDetalleTanques WHERE Descarga>0 AND IdCierreTurno IN (SELECT idCierreTurno FROM  dbo.CierresTurno WHERE Fecha>='".date("Y-d-m")."')";
$stmt = odbc_exec2($mssql, $sqlDespachosCALDEN, __LINE__, __FILE__);
// verifico si hubo descargas en Calden, si las hubo las cotejo contra las OP cargadas en mysql, si no las hubo reviso si hay alguna descarga en mysql en este día para incorporarlas
while($descarga = sqlsrv_fetch_array($stmt)){
	// hay descargas en Calden
	//$estadoComb[$descarga['IdArticulo']]['descarga'] = $descarga['Descarga'];
}
// Obtengo descargas de YPF basadas en mi sistema
$sqlDespachosMAXI = "SELECT idTanque, litrosDespachados FROM `ordenes`, `recepcion` where `ordenes`.idOrden=`recepcion`.idOrden AND `ordenes`.entregado=1 AND fechaEntregada>='$ultimoCierre'";
$resDespachosMAXI = $mysqli->query($sqlDespachosMAXI);
// verifico si hubo descargas en Calden, si las hubo las cotejo contra las OP cargadas en mysql, si no las hubo reviso si hay alguna descarga en mysql en este día para incorporarlas
if($resDespachosMAXI&&$resDespachosMAXI->num_rows>0){
while($descarga = $resDespachosMAXI->fetch_array()){
	// hay descargas en Calden
	if($descarga[0]==1||$descarga[0]==4){$idArt='2068';}
	elseif($descarga[0]==2||$descarga[0]==6){$idArt='2069';}
	elseif($descarga[0]==3){$idArt='2078';}
	else {$idArt='2076';}
	$estadoComb[$idArt]['descarga'] = ((isset($estadoComb[$idArt]['descarga']))?$estadoComb[$idArt]['descarga']:0)+$descarga['litrosDespachados'];
	$recepcionCombustibleEnTanque[$descarga[0]] = $descarga['litrosDespachados'];
}}

// COMBUSTIBLES
$sqlTanques = "select Capacidad, IdArticulo, numero, IdTanque from dbo.tanques order by numero;";
$stmt = odbc_exec2($mssql, $sqlTanques);
if( $stmt === false ){
          echo "Error in executing query.</br>";
          die( print_r( sqlsrv_errors(), true));
}
while($tanque = sqlsrv_fetch_array($stmt)){
  $sqlTelemedicion = "SELECT TOP 1 Litros, NivelAgua, Nivel from dbo.tanquesmediciones WHERE idTanque=$tanque[IdTanque] ORDER BY LastUpdated DESC";
  $stmtTelemedicion = odbc_exec2($mssql, $sqlTelemedicion);
  if( $stmt === false ){
        echo "Error in executing query.</br>";
        die( print_r( sqlsrv_errors(), true));
  }
  $telemedido[$tanque['IdTanque']] = sqlsrv_fetch_array($stmtTelemedicion);
  $stockActual = $telemedido[$tanque['IdTanque']]['Litros'];
  if(in_array($tanque['IdTanque'], $CFG->tanquesATomarMilimetrosDesdeTablas)){
    $sqlConversion = "SELECT tq$tanque[IdTanque] FROM `cierres_tanques_equivalencias` WHERE mm=".round($telemedido[$tanque['IdTanque']]['Nivel'],0).";";
    //fb($sqlConversion);
    //fb($telemedido);
    $result = $mysqli->query($sqlConversion);
    $litrosDesdeMM = $result->fetch_assoc();
    $tq = "tq$tanque[IdTanque]";
    //var_dump($litrosDesdeMM[$tq]);
    if(in_array($tanque['IdTanque'], $CFG->tanquesATomarMilimetrosDesdeTablas)&&$CFG->tomaLitrosDesdeTabla){
      $stockActual   = $litrosDesdeMM[$tq];
      $telemedido[$tanque['IdTanque']]['Litros'] = $litrosDesdeMM[$tq];
    }
  }
  // var_dump($telemedido);
  //fb($telemedido);
  
    $telemedidoXarticulo[$tanque['IdArticulo']] = ((isset($telemedidoXarticulo[$tanque['IdArticulo']]))?$telemedidoXarticulo[$tanque['IdArticulo']] + $telemedido[$tanque['IdTanque']]['Litros']:$telemedido[$tanque['IdTanque']]['Litros']);
}

/*
$sqlTanques = "SELECT m1.LastUpdated, m1.IdTanque, m1.IdArticulo, m1.Litros, m1.NivelAgua FROM dbo.tanquesmediciones m1 LEFT JOIN dbo.tanquesmediciones m2  ON (m1.IdTanque = m2.idTanque AND m1.FechaHora < m2.fechahora) WHERE m2.FechaHora IS NULL order by IdTanque asc;";
 $stmt = odbc_exec2($mssql, $sqlTanques);
if( $stmt === false ){
     echo "Error in executing query.</br>";
     die( print_r( sqlsrv_errors(), true));
}
while($tanque = sqlsrv_fetch_array($stmt)){
    $telemedido[$tanque[1]] = $tanque;
    $telemedidoXarticulo[$tanque[2]] = ((isset($telemedidoXarticulo[$tanque[2]]))?$telemedidoXarticulo[$tanque[2]] + $tanque[3]:$tanque[3]);
} 
// DEPRECATED Metodo viejo, tardaba 10 segundo el query
 * 
 *  */


$sqlInfoUltimoCierre = "select top 6 dbo.CierresTurno.idCierreTurno as idT, CONVERT(VARCHAR(5), dbo.CierresTurno.Fecha,4) AS Fecha, CONVERT(VARCHAR(8), dbo.CierresTurno.Fecha, 108) as Hora, Descarga, Medicion, Vendido, StockActual, Capacidad, CAST(round(Medicion/Capacidad*100,2) AS decimal(4, 2)) as Ocupado, (Capacidad-Medicion) as Disponible, dbo.CierresDetalleTanques.IdTanque,  dbo.CierresDetalleTanques.IdArticulo as idArticulo, dbo.CierresTurno.Fecha as fechaCierre from dbo.Tanques, dbo.CierresDetalleTanques, dbo.Articulos, dbo.CierresTurno WHERE dbo.CierresDetalleTanques.IdArticulo=dbo.Articulos.IdArticulo AND dbo.CierresTurno.IdCierreTurno=dbo.CierresDetalleTanques.IdCierreTurno AND dbo.Tanques.idTanque=dbo.CierresDetalleTanques.idTanque order by dbo.CierresDetalleTanques.IdCierreTurno DESC, idArticulo ;";
$stmt = odbc_exec2( $mssql, $sqlInfoUltimoCierre);
/* Retrieve and display the results of the query. */
$tabla=$tabla2="";
while($tanque = sqlsrv_fetch_array($stmt)){
//    print_r($tanque);echo"<br><br>";
  $tabla.="<tr><th>".$articulo[$tanque['idArticulo']]."</th><td>[$tanque[IdTanque]]</td><td>$tanque[Medicion]</td><td>$tanque[Capacidad]</td><td>".sprintf("%01.2f", $tanque['Ocupado'])."%</td><td>$tanque[Disponible]</td></tr>";
  $idCierre = $tanque['idT'];
  $dia = $tanque['Fecha'];
  $hora = $tanque['Hora'];
  //$fechaCierre = date_format($tanque['Fecha'], 'Y-m-d H:i:s');
  $fechaCierre = $tanque['Fecha'];
  if(!isset($combustible[$tanque['idArticulo']])){
    $combustible[$tanque['idArticulo']]=$tanque;
    $combustible[$tanque['idArticulo']]['despachos']=(isset($despachosDesdeUltimoCierre[$tanque['IdTanque']]))?$despachosDesdeUltimoCierre[$tanque['IdTanque']]:0;
  } else {
    $combustible[$tanque['idArticulo']]['Medicion']+=$tanque['Medicion'];
    $combustible[$tanque['idArticulo']]['Capacidad']+=$tanque['Capacidad'];
    $combustible[$tanque['idArticulo']]['Disponible']+=$tanque['Disponible'];
    $combustible[$tanque['idArticulo']]['despachos']+=(isset($despachosDesdeUltimoCierre[$tanque['IdTanque']]))?$despachosDesdeUltimoCierre[$tanque['IdTanque']]:0;
  }	
}

foreach($combustible as $key => $cb){
  //print_r($key);
  $stockCierre = sprintf("%01.2f", $cb['Medicion']);
  if(isset($despachos[$key])){
    $estadoComb[$key]['vtasDdeCierre']	= sprintf("%01.2f", $despachos[$key]);
    $estadoComb[$key]['stock']		= sprintf("%01.2f", $telemedidoXarticulo[$key]); //$despachos[$key]));
    $ocupado				= round((($estadoComb[$key]['stock'])/$cb['Capacidad'])*100,2);
    $porcentajeOcupado			= sprintf("%01.2f", $ocupado);
    $porcentajeOcupado			= round($ocupado,0);
    $estadoComb[$key]['stockInicial']	= sprintf("%01.2f", ($stockCierre-$cb['despachos']));
    $estadoComb[$key]['disponible']	= sprintf("%01.0f", round($cb['Capacidad']-$estadoComb[$key]['stock'],2));
    //$estadoComb[$key]['disponible'] 	= round($cb['Capacidad']-($stockCierre),2);
    $classNoVentas		= "";
    $despachoPromedio                     = sprintf("%01.1f", ($estadoComb[$key]['vtasDdeCierre']/$despachos['d'.$key]));
  } else {
    $estadoComb[$key]['vtasDdeCierre']	= sprintf("%01.2f", 0);
    $classNoVentas			= " class='noVentas'";
    $estadoComb[$key]['stockInicial']	= sprintf("%01.2f", $stockCierre);
    $estadoComb[$key]['stock']		= sprintf("%01.2f", $telemedidoXarticulo[$key]); //$despachos[$key]));
    $ocupado				= round(($estadoComb[$key]['stock']/$cb['Capacidad'])*100,2);
    $porcentajeOcupado			= round($ocupado,0);
    $estadoComb[$key]['disponible'] 	= sprintf("%01.0f", round($cb['Capacidad']-$estadoComb[$key]['stock'],2));
    $estadoComb[$key]['disponible'] 	= round($cb['Capacidad']-$stockCierre,2);
    $despachoPromedio                     = "0";
  }
  // modificacion para que el color del porcentaje varíe de acuerdo a cantidad de días de producto
  // echo $key;
  $limiteParaQuiebre	= ($key=='2069'||$key=='2068')?2400:1000;
  $classQuiebre		= ($estadoComb[$key]['stock']<$limiteParaQuiebre)?' class="noVentas"':'';
  $promedio			= ($estadoComb[$key]['stock']<$limiteParaQuiebre)?'----':"(".round(($estadoComb[$key]['stock']-$limiteParaQuiebre)/$promedioHistorico['l'.$key],1)." d.)";
  if(((($estadoComb[$key]['stock']-$limiteParaQuiebre)/$promedioHistorico['l'.$key])<1)||$estadoComb[$key]['stock']<$limiteParaQuiebre){$classAlerta='btn-danger btn btn-xs';}
  elseif((($estadoComb[$key]['stock']-$limiteParaQuiebre)/$promedioHistorico['l'.$key])<2){$classAlerta='btn-warning btn btn-xs';}
  elseif((($estadoComb[$key]['stock']-$limiteParaQuiebre)/$promedioHistorico['l'.$key])<3){$classAlerta='btn-info btn btn-xs';}
  else {$classAlerta='btn-success btn btn-xs';}
  /*
  if($ocupado<25)$classAlerta='btn-danger btn';
  elseif($ocupado<50)$classAlerta='btn-warning btn';
  elseif($ocupado<75)$classAlerta='btn-info btn';
  else $classAlerta='btn-success btn';
  */
  $d = (isset($despachos['d'.$key]))?$despachos['d'.$key]:0;
  $tabla2.="<tr$classQuiebre><th width='18%'>".$articulo[$cb['idArticulo']]."<br/>$cb[Capacidad] lts</th><td$classNoVentas>".$estadoComb[$key]['vtasDdeCierre']."<br>$d desp / $despachoPromedio lt</td><td>".$estadoComb[$key]['stock']."<br/>$promedio</td><td><span class='$classAlerta'>$porcentajeOcupado%</span></td><td>".$estadoComb[$key]['disponible']."</td></tr>";//<td>$stockCierre</td>
  // corregir que no duplique después de las 22 hs
  
  @$totalDiario['vtasDdeCierre']+=$estadoComb[$key]['vtasDdeCierre'];
  @$totalDiario['d']+=$d;
}
//print_r($estadoComb);
@$mixInfinia = 100*$estadoComb[2076]['vtasDdeCierre']/($estadoComb[2076]['vtasDdeCierre']+$estadoComb[2078]['vtasDdeCierre']);
@$mixEuro = 100*$estadoComb[2068]['vtasDdeCierre']/($estadoComb[2068]['vtasDdeCierre']+$estadoComb[2069]['vtasDdeCierre']);

// para corregir error después de las 0 de cada día mientras no haya despachos.
$totalDiario['d'] = ($totalDiario['d']<>0)?$totalDiario['d']:.0001;

$tabla2.="<tr class='well'><th width='18%'>Total</th><td>".$totalDiario['vtasDdeCierre']."<br>$totalDiario[d] desp / ".round($totalDiario['vtasDdeCierre']/$totalDiario['d'],2)." lts</td><td colspan=2>Estimado ".round($totalDiario['vtasDdeCierre']/(date("H")-6)*16)." lts<br/>Mix ".round($mixInfinia,1)."% | ".round($mixEuro,1)."%</td><td></td></tr>"; // basado en cálculos ultra precisos


// verifico que el historico no esté en sesion
if(!isset($_SESSION['despachosHorariosHistoricos'])){
  // saca promedio general desde el día 0 hasta hoy
  // select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha))/DATEDIFF(day,'2011-10-12',getdate()) from dbo.Despachos group by datepart(HOUR, Fecha) order by hora; 
  $sqlDespachosHorariosHistoricos = "select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha))/DATEDIFF(day,'2011-10-12',getdate()) as q from dbo.Despachos group by datepart(HOUR, Fecha) order by hora;"; 
  $stmt = odbc_exec2($mssql, $sqlDespachosHorariosHistoricos);
  if( $stmt === false ){
    echo "Error in executing query. $sqlDespachosHorariosHistoricos</br>";
    die( print_r( sqlsrv_errors(), true));
  }
  $despachosHorariosHistoricos = array();
  while($row = sqlsrv_fetch_array($stmt)){
    $despachosHorariosHistoricos[$row['hora']] = $row['q'];
  }
  $_SESSION['despachosHorariosHistoricos']=$despachosHorariosHistoricos;
  $sqlLitrosHorariosHistoricos = "select datepart(HOUR, Fecha) as hora, sum(Cantidad)/DATEDIFF(day,'2011-10-12',getdate()) as q from dbo.Despachos group by datepart(HOUR, Fecha) order by hora;";
  $stmt = odbc_exec2($mssql, $sqlLitrosHorariosHistoricos);
  if( $stmt === false ){
    echo "Error in executing query. $sqlLitrosHorariosHistoricos</br>";
    die( print_r( sqlsrv_errors(), true));
  }
  $litrosHorariosHistoricos = array();
  while($row = sqlsrv_fetch_array($stmt)){
    $litrosHorariosHistoricos[$row['hora']] = round($row['q'],1);
  }
  $_SESSION['litrosHorariosHistoricos']=$litrosHorariosHistoricos;
}

if(!isset($_SESSION['despachosHorariosHistoricosDiarios'][date('w')])){
  // saca promedio general desde el día 0 hasta hoy
  // select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha))/DATEDIFF(day,'2011-10-12',getdate()) from dbo.Despachos group by datepart(HOUR, Fecha) order by hora; 
  $sqlDespachosHorariosHistoricos = "select datepart(HOUR, Fecha) as hora, (count(datepart(HOUR, Fecha))/DATEDIFF(day,'2011-10-12',getdate()))*7 as q from dbo.Despachos WHERE DATEPART(dw,Fecha)=".(date('w')+1)." group by datepart(HOUR, Fecha) order by hora;"; 
  $stmt = odbc_exec2($mssql, $sqlDespachosHorariosHistoricos);
  if( $stmt === false ){
    echo "Error in executing query. $sqlDespachosHorariosHistoricos</br>";
    die( print_r( sqlsrv_errors(), true));
  }
  $despachosHorariosHistoricos = array();
  while($row = sqlsrv_fetch_array($stmt)){
    $despachosHorariosHistoricos[date('w')][$row['hora']] = $row['q'];
  }
  $_SESSION['despachosHorariosHistoricosDiarios']=$despachosHorariosHistoricos;
  $sqlLitrosHorariosHistoricos = "select datepart(HOUR, Fecha) as hora, sum(Cantidad)/DATEDIFF(day,'2011-10-12',getdate())*7 as q from dbo.Despachos WHERE DATEPART(dw,Fecha)=".(date('w')+1)." group by datepart(HOUR, Fecha) order by hora;";
  $stmt = odbc_exec2($mssql, $sqlLitrosHorariosHistoricos);
  if( $stmt === false ){
    echo "Error in executing query. $sqlLitrosHorariosHistoricos</br>";
    die( print_r( sqlsrv_errors(), true));
  }
  $litrosHorariosHistoricos = array();
  while($row = sqlsrv_fetch_array($stmt)){
    $litrosHorariosHistoricos[date('w')][$row['hora']] = round($row['q'],1);
  }
  $_SESSION['litrosHorariosHistoricosDiarios']=$litrosHorariosHistoricos;
}


//fb($_SESSION['despachosHorariosHistoricos']);

// despachos por hora
$sqlDespachosHorariosActuales = "select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha)) as q from dbo.Despachos where CONVERT(date, Fecha)=CONVERT(date, Getdate()) group by datepart(HOUR, Fecha) order by hora;";

$stmt = odbc_exec2($mssql, $sqlDespachosHorariosActuales);
if( $stmt === false ){
  echo "Error in executing query. $sqlDespachosHorariosActuales</br>";
  die( print_r( sqlsrv_errors(), true));
}
while($row = sqlsrv_fetch_array($stmt)){
  $despachosHorariosActuales[$row['hora']]=$row['q'];
}
@fb($despachosHorariosActuales);
//// calcula estimacion hora actual
//$minuto = 
@$despachosHorariosActuales[date('G')] = round($despachosHorariosActuales[date('G')]/date('i')*60,0);
//fb($despachosHorariosActuales[date('G')]);
//fb($despachosHorariosActuales);
//fb(date('G'));
$max1 = max($_SESSION['despachosHorariosHistoricos']);
$max2 = max($despachosHorariosActuales);

$maximo = max($max1, $max2)+10;


// litros por hora
$sqlLitrosHorariosActuales = "select datepart(HOUR, Fecha) as hora, sum(Cantidad) as q from dbo.Despachos where CONVERT(date, Fecha)=CONVERT(date, Getdate()) group by datepart(HOUR, Fecha) order by hora;";

$stmt = odbc_exec2($mssql, $sqlLitrosHorariosActuales);
if( $stmt === false ){
  echo "Error in executing query. $sqlLitrosHorariosActuales</br>";
  die( print_r( sqlsrv_errors(), true));
}
while($row = sqlsrv_fetch_array($stmt)){
  $litrosHorariosActuales[$row['hora']]=round($row['q'],1);
}
//fb($litrosHorariosActuales);
// calcula estimacion hora actual
@$litrosHorariosActuales[date('G')] = round($litrosHorariosActuales[date('G')]/date('i')*60,0);
//fb($despachosHorariosActuales[date('G')]);
//fb($litrosHorariosActuales);
//fb(date('G'));
$max1 = max($_SESSION['litrosHorariosHistoricos']);
$max2 = max($litrosHorariosActuales);

$maximo2 = max($max1, $max2)+100;


$sql="SELECT sum( ns ) , sum( np ) , sum( ud ) , sum( ed ) FROM `ventasdiarias` WHERE YEAR( fecha ) = YEAR( CURDATE( ) ) AND MONTH( fecha ) = MONTH( CURDATE( ) ) ";

$sqlMangueras = "SELECT idManguera, IdArticulo FROM dbo.mangueras";
$stmt = odbc_exec2($mssql, $sqlMangueras);
if( $stmt === false ){
	 echo "Error in executing query.</br>";
	 die( print_r( sqlsrv_errors(), true));
}
$mangueras = array();
while($manguera = sqlsrv_fetch_array($stmt)){
    $mangueras[$manguera['idManguera']] = $manguera['IdArticulo'];
}


//$year = (date("n")==1)?date("Y")-1:date("Y");
$year = date("Y");


$ultimoDiaMesAnterior = date('Y-d-m', strtotime('last day of previous month'));
$ultimoCierre22 = (date('H')>22)?date('Y-m-d'):date('Y-d-m', strtotime('yesterday'));

$sqlAforadoresAlUltimoTurnoMesAnterior = "select IdManguera, AforadorElectronico, AforadorMecanico, d.IdCierreSurtidores, Fecha from dbo.CierresDetalleSurtidores as d, dbo.cierressurtidores as s where d.IdCierreSurtidores=s.IdCierreSurtidores AND d.IdCierreSurtidores=(select IdCierreSurtidores from dbo.CierresSurtidores where Fecha>='$ultimoDiaMesAnterior 19:00:00' and Fecha<'$ultimoDiaMesAnterior 23:59:59') UNION select IdManguera, AforadorElectronico, AforadorMecanico, d.IdCierreSurtidores, Fecha from dbo.CierresDetalleSurtidores as d, dbo.cierressurtidores as s where d.IdCierreSurtidores=s.IdCierreSurtidores AND d.IdCierreSurtidores=(select IdCierreSurtidores from dbo.CierresSurtidores where Fecha>='$ultimoCierre22 19:00:00' and Fecha<'$ultimoCierre22 23:59:59') order by IdCierreSurtidores desc";


//$sqlAforadoresAlUltimoTurnoMesAnterior = "select IdManguera, AforadorElectronico, AforadorMecanico, IdCierreSurtidores from dbo.CierresDetalleSurtidores where IdCierreSurtidores=(select top 1 IdCierreSurtidores from dbo.CierresSurtidores where Fecha<'".date("Y-m-01")."' order by Fecha desc) OR IdCierreSurtidores=(select top 1 IdCierreSurtidores from dbo.CierresSurtidores order by Fecha desc)  order by IdCierreDetalleSurtidores desc";
// echo $sqlAforadoresAlUltimoTurnoMesAnterior;
$stmt = odbc_exec2($mssql, $sqlAforadoresAlUltimoTurnoMesAnterior);
if( $stmt === false ){
	 echo "Error in executing query.</br>";
	 die( print_r( sqlsrv_errors(), true));
}
$signo = 1;
$sumaProductoElectronico = Array();
$sumaProductoMecanico = array();
while($aforadores = sqlsrv_fetch_array($stmt)){
    if(!isset($idCierreSurtidores)){
        $idCierreSurtidores = $aforadores['IdCierreSurtidores'];
    } elseif($idCierreSurtidores<>$aforadores['IdCierreSurtidores']){
        $idCierreSurtidores = $aforadores['IdCierreSurtidores'];
        $signo = -1;
    }
    if(isset($sumaProductoElectronico[$mangueras[$aforadores['IdManguera']]])){
        $sumaProductoElectronico[$mangueras[$aforadores['IdManguera']]] +=  $signo*$aforadores['AforadorElectronico'];
        $sumaProductoMecanico[$mangueras[$aforadores['IdManguera']]] +=  $signo*$aforadores['AforadorMecanico'];
    } else {
        $sumaProductoElectronico[$mangueras[$aforadores['IdManguera']]] =  $signo*$aforadores['AforadorElectronico'];
        $sumaProductoMecanico[$mangueras[$aforadores['IdManguera']]] =  $signo*$aforadores['AforadorMecanico'];
    }
    if(!isset($ultimoCierreAyer))$ultimoCierreAyer = $aforadores['Fecha'];
}
$sqlVentasDesdeUltimoCierre = "SELECT IdArticulo, sum(cantidad) from dbo.despachos where fecha>='".$ultimoCierreAyer->format('Y-m-d H:i:s')."' group by IdArticulo; ";
//$sqlVentasDesdeUltimoCierre = "SELECT IdArticulo, sum(cantidad) from dbo.despachos where fecha>='".$ultimoCierreAyer."' group by IdArticulo; ";
//fb($sqlVentasDesdeAyer);
//echo $sqlVentasDesdeUltimoCierre;
$stmt = odbc_exec2($mssql, $sqlVentasDesdeUltimoCierre);
if( $stmt === false ){
	 echo "Error in executing query.</br>";
	 die( print_r( sqlsrv_errors(), true));
}
while($ventasDesdeUltimoCierre = sqlsrv_fetch_array($stmt)){
  $sumaProductoElectronico[$ventasDesdeUltimoCierre[0]] += $ventasDesdeUltimoCierre[1];
  $sumaProductoMecanico[$ventasDesdeUltimoCierre[0]] += $ventasDesdeUltimoCierre[1];
}


$sqlVentasMensuales = "SELECT month(fecha) as mes,  sum( ns ) as l2078, sum( np ) as l2076, sum( ud ) as l2069, sum( ed ) as l2068, year(fecha) as anio FROM `ventasdiarias` WHERE YEAR( fecha ) = YEAR( CURDATE( ) ) OR (YEAR(fecha)=YEAR(CURDATE())-1 AND MONTH(fecha)>=MONTH(CURDATE())-1) group by year(fecha),month(fecha)";
$result = $mysqli->query($sqlVentasMensuales);
if($result&&$result->num_rows>0 ){//&& !$_SESSION['esMovil']
    $tablaVentasMensuales="<table class='table'><thead><tr><th></th><th>$articulo[2068]</th><th>$articulo[2069]</th><th>$articulo[2076]</th><th>$articulo[2078]</th><th>Total</th></tr></thead><tbody>";
        while($rowPromedioDiaSemana = $result->fetch_assoc()){
          //print_r($rowPromedioDiaSemana);
          //Tengo que sumar en un array para cada tipo de combustible, contar y luego hacer promedio
          //Array ( [0] => Wednesday [] => 1084 [1] => 76340.9271 [2] => 2068 [IdArticulo] => 2068 [3] => 1084 )
          if(date("n")==$rowPromedioDiaSemana['mes']&&date("Y")==$rowPromedioDiaSemana['anio']){
              //nada 
          } else {
            $mixInfinia = round($rowPromedioDiaSemana['l2076']/($rowPromedioDiaSemana['l2076']+$rowPromedioDiaSemana['l2078']),2);
            $mixEuro = round($rowPromedioDiaSemana['l2068']/($rowPromedioDiaSemana['l2068']+$rowPromedioDiaSemana['l2069']),2);
            $colorMixInfinia = (($mixInfinia>.18)?'success':(($mixInfinia>.16)?'info':(($mixInfinia>.14)?'warning':'danger')));
            $colorMixEuro = (($mixEuro>.18)?'success':(($mixEuro>.16)?'info':(($mixEuro>.14)?'warning':'danger')));
            $tablaVentasMensuales.="<tr class='".
            (((date("n")==$rowPromedioDiaSemana['mes'])||(date("N")==0))?'label-warning':'').
            "'><td>".$mes[$rowPromedioDiaSemana['mes']]
            ."</td><td>".sprintf("%01.0f",$rowPromedioDiaSemana['l2068']+((date("n")==$rowPromedioDiaSemana['mes'])?$estadoComb[2068]['vtasDdeCierre']:0)). " <span class='label label-$colorMixEuro'>"
            .sprintf("%01.0f",100*$mixEuro).'%</span>'
            ."</td><td>".sprintf("%01.0f",$rowPromedioDiaSemana['l2069']+((date("n")==$rowPromedioDiaSemana['mes'])?$estadoComb[2069]['vtasDdeCierre']:0))
            ."</td><td>".sprintf("%01.0f",$rowPromedioDiaSemana['l2076']+((date("n")==$rowPromedioDiaSemana['mes'])?$estadoComb[2076]['vtasDdeCierre']:0)). " <span class='label label-$colorMixInfinia'>"
            .sprintf("%01.0f",100*$mixInfinia).'%</span>'
            ."</td><td>".sprintf("%01.0f",$rowPromedioDiaSemana['l2078']+((date("n")==$rowPromedioDiaSemana['mes'])?$estadoComb[2078]['vtasDdeCierre']:0))
            ."</td><td>".sprintf("%01.0f",$rowPromedioDiaSemana['l2078']+$rowPromedioDiaSemana['l2069']+$rowPromedioDiaSemana['l2068']+$rowPromedioDiaSemana['l2076'])
            ."</td></tr>";
          }
        }
        $mixInfinia = round(($sumaProductoElectronico['2076']+$estadoComb[2076]['vtasDdeCierre'])/($sumaProductoElectronico['2076']+$estadoComb[2076]['vtasDdeCierre']+$sumaProductoElectronico['2078']+$estadoComb[2078]['vtasDdeCierre']),2);
        $mixEuro = round(($sumaProductoElectronico['2068']+$estadoComb[2068]['vtasDdeCierre'])/($sumaProductoElectronico['2068']+$estadoComb[2068]['vtasDdeCierre']+$sumaProductoElectronico['2069']+$estadoComb[2069]['vtasDdeCierre']),2);
        $colorMixInfinia = (($mixInfinia>.18)?'success':(($mixInfinia>.16)?'info':(($mixInfinia>.14)?'warning':'danger')));
        $colorMixEuro = (($mixEuro>.18)?'success':(($mixEuro>.16)?'info':(($mixEuro>.14)?'warning':'danger')));
        $totalMesActual = $sumaProductoElectronico['2078']+$sumaProductoElectronico['2076']+$sumaProductoElectronico['2068']+$sumaProductoElectronico['2069']+$estadoComb[2068]['vtasDdeCierre']+$estadoComb[2069]['vtasDdeCierre']+$estadoComb[2076]['vtasDdeCierre']+$estadoComb[2078]['vtasDdeCierre'];
        $totalMesEstimado = $totalMesActual/(date('d')-1+date('H')/24)*date('t');
        $tablaVentasMensuales.="<tr class='label-warning'><td>".$mes[date("n")]
        ."</td><td>".sprintf("%01.0f",$sumaProductoElectronico['2068']+$estadoComb[2068]['vtasDdeCierre']). " <span class='label label-$colorMixEuro'>"
        .sprintf("%01.0f",100*$mixEuro).'%</span>'
        ."</td><td>".sprintf("%01.0f",$sumaProductoElectronico['2069']+$estadoComb[2069]['vtasDdeCierre']) 
        ."</td><td>".sprintf("%01.0f",$sumaProductoElectronico['2076']+$estadoComb[2076]['vtasDdeCierre']). " <span class='label label-$colorMixInfinia'>"
        .sprintf("%01.0f",100*$mixInfinia).'%</span>'
        ."</td><td>".sprintf("%01.0f",$sumaProductoElectronico['2078']+$estadoComb[2078]['vtasDdeCierre'])
        ."</td><td>".sprintf("%01.0f",$totalMesActual)
        ."<br/>".sprintf("%01.0f",$totalMesEstimado)."</td></tr>";
        //$tablaPromedioDiaSemana.="<tr class='label-info'><td>General</td><td>".sprintf("%01.2f",$promedioHistorico['l2068'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2069'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2076'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2078'])."</td></tr></tbody></table>";
    $tablaVentasMensuales.="</tbody></table>";
}


if(!isset($_GET['soloComb'])){



//print_r($estadoComb);
function d($fecha, $incluyeHora=false){
  if($incluyeHora){
    $part = explode(' ', $fecha);
    $dia = explode('-', $part[0]);
    return $dia[2].'/'.$dia[1].' '.$part[1]; 
  } else {
    $part = explode('-', $fecha);	
    return $part[2].'/'.$part[1];
  }
}

function muestraOrdenes(){
	global $articulo, $estadoComb, $config, $mysqli;
	$tabla="<table class='table'><tbody>";//<thead><tr><th>OP</th><th>Estado</th><th>Pedido</th></tr></thead>
	$sql1 = "SELECT * FROM ordenes ORDER BY idOrden DESC LIMIT 5";
	$res1 = $mysqli->query($sql1);
	while($orden = $res1->fetch_assoc()){
		// veo el estado y decido que otros datos levanto
		// print_r($orden);
		$renglon = '';
		$sqlEstados = "SELECT estado, fechaEstado FROM estados WHERE idOrden='$orden[idOrden]' ORDER BY fechaEstado DESC";
		$resEstados = $mysqli->query($sqlEstados);
		$numEstados = $resEstados->num_rows;
        if($orden['entregado']==1)$orden['ultimoEstado']='Pedido Entregado';
		switch($orden['ultimoEstado']){
			case 'Pedido Ruteado':
			case 'Pedido Ruteado Parcialmente':
				$sql2 = "SELECT * FROM pedidos WHERE idOrden='$orden[idOrden]'";
				$res2 = $mysqli->query($sql2);
				while($pedido = $res2->fetch_assoc()){
					//[idPedido] => 92 [idOrden] => 239 [idArticulo] => 2069 [litrosPedidos] => 24000 [litrosEntregados] => 0 [litrosDespachados]
					$renglon .= "{$articulo[$pedido['idArticulo']]} - $pedido[litrosEntregados]<br/>";
				}
				$tabla.="<tr class='alert alert-info'><th>$orden[op]<br/>(".d($orden['fechaPedido']).")</th><td>$orden[ultimoEstado] ".d($orden['fechaDespachoEstimada'])."</td><td style='border-bottom:none'>$renglon</td><td>$orden[camion]</td>";
				//Array ( [idOrden] => 239 [op] => 0071267108 [fechaPedido] => 2012-11-16 [fechaDespacho] => 0000-00-00 [fechaEstimada] => 0000-00-00 [transportista] => Cucit [camion] => 0003-Cucit [fechaDespachoEstimada] => 2012-10-19 [horaDespacho] => 00:00:00 [remito] => 0 [ultimoEstado] => Pedido Ruteado [entregado] => 0 ) 
				break;
			case 'Despachado Parcialmente':
			case 'Despachado':
			case 'Pedido Entregado':
            case 'Pedido Despachado':
				$sql2 = "SELECT * FROM pedidos WHERE idOrden='$orden[idOrden]'";
				$res2 = $mysqli->query($sql2);
				while($pedido = $res2->fetch_assoc()){
					$renglon .= "{$articulo[$pedido['idArticulo']]}: <b>$pedido[litrosDespachados] lts.</b>".(($pedido['litrosDespachados']<>$pedido['litrosPedidos'])?" (".($pedido['litrosDespachados']-$pedido['litrosPedidos'])." lts.)":'')."<br/>";
				}
				if($orden['entregado']==1){
					$tabla.="<tr class='OPentregada'><th>
 $orden[op]<br/>(".d($orden['fechaPedido']).")</th><td >Entregada ".d($orden['fechaEntregada'],1)." </td><td>$renglon</td><td></td>";
					//Array ( [idOrden] => 234 [op] => 0071266889 [fechaPedido] => 2012-11-15 [fechaDespacho] => 2012-11-16 [fechaEstimada] => 0000-00-00 [transportista] => [camion] => [fechaDespachoEstimada] => 0000-00-00 [horaDespacho] => 21:59:36 [remito] => 600755226 [ultimoEstado] => Despachado [entregado] => 0 ) 
				} else {
					$tabla.="<tr class='success'><th class='alert alert-success'>$orden[op]<br/>(".d($orden['fechaPedido']).") <a href='func/modalDescargaCisterna.php?op=$orden[idOrden]' rel='modal:open'><span class='glyphicon glyphicon-tint descargaCisterna' id='$orden[idOrden]'> [@] </span></a></th><td>Despachada ".(($orden['fechaDespacho']<>'0000-00-00')?d($orden['fechaDespacho'])." a las $orden[horaDespacho]":' (sin fecha)')."</td><td>$renglon</td><td>$orden[camion]</td>";
					//Array ( [idOrden] => 234 [op] => 0071266889 [fechaPedido] => 2012-11-15 [fechaDespacho] => 2012-11-16 [fechaEstimada] => 0000-00-00 [transportista] => [camion] => [fechaDespachoEstimada] => 0000-00-00 [horaDespacho] => 21:59:36 [remito] => 600755226 [ultimoEstado] => Despachado [entregado] => 0 ) 
				}
				break;
			
			case 'Dado de baja':
				while($estado = $resEstados->fetch_array()){
					if(!isset($ultimoEstado)&&isset($estado['estado'])){
						$ultimoEstado=$estado['estado'];
						$renglon .= d($estado['fechaEstado'],1)." - $estado[estado]<br/>";
					}elseif(isset($estado[0])&&$ultimoEstado<>$estado['estado']){
						$renglon .= d($estado['fechaEstado'],1)." - $estado[estado]<br/>";
					}
				}
				if($orden['caida']==1&&$orden['entregado']<>1){
					$tabla.="<tr class='error OPcaida'><td>$orden[op]<br/>(".d($orden['fechaPedido']).")</td><td>OP CAIDA</td><td>$renglon</td>";
				}else{
					$tabla.="<tr>"
                            . "<th class='OPentregada'>$orden[op]<br/>(".d($orden['fechaPedido']).")</th><td class='OPentregada' colspan='2'>Recibida el ".d($orden['fechaEntregada'],1)."</td>"
                            . "</tr>"
                            . "<tr>";
				}				
				break;
			
			
			case 'OP Pendiente de Autorizacion':
			case 'OP Abierta':
				$sql2 = "SELECT * FROM pedidos WHERE idOrden='$orden[idOrden]'";
				$res2 = $mysqli->query($sql2);
				while($pedido = $res2->fetch_assoc()){
					$renglon .= "{$articulo[$pedido['idArticulo']]}: <b>$pedido[litrosPedidos] lts.</b><br/>";
				}
				if($numEstados>1)
				while($estado = $resEstados->fetch_assoc()){
					if(!isset($ultimoEstado)&&isset($estado[0]))$ultimoEstado=$estado[0];
					$renglon .=  d($estado['fechaEstado'],1)." - $estado[estado]<br/>";
				}
				$alert=($orden['ultimoEstado']=='OP Abierta')?'success':'block';
				$tabla.="<tr><th class='label-$alert'>$orden[op]<br/>(".d($orden['fechaPedido']).")</th><td>$orden[ultimoEstado]</td><td>$renglon</td></tr><tr>";
				break;
			
			default:
				while($estado = $resEstados->fetch_assoc()){
					if(!isset($ultimoEstado))$ultimoEstado=$estado['fechaEstado'];
					$renglon .= d($estado['fechaEstado'],1)." - $estado[estado]<br/>";
				}
				$tabla.="<tr><th >$orden[ultimoEstado] $orden[op]<br/>(".d($orden['fechaPedido']).")</th><td class='OPentregada'>$orden[ultimoEstado] $ultimoEstado</td><td >$renglon</td><td>Transporte $orden[camion]</td>";
				//Array ( [idOrden] => 234 [op] => 0071266889 [fechaPedido] => 2012-11-15 [fechaDespacho] => 2012-11-16 [fechaEstimada] => 0000-00-00 [transportista] => [camion] => [fechaDespachoEstimada] => 0000-00-00 [horaDespacho] => 21:59:36 [remito] => 600755226 [ultimoEstado] => Despachado [entregado] => 0 ) 
				break;
			
		
		}
		$tabla.="</tr>";
	}
	echo $tabla.'</tbody></table>';
}
if(false){
function muestraProyeccion(){
	global $articulo, $estadoComb, $config;
	$tablaEncabezado="<table class='table'><thead><tr><th></th><th>$articulo[2068]</th><th>$articulo[2069]</th><th>$articulo[2076]</th><th>$articulo[2078]</th></tr></thead><tbody>";
	
	$renglonStockSistema="<tr><td>Sistema</td>";
	$renglonDescargas="<tr><td>Descargas</td>";
	$renglonStockActual="<tr><td>Actual</td>";
	$renglonProvision="<tr><td>Provisión</td>";
	foreach($articulo as $key => $n){
		// muestra stock actual segun sistema
		$renglonStockSistema.="<td>".$estadoComb[$key]['stockInicial']."</td>";
		// revisa en la tabla si hubo descargas de combustible en el día de hoy (que no figuran en el sistema aún)
		$renglonDescargas.="<td>".((isset($estadoComb[$key]['descarga']))?sprintf("%01.2f",$estadoComb[$key]['descarga']):'0.00')."</td>";
		// stock con descargas
		if(isset($estadoComb[$key]['descarga'])&&$estadoComb[$key]['descarga']>$config['minimoDescarga']){
			$estadoComb['hayDescarga']=true;
		}
		$stockActual[$key]=(isset($estadoComb[$key]['descarga'])&&$estadoComb[$key]['descarga']>$config['minimoDescarga']?($estadoComb[$key]['descarga']+$estadoComb[$key]['stockInicial']):$estadoComb[$key]['stock']);
		// renglon stock actual con descargas del dia con cantidad de días con combustible segun el stock actual mas las descargas del dia
			
		$stockDias = round($stockActual[$key]/$estadoComb[$key]['promedio'],1);
        $stockDias = round($stockActual[$key]/1,1);
		
		$stockDias=($stockDias<2)?"<span class='btn-danger label'>($stockDias d)</span>":"($stockDias d)";
		
		$renglonStockActual.="<td>".sprintf("%01.2f",$stockActual[$key])."<br/><b>$stockDias</b></td>";
		// cantidad de días con combustible segun el stock actual mas las descargas del dia
		//$renglonProvision.="<td>".round($stockActual[$key]/$estadoComb[$key]['promedio'],1)."</td>";
	}
	$renglonStockSistema.="</tr>";
	$renglonDescargas.="</tr>";
	$renglonProvision.="</tr>";
	$renglonStockActual.="</tr>";
	
	// calcula stock previo al proximo cisterna (si hubiera OP abiertas en curso)
	if(isset($estadoComb['proximaOP'])){
		/*
			Array ( [proximaOP] => Array ( 
			[13] => Array ( [fechaEstimada] => 2012-06-02 [idOrden] => 13 [op] => 0071209809 ) 
			[14] => Array ( [fechaEstimada] => 2012-06-02 [idOrden] => 14 [op] => 0071210431 ) ) 
			[2069] => Array ( [13] => 24000 ) 
			[2078] => Array ( [13] => 10000 ) 
			[2068] => Array ( [14] => 5000 ) ) 
		*/
		$renglonEstimado=$renglonProximaOP="";
		foreach($estadoComb['proximaOP'] as $idOrden => $datosOrden){
			// calculo cuantos días hay entre este momento y las 14 horas del día de entrega estimada, absado en  Ojímetro (TM)
			// [fechaEstimada] => 2012-06-02 [idOrden] => 13 [op] => 0071209809 
			$interval = date_diff(date_create(date("Y-m-d H:i:s")), date_create(date($datosOrden['fechaEstimada']." 14:00:00")));
			$intervalo = $interval->format('%a')+($interval->format('%h')/24)+($interval->format('%i')/(24*60));
			// Array ( [fechaEstimada] => 2012-06-02 [idOrden] => 13 [op] => 0071209809 ) 
			$renglonEstimado="<tr class='alert-info'><td>Estimado ".$datosOrden['fechaEstimada']."</td>";
			$renglonProximaOP.="<tr class='alert-info'><td><a href='cargaOP.php?id={$estadoComb['proximaOP'][$idOrden]['idOrden']}' class=' label-info'>{$estadoComb['proximaOP'][$idOrden]['op']}</a></td>";
			$renglonResultante="<tr class='alert-success'><td>Resultante {$estadoComb['proximaOP'][$idOrden]['fechaEstimada']}</td>";
			
			foreach($articulo as $key => $n){
				// tira estimacion con el estado actual de tanques al día de la próxima descarga
				$estimado[$key] = (($stockActual[$key]-($estadoComb[$key]['promedio']*$intervalo))<0)?0:$stockActual[$key]-($estadoComb[$key]['promedio']*$intervalo);
				$renglonEstimado.="<td>".sprintf("%01.2f",$estimado[$key])."</td>";
				//echo "$key:$intervalo: $estimado[$key] = (($stockActual[$key]-({$estadoComb[$key]['promedio']}*$intervalo))<0)?0:<br/>";
				// muestra litros esperados en la Proxima OP
				$renglonProximaOP.="<td>".sprintf("%01.2f",((isset($estadoComb[$key][$idOrden]))?$estadoComb[$key][$idOrden]:0))."</td>";
				
				if(!isset($stockConDescarga[$key]))
					$stockConDescarga[$key]=$estimado[$key]+((isset($estadoComb[$key][$idOrden]))?$estadoComb[$key][$idOrden]:0);
				else
					$stockConDescarga[$key]=$stockConDescarga[$key]+((isset($estadoComb[$key][$idOrden]))?$estadoComb[$key][$idOrden]:0);
				
				if(isset($resultante[$key]))
					$resultante[$key] = $resultante[$key] + round($stockActual[$key]-($estadoComb[$key]['promedio']*$intervalo)+((isset($estadoComb[$key][$idOrden]))?$estadoComb[$key][$idOrden]:0),2);
				else
					$resultante[$key] = round($stockActual[$key]-($estadoComb[$key]['promedio']*$intervalo)+((isset($estadoComb[$key][$idOrden]))?$estadoComb[$key][$idOrden]:0),2);
				
				//$resultanteDias = round(($stockActual[$key]-($estadoComb[$key]['promedio']*$intervalo)+((isset($estadoComb[$key][$idOrden]))?$estadoComb[$key][$idOrden]:0))/$estadoComb[$key]['promedio'],1);
				$resultanteDias = round($resultante[$key]/$estadoComb[$key]['promedio'],1);
				
				$resultanteDias=($resultanteDias<2)?"<span class='btn-danger label'>($resultanteDias d)</span>":"($resultanteDias d)";
				$renglonResultante.="<td>$resultante[$key]<br/><b>$resultanteDias</b><br>".round($stockConDescarga[$key])."</td>"; //debug
			}
			$renglonEstimado.="</tr>";
			$renglonProximaOP.="</tr>";
			$renglonResultante.="</tr>";
		}
	} else {
		$renglonProximaOP="<tr class='btn-danger'><td colspan='5' align='center'>ATENCION! NO HAY ORDEN DE PROVISIÓN EN CURSO</td>";
	}
	
	
	echo $tablaEncabezado;
	if(isset($estadoComb['hayDescarga'])){
		echo $renglonStockSistema;
		echo $renglonDescargas;
	}
	echo $renglonStockActual;
	//echo $renglonProvision;
	if(isset($renglonEstimado))
	echo $renglonEstimado;
	if(isset($renglonProximaOP))
	echo $renglonProximaOP;
	if(isset($renglonResultante))
	echo $renglonResultante;
	echo"</tbody></table>";
}}
// aca iba muestraDetalleTanques


}


?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
    <link rel="stylesheet" href="css/jquery.modal.css" type="text/css" media="screen" />
    <style type="text/css">
        body{
          <?php if(!$_SESSION['esMovil']){?>
          margin: 50px auto;
          <?php } else {?>
          margin: 10px 0;
          <?php }?>
        }
        .OPentregada{
            background-color:#fcf8e3;
        }
        #myModal2 table{
            background-color:#fff;
        }
    </style>
    <?php if(isset($_GET['soloComb'])){?><link href="css/graficobarras.css" rel="stylesheet" type="text/css" media="screen"/><?php }; ?>
	<link rel="stylesheet" href="css/print.css" type="text/css" media="print"/>
  </head>
  <body>
	<?php if(!isset($_GET['soloComb'])&&!$_SESSION['esMovil']){include($_SERVER['DOCUMENT_ROOT']."/include/menuSuperior.php");} ?>
	<?php //if(!isset($_GET['soloComb'])){include("include/menuSuperior.php");} ?>
    <div class="container">
		<!-- Example row of columns -->
		<div class="row">
            <div class='col-md-5'>
                <div class="panel panel-primary" id='combustibles'>
                                <div class="panel-heading">
                                    <h3 class="panel-title">Combustibles <?php echo date("d/m/y H:i:s")?></span></h3>
                                  </div>
                     <div class="panel-body">
                        <table class='table'><thead><tr><th></th><th>Ventas</th><th>Actual</th><th>Lleno</th><th>Vacío</th></tr></thead>
                        <tbody>
                            <?php echo $tabla2;?>
                        </tbody>
                    </table>
                </div>
                <!--<p><a class="btn" href="#" id='detallesTanques'>Detalle por tanques &raquo;</a></p>-->
                </div>
                
            </div>
            <?php if(isset($_GET['juli'])){?>
            <div class='col-md-5 alert'>
                   <?php 
                   if($estadoComb[2078]['stock']>5000)echo "Queda mucha Super";
                   elseif($estadoComb[2078]['stock']>2000)echo "Queda Super";
                   elseif($estadoComb[2078]['stock']>1000)echo "Apurate, queda poca Super";
                   else {echo "No hay Super"; $y = "pero";}
                   if(!isset($y))$y="y";
                   if($estadoComb[2076]['stock']>7000)echo ", $y queda mucha, mucha Infinia<br/>";
                   elseif($estadoComb[2076]['stock']>4000)echo " $y queda mucha Infinia<br/>";
                   elseif($estadoComb[2076]['stock']>2000)echo ", hay Infinia<br/>";
                   elseif($estadoComb[2076]['stock']>1000){
                       if($estadoComb[2078]['stock']<3000)echo ", <b>CORRÉ, QUEDA POCA NAFTA</b><br/>";
                       else echo ", queda poca Infinia<br/>";
                   }
                   else echo ", no hay Infinia<br/>";
                   ?>
            </div>
        </div> <!-- /container -->
        <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
    </div></body></html>
            <?php die;}?>
			<div class='col-md-5'>
			<div class="panel panel-primary" id='combustibles'>
                <div class="panel-heading">
                    <h3 class="panel-title">Detalle tanques</h3>
                </div>
                <div class="panel-body gris" id="panelDetalle">
                    <?php $b=microtime();muestraDetallesTanquesTelemedidos();?>
                    <?php //muestraDetalleTanques()?>
                </div>
            </div></div>

        </div>
        <div class='row'>
          <div id="chartContainer" style="height: 200px; " class='col-md-10'></div>
        </div>
        <div class='row'>
          <div id="chartContainer2" style="height: 200px; " class='col-md-10'></div>
        </div>
        
        
        
        <div class='row'>
            <div class='col-md-5'>
			<div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Despachado mensual</h3>
                </div>
                <div class="panel-body gris">
                    <?php echo $tablaVentasMensuales;?>
                </div>
            </div>            </div>

            <div></div><?php if(!isset($_GET['soloComb'])&&!$_SESSION['esMovil']){?>
            <div class='col-md-5'>
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Recibido mensual</h3>
                </div>
                <div class="panel-body gris">
                    <?php $a=microtime();recepcionMensual();?>
                </div>
            </div>
            </div><?php } ?>
        </div>
        
        <div class='row'>
        <?php if(!isset($_GET['soloComb'])&&!$_SESSION['esMovil']){?>
              <div class='col-md-5'>
			<div class="panel panel-primary" id='pedidos'>
                <div class="panel-heading">
                    <h3 class="panel-title">Ordenes <span class='pull-right sh2'><?php echo $interval->format('%h:%I horas').' // '.$ultimoUpdate[1]?></span><span class="sh2 glyphicon glyphicon-refresh"><a class='noPrint' href='http://C30530341131:ingreso46@downstream.ypf.com.ar/agent_portal/plsql/PKG_CONSULTA_UNIFICADA.prc_consulta'>Abrir YPF</a></span> || <span id='muestraYPF'>Ultimo estado</span></h3>
                </div>
                <div class="panel-body gris" id="panelDetalle">
                <?php $a=microtime();muestraOrdenes();?>
                </div>
			</div>
            </div>

			<!-- <div class="col-md-3 noPrint" id="botonera">Detalle tanques<br/>Boton2</div>-->
            <div class='col-md-5'>
			<div class="panel panel-primary" id='ventasDiarias'>
                <div class="panel-heading">
                    <h3 class="panel-title">Promedio diario <?php echo date("M")?></h3>
                </div>
                <div class="panel-body gris" id="panelDetalle">
                    <?php echo $tablaPromedioDiaSemana; ?>
                </div>
			</div>
            </div><?php } ?>
		</div>
        
        <?php /*if(!$_SESSION['esMovil']){echo "<h1> no es movil</h1>";} else {echo "<h1> SI es movil</h1>"; }*/?>
        <?php if(!$_SESSION['esMovil']){?>
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        </div><!-- /.modal-dialog -->
        <?php } ?>
        <?php include($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script src="js/jquery.modal.min.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript">
          window.onload = function () {
            var chart = new CanvasJS.Chart("chartContainer",
            {
              zoomEnabled: false,
              animationEnabled: true,
              axisY2:{
                valueFormatString:"0",
                maximum: <?php echo $maximo?>,
                interval: 10,
                interlacedColor: "#F5F5F5",
                gridColor: "#D7D7D7",      
                tickColor: "#D7D7D7"
              },
              theme: "theme2",
              toolTip:{
                shared: true
              },
              legend:{
                verticalAlign: "bottom",
                horizontalAlign: "center",
                fontSize: 15,
                fontFamily: "Lucida Sans Unicode"

              },
              data: [
              {        
                type: "line",
                lineThickness:3,
                axisYType:"secondary",
                showInLegend: true,           
                name: "Histórico", 
                dataPoints: [
                <?php foreach($_SESSION['despachosHorariosHistoricos'] as $hora => $despachos){
                  if(isset($coma1))echo","; else $coma1=1;
                  echo "{ x: $hora, y: $despachos }";
                }?>
                ]
              },
              {        
                type: "line",
                lineThickness:3,
                showInLegend: true,           
                name: "Actual",
                axisYType:"secondary",
                dataPoints: [
                <?php foreach($despachosHorariosActuales as $hora => $despachos){
                  if(isset($coma2))echo","; else $coma2=1;
                  echo "{ x: $hora, y: $despachos }";
                }?>
                ]
              },
              {        
                type: "line",
                lineThickness:3,
                showInLegend: true,           
                name: "<?php echo date('l');?>",
                axisYType:"secondary",
                dataPoints: [
                <?php foreach($_SESSION['despachosHorariosHistoricosDiarios'][date('w')] as $hora => $despachos){
                  if(isset($coma5))echo","; else $coma5=1;
                  echo "{ x: $hora, y: $despachos }";
                }?>
                ]
              }
              ],
            legend: {
              cursor:"pointer",
              itemclick : function(e) {
                if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
                }
                else {
                  e.dataSeries.visible = true;
                }
                chart.render();
              }
            }
          });
          chart.render();
          
          var chart = new CanvasJS.Chart("chartContainer2",
            {
              zoomEnabled: false,
              animationEnabled: true,
              axisY2:{
                valueFormatString:"0",
                maximum: <?php echo $maximo2?>,
                interval: <?php echo $maximo2/6?>,
                interlacedColor: "#F5F5F5",
                gridColor: "#D7D7D7",      
                tickColor: "#D7D7D7"
              },
              theme: "theme2",
              toolTip:{
                shared: true
              },
              legend:{
                verticalAlign: "bottom",
                horizontalAlign: "center",
                fontSize: 15,
                fontFamily: "Lucida Sans Unicode"

              },
              data: [
              {        
                type: "line",
                lineThickness:3,
                axisYType:"secondary",
                showInLegend: true,           
                name: "Histórico", 
                dataPoints: [
                <?php foreach($_SESSION['litrosHorariosHistoricos'] as $hora => $despachos){
                  if(isset($coma3))echo","; else $coma3=1;
                  echo "{ x: $hora, y: $despachos }";
                }?>
                ]
              },
              {        
                type: "line",
                lineThickness:3,
                showInLegend: true,           
                name: "Actual",
                axisYType:"secondary",
                dataPoints: [
                <?php foreach($litrosHorariosActuales as $hora => $despachos){
                  if(isset($coma4))echo","; else $coma4=1;
                  echo "{ x: $hora, y: $despachos }";
                }?>
                ]
              },
              {        
                type: "line",
                lineThickness:3,
                showInLegend: true,           
                name: "<?php echo date('l')?>",
                axisYType:"secondary",
                dataPoints: [
                <?php foreach($_SESSION['litrosHorariosHistoricosDiarios'][date('w')] as $hora => $despachos){
                  if(isset($coma6))echo","; else $coma6=1;
                  echo "{ x: $hora, y: $despachos }";
                }?>
                ]
              }
              ],
            legend: {
              cursor:"pointer",
              itemclick : function(e) {
                if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
                }
                else {
                  e.dataSeries.visible = true;
                }
                chart.render();
              }
            }
          });
          chart.render();
        }
        </script>
	<script type="text/javascript" src="js/canvasjs.min.js"></script>
	<script>
        $(document).ready(function() {
            $('#fecha').datepicker({autoclose: true});
    //        id='$orden[idOrden]' class='descargaCisterna'
            $('.descargaCisterna').click(function(){
              $('#idOrden').val($(this).attr('id'));
              $('#myModal').modal({remote:'func/modalDescargaCisterna.php'});
              var opciones= {
                success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
                url:       'func/asignaTanques2.php',         // override for form's 'action' attribute 
                type:      'post',       // 'get' or 'post', override for form's 'method' attribute
                dataType:   'json'
              };
                //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
              $('#formDescarga').ajaxForm(opciones) ; 
              function mostrarRespuesta(responseText){
                if(responseText.status==='success'){
                  $('#myModal').modal('hide');
                  location.reload();
                } else {
                  $('.litros').effect( "highlight", {color:"#c7270a"}, 3000 );
                }
                // eliminar bloque en box izquierdo
                // mejorar efecto de feedback
                // eliminar opcion dedistribuir el mismo camion de nuevo
              };
            });

        });
	</script>
  </body>
</html>
