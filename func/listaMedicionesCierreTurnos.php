<?php
// listaMedicionesCierreTurnos.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$limit=11;
$offset=0;

if(isset($_POST['fechaCierre'])&&$_POST['fechaCierre']<>''){
  $mm=substr($_POST['fechaCierre'],3,2);
  $aa=substr($_POST['fechaCierre'],6,4);
  $dd=substr($_POST['fechaCierre'],0,2);
} elseif(isset($_POST['saldoCaja'])){
  $mm=date("m");
  $aa=date("Y");
  $dd=date("d");
} else {
  $mm='11';
  $aa='2018';
  $dd='01';
}



echo "<table><thead><tr><th rowspan=2>Fecha</th><th colspan=2>Tanque 1</th><th colspan=2>Tanque 2</th><th colspan=2>Tanque 3</th><th colspan=2>Tanque 4</th><th colspan=2>Tanque 5</th><th colspan=2>Tanque 6</th></tr><tr><th>mm</th><th>lts</th><th>mm</th><th>lts</th><th>mm</th><th>lts</th><th>mm</th><th>lts</th><th>mm</th><th>lts</th><th>mm</th><th>lts</th></thead><tbody>";
$sqlTurnos = "select Fecha FROM dbo.CierresTurno WHERE IdCaja=1 AND Fecha>'$aa-$mm-$dd' ORDER BY IdCierreTurno ASC;";
$stmt = odbc_exec2( $mssql, $sqlTurnos, __FILE__, __LINE__);
while($rowTurnos = sqlsrv_fetch_array($stmt)){
  // toma las lecturas de tanques para cada cierre de turno
  echo "<tr><td>".$rowTurnos['Fecha']->format('Y-m-d H:i')."</td>";
  for($i=1; $i<7; $i++){
    $sqlMediciones = "SELECT TOP 1 Litros, NivelAgua, Nivel, IdTanque from dbo.tanquesmediciones WHERE idTanque=$i AND FechaHora<'".$rowTurnos['Fecha']->format('Y-m-d H:i:s')."' ORDER BY LastUpdated DESC;";
    $stmt2 = odbc_exec2( $mssql, $sqlMediciones, __FILE__, __LINE__);

    while($rowMedicion = sqlsrv_fetch_array($stmt2)){
      echo "<td>$rowMedicion[Nivel]</td><td>$rowMedicion[Litros]</td>";
      //ChromePhp::log($sqlChequesRecibos);
      
    }
  }
  echo "</tr>";
}
echo "</tbody></table>";
?>
