<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
echo "<tr>";
for($i=0; $i<12;$i++){
  echo "<td align='center'>".date("M Y", strtotime("-$i months"))."<table><thead><tr><th class='columna'>Empleado</th><th class='columna'>Cuantos</th><th class='columna'>%</th><th>Desp</th></thead><tbody>";
  $sqlResultados = "select c.IdEmpleado, count(c.IdTicket) as cuantos, sum(c.esNafta) as nafta, sum(c.esDespacho) as despachos from [coop].dbo.promoDesayunos as c, [CoopDeTrabajo.Net].dbo.Empleados as e where c.IdEmpleado=e.IdEmpleado AND c.mesAsignado=".date("Ym", strtotime("-$i months"))." Group by c.IdEmpleado order by cuantos desc;";
  $stmt = odbc_exec2($mssql, $sqlResultados, __LINE__, __FILE__);
  $tr='';
  $j=0;
  while($rowResultados = sqlsrv_fetch_array($stmt)){
    $j++;
    echo "<tr class='".(($j<4)?'bold':'')."'><td>{$vendedor[$rowResultados[0]]}</td><td>$rowResultados[cuantos]</td><td>".round($rowResultados['nafta']/$rowResultados['cuantos']*100,0)."%</td><td>".round($rowResultados['despachos']/$rowResultados['cuantos']*100,0)."%</td></tr>";
  }
  echo "</tbody></table>";
  if($i == 3 || $i==7 || $i==11)echo "</tr><tr>";
} 
echo "</tr>";
?>
