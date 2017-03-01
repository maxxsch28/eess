<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
fb($_POST);

 // $array=array();
//$_POST['mes']='201411';
// ﻿Array ( [IdGrupoArticulo] => 3 [clientes] => Array ( [0] => grupo [1] => ubicacion ) [ubicacion] => DANESA )
$filtraUbicacion = (isset($_POST['clientes'][2]))?" AND ubicacion='$_POST[ubicacion]'":'';
$filtraGrupoArticulo = (isset($_POST['clientes'][1]))?" AND g.IdGrupoArticulo=$_POST[IdGrupoArticulo]":'';

$sqlArticulos = "SELECT Codigo, CodigoBarra, dbo.Articulos.Descripcion, PrecioPublico, dbo.GruposArticulos.Descripcion as grupo FROM dbo.Articulos, dbo.GruposArticulos WHERE dbo.Articulos.IdGrupoArticulo=dbo.GruposArticulos.IdGrupoArticulo AND dbo.Articulos.Activo=1 $filtraUbicacion $filtraGrupoArticulo UNION ALL SELECT Codigo, Sinonimos.CodigoBarra, dbo.Articulos.Descripcion, PrecioPublico, dbo.GruposArticulos.Descripcion as grupo FROM dbo.Articulos, dbo.GruposArticulos, dbo.Sinonimos WHERE dbo.Articulos.IdGrupoArticulo=dbo.GruposArticulos.IdGrupoArticulo AND dbo.Articulos.Activo=1 $filtraUbicacion $filtraGrupoArticulo AND dbo.Articulos.IdArticulo=dbo.Sinonimos.IdArticulo AND ubicacion='LACTEO' ORDER BY dbo.GruposArticulos.Descripcion, dbo.articulos.Descripcion";

$sqlArticulos = "SELECT Codigo, CodigoBarra, PrecioPublico, g.Descripcion+'' as grupo, a.Descripcion+'' as articulo FROM dbo.Articulos a, dbo.GruposArticulos g WHERE a.IdGrupoArticulo=g.IdGrupoArticulo AND a.Activo=1 $filtraUbicacion $filtraGrupoArticulo ORDER BY grupo, a.Descripcion";


echo $sqlArticulos;


$stmt = odbc_exec2( $mssql, $sqlArticulos, __LINE__,__FILE__);
//print_r($stmt);
$tabla = "";$a=0;
while($fila = odbc_fetch_array($stmt)){fb($fila);
  $a++;
  //$date = date_create_from_format('j-M-Y', $fila['fechaprest']);
  //echo date_format($date, 'Y-m-d');
  //echo $fila['fechaprest'];
  
  if(!isset($grupo)||$fila['grupo']<>$grupo){
    $tabla .= "<tr class='info comisionEncabezado'><td colspan='3' style='text-align:left' ><b>$fila[grupo]</b></td></tr>";
    $grupo = $fila['grupo'];
  }
    
  //$tabla.= "<tr><td class='col-md-2'>$fila[CodigoBarra]</td>";
  $tabla .= "<td style='text-align:left' width='5%'>$fila[Codigo]</td>"
          . "<td style='text-align:left' width='75%'>".ucwords(strtolower(utf8_encode($fila['articulo'])))."</td>"
          . "<td style='text-align:right' width='20%'>$ ".sprintf("%.2f",$fila['PrecioPublico'])."</td></tr>";
}
if($a==0){ 
  $tabla .= "<tr class='info comisionEncabezado'><td colspan='3' style='text-align:left' ><b>No hay artículos que reunan los filtros seleccionados.</b></td></tr>";
}

echo $tabla;
