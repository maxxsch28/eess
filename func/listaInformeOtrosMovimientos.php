<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
 // print_r($_POST);
 // $array=array();
if(isset($_POST['mes'])){
    $mes=substr($_POST['mes'],4,2);
    $anio=substr($_POST['mes'],0,4);
}

$rangoMes = "DATEPART(month, fecha) = $mes and DATEPART(year, fecha)=$anio";
//$rangoMes = "mes = $mes and anio = $anio";


$sqlGastos = "SELECT Descripcion, IngresoEgreso, Detalle, dbo.OtrosMovimientosCajaTesoreria.IdGrupoOtrosMovimientosCajaTesoreria, Importe FROM dbo.OtrosMovimientosCajaTesoreria, dbo.GruposOtrosMovimientosCajaTesoreria WHERE dbo.OtrosMovimientosCajaTesoreria.IdGrupoOtrosMovimientosCajaTesoreria=dbo.GruposOtrosMovimientosCajaTesoreria.IdGrupoOtrosMovimientosCajaTesoreria AND $rangoMes AND otrosmovimientoscajatesoreria.IdGrupoOtrosMovimientosCajaTesoreria NOT IN (1, 2, 16, 27, 3, 34,31) order by Descripcion";

$stmt = odbc_exec2( $mssql, $sqlGastos, __LINE__, __FILE__);
$cantidadFilas = sqlsrv_num_rows($stmt);


$a=0;
$sumaGasto=0;
$div="";//"<p class='text-capitalize'>";//'<div class="div-table-row"><div class="div-table-col" align="center">Rubro</div><div  class="div-table-col">Importe</div></div>';
while($fila = sqlsrv_fetch_array($stmt)){
    $a++;//echo "|".fmod(6, $a)."|";
    if(!isset($encabezado)){
      $encabezado=trim($fila['Descripcion']);
    } elseif($encabezado<>trim($fila['Descripcion'])){
      $div.= "<tr style='font-weight:bold' class='alert alert-success'><td><b>$encabezado</td><td class=''>".(($sumaGasto<>0)?sprintf("%01.2f",$sumaGasto):'')."</td></tr>";
      $sumaGasto=0;
      $encabezado=$fila['Descripcion'];
    }
    $ultimaDiv = "<tr style='font-weight:bold' class='alert alert-success'><td><b>$encabezado</td><td class=''>".(($sumaGasto<>0)?sprintf("%01.2f",$sumaGasto):'')."</td></tr>";
    
    $div .= "<tr class='viaje'><td>".ucwords(strtolower(trim($fila['Detalle'])))."</td><td class=''>".(($fila['IngresoEgreso']==1)?'':'-').(($fila['Importe']<>0)?sprintf("%01.2f", ($fila['Importe'])):'')."</td></tr>";
    $sumaGasto+=(($fila['IngresoEgreso']==1)?1:-1)*$fila['Importe'];
}
echo $div.$ultimaDiv;

?>
