<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
//print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';

$soloExternos = (isset($_POST['soloExternos'])&&$_POST['soloExternos']<>0)?" AND tipoviaje=$_POST[soloExternos]":"";
if(isset($_POST['mes'])){
  $periodo = "";
  if(strlen($_POST['mes'])>4){
    $anio = substr($_POST['mes'], 0, 4);
    $mes = substr($_POST['mes'], 5, 2);
    $incluyeMes = "AND datepart(month, Partes.Salida)='$mes'";
  } else {
    $anio = substr($_POST['mes'], 0, 4);
    $incluyeMes ="";
  }
  $sqlClientes = "SELECT Parttram.LiquidarCh, Partes.APagar_Fle, Partes.TipoViaje, parttram.Salida, parttram.SalidaHora, Partes.Sucursal_E, Partes.Parte, parttram.Loc_Origen as Loc_Origen,  Parttram.Loc_Desti as Loc_Desti, Parttram.Chofer, Choferes.Nombre Collate SQL_Latin1_General_CP1253_CI_AI as Cho_Nombre, parttram.Fletero, Fleteros.Nombre Collate SQL_Latin1_General_CP1253_CI_AI as Fle_Nombre, Partes.Cumplido, Partes.Rendido, Partes.Anulado, ParteVta.SucuPrefac, ParteVta.NumePrefac, ParteVta.Importe as ImpVta, ParteVta.Cliente, dbo.clientes.nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombreCliente FROM dbo.clientes, Partes INNER JOIN PARTTRAM ON partes.sucursal_e = parttram.sucursal AND partes.parte = parttram.numero INNER JOIN Choferes ON parttram.Chofer = Choferes.Codigo INNER JOIN Fleteros ON parttram.Fletero = Fleteros.Fletero INNER JOIN Equipos ON parttram.Equipo = Equipos.Equipo INNER JOIN TipoEqui ON parttram.Tipo_Equi = TipoEqui.Codigo LEFT JOIN Equipos as Acoplado ON parttram.Acoplado = Acoplado.Equipo LEFT JOIN Ciudades Origen  ON PartTram.Origen = Origen.Codigo  LEFT JOIN Ciudades Destino ON PartTram.Destino = Destino.Codigo LEFT JOIN Negocios ON Partes.Negocio = Negocios.Codigo LEFT JOIN TipoPedi ON Partes.TipoViaje  = TipoPedi.Codigo LEFT JOIN TipoServ ON Partes.Tipo_Servi = TipoServ.Codigo LEFT JOIN AcomPart ON Partes.Sucursal_E = AcomPart.Sucursal_E AND Partes.Parte = AcomPart.Parte LEFT JOIN Choferes AS Acompa ON AcomPart.Chofer = Acompa.Codigo INNER JOIN ParteVta ON Partes.Sucursal_E = ParteVta.Sucursal_E AND Partes.Parte = ParteVta.Parte WHERE Partes.Anulado = 0 AND datepart(year, Partes.Salida)='$anio' $incluyeMes and rendtram=1 $soloExternos AND ParteVta.Cliente=dbo.clientes.codigo ORDER BY fle_nombre, tipoviaje, parttram.Salida, parttram.SalidaHora, Partes.Sucursal_E, Partes.Parte";//AND clientes.idCliente=1641
} 
//ChromePhp::log($sqlClientes);
ChromePhp::log($_SESSION['transporte_tipos_comisiones']);
$stmt = odbc_exec2($mssql2, $sqlClientes, __LINE__, __FILE__);
$tabla = "";
$a = $totalB = $totalA = $cantidadFacturas = $cantidadClientes = $totalACapitalizar = 0;
$comision = array();
$totalAComisionar = array();
$numeroResultados = sqlsrv_num_rows($stmt);
while($fila = sqlsrv_fetch_array($stmt)){
  if(!isset($idSocio)){
    $idSocio = $fila['Fletero'];
    $socio = $fila['Fle_Nombre'];
    $comision=array();
    $comision[$idSocio]=array();
    $cantidadClientes++;
  }
  if($fila['Fletero']<>$idSocio){
    $tablaEncabezado = "<tr class='info comisionEncabezado'><td>$idSocio - <b>".strtoupper(trim(utf8_encode($socio)))."</b></td><td colspan='4' style='text-align:right'><b>";
    $a=0;
    $totalAFacturar = 0;
    foreach($comision[$idSocio] as $alicuota => $monto){
      $totalAFacturar += $monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100;
      $a++;
      // en el pie de cada fletero diferencia los totales por distinto tipo de comisiones
      if($a==1){
        $tablaEncabezado .= " {$_SESSION['transporte_tipos_comisiones'][$alicuota]}: $".number_format($monto, 2, ',', '.')." || Facturar $".number_format($monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100, 2, ',', '.')."</b>";
        if($alicuota==1||$alicuota==5){
          $tablaEncabezado .= "<br/><b>1% A capitalizar $".number_format($monto*.01, 2, ',', '.')."</b></td></tr>";
          $totalACapitalizar = $totalACapitalizar + $monto*.01;
        }
        $tablaEncabezado.="</td></tr>";
      } else {
        $tablaEncabezado .= "<tr class='info comisionEncabezado'><td></td><td colspan='4' style='text-align:right'><b> {$_SESSION['transporte_tipos_comisiones'][$alicuota]}: $".number_format($monto, 2, ',', '.')." || Facturar $".number_format($monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100, 2, ',', '.')."</b></td></tr>";
      }
    }
    if($a>1){
      // totaliza comisiones por fletero
      $tablaEncabezado .= "<tr class='info comisionEncabezado'><td></td><td colspan='4' style='text-align:right'><b> TOTAL A FACTURAR SOCIO: $".sprintf("%.2f",$totalAFacturar)."</b></td></tr>";
    }
    $idSocio = $fila['Fletero'];
    $comision=array();
    $comision[$idSocio]=array();
    $socio = $fila['Fle_Nombre'];
    $codigosocio = $fila['Fletero'];
    $cantidadClientes++;
  }
  if(isset($tablaEncabezado)){
    $tabla.=$tablaEncabezado;
    unset($tablaEncabezado);
  }
  //Salida	SalidaHora	Sucursal_E	Parte	Tramo	Origen	nom_Origen	Loc_Origen	ProvOrigen	Destino	Nom_Destin	Loc_Desti	Fletero	Fle_Nombre	Kilometros	TipoViaje	TpV_Nombre	APagar_Fle	Pagado_Fle	LiquidarCh	Cumplido	Rendido	Anulado	NomOrigen	NomDestino	ImpVta	Cliente

  $importe = ($fila['APagar_Fle']>0&&$fila['APagar_Fle']==$fila['ImpVta'])?$fila['APagar_Fle']:(($fila['LiquidarCh']>0)?$fila['LiquidarCh']:$fila['ImpVta']);
  //$importe = ($fila['APagar_Fle']>0)?$fila['APagar_Fle']:(($fila['LiquidarCh']>0)?$fila['LiquidarCh']:$fila['ImpVta']);
  //$importe = ($fila['ImpVta']>0)?$fila['ImpVta']:(($fila['LiquidarCh']>0)?$fila['LiquidarCh']:$fila['ImpVta']);
  $tipoComision = ($fila['TipoViaje']==0)?1:$fila['TipoViaje'];
  
  if(!isset($comision[$idSocio][$tipoComision])){
    $comision[$idSocio][$tipoComision]=0;
  }
  
  @$totalAComisionar[$tipoComision] += $importe;
  $comision[$idSocio][$tipoComision] += $importe;
  
  $tabla.= "<tr class='viaje'><td class='no'> > ($fila[Chofer]) - ".ucwords(strtolower(trim(utf8_encode($fila['Cho_Nombre']))))."</td><td>$fila[Loc_Origen] -> $fila[Loc_Desti] (<span class='no2'>Viaje </span>$fila[Sucursal_E]-$fila[Parte])</td><td style='text-align:right'>$ ".number_format($importe, 2, ',', '.')."</td><td><small>".$_SESSION['transporte_tipos_comisiones'][$tipoComision]."</small></td><td>($fila[Cliente]) <small>".trim($fila['nombreCliente'])."</small></td></tr>";
  $cantidadFacturas++;
      
}
if($tabla==""){
  $tabla="<tr><td colspan='5' class='label-info center'>NO HAY FACTURAS CARGADAS EN ESTE PERIODO</td></tr>";
} elseif(!isset($limit)) {
  @$tablaEncabezado = "<tr class='info comisionEncabezado'><td>$codigosocio - <b>".strtoupper(utf8_encode($socio))."</b></td><td colspan='4' style='text-align:right'><b>";
  $a=0;$totalAFacturar = 0;
  foreach($comision[$idSocio] as $alicuota => $monto){
    $a++;
    $totalAFacturar += $monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100;
    if($a==1){
      $tablaEncabezado .= " {$_SESSION['transporte_tipos_comisiones'][$alicuota]}: $".number_format($monto, 2, ',', '.')." ||  Facturar $".number_format($monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100, 2, ',', '.')."</b>";
      if($alicuota==1||$alicuota==5){
        $tablaEncabezado .= "<br/><b>1% A capitalizar $".number_format($monto*.01, 2, ',', '.')."</b>";
        $totalACapitalizar = $totalACapitalizar + $monto*.01;
      }
      $tablaEncabezado .= "</td></tr>";
    }  else {
      $tablaEncabezado .= "<tr class='info comisionEncabezado'><td></td><td colspan='4' style='text-align:right'><b> {$_SESSION['transporte_tipos_comisiones'][$alicuota]}: $".number_format($monto, 2, ',', '.')." || Facturar $".number_format($monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100, 2, ',', '.')."</b></td></tr>";
    }
    
  }
  if($a>1){
    // totaliza comisiones por fletero
    $tablaEncabezado .= "<tr class='info comisionEncabezado'><td></td><td colspan='4' style='text-align:right'><b> TOTAL A FACTURAR SOCIO: $".number_format($totalAFacturar, 2, ',', '.')."</b></td></tr>";
  }
  $tabla.="$tablaEncabezado<tr class='warning'><td colspan='1'>$cantidadClientes Fleteros, $cantidadFacturas Viajes</td><td colspan='4'><u>$numeroResultados - Total comisiones mensuales</u>:</td></tr>";
  foreach($totalAComisionar as $alicuota => $monto){
    $tabla .= "<tr class='warning'><td colspan='1'></td><td colspan='4'>{$_SESSION['transporte_tipos_comisiones'][$alicuota]} \$".number_format($monto, 2, ',', '.')." <b>\$".number_format($monto*$_SESSION['transporte_alicuotas_comisiones'][$alicuota]/100, 2, ',', '.')."</b></td></tr>";
    if($alicuota==1||$alicuota==5){
      $tabla .= "<tr class='warning'><td colspan='1'></td><td colspan='4'>Aportes a capitalizar, sobre \$".number_format($monto, 2, ',', '.')." <b>\$".number_format($totalACapitalizar, 2, ',', '.')." <b></b></td></tr>";
    }
  }
}
echo $tabla;
 // $sqlClientes = "SELECT parttram.Salida, parttram.SalidaHora, Partes.Sucursal_E, Partes.Parte, Parttram.Tramo, parttram.Origen , parttram.nom_Origen , parttram.Loc_Origen  as Loc_Origen, parttram.ProvOrigen, Parttram.Destino, Parttram.Nom_Destin  , Parttram.Loc_Desti  as Loc_Desti, Parttram.Chofer, Choferes.Nombre  as Cho_Nombre, parttram.Fletero, Fleteros.Nombre  as Fle_Nombre, parttram.RegimenGan, AcomPart.Chofer as Acompanante, Acompa.Nombre  as Aco_Nombre, parttram.Equipo, Equipos.Patente as Equ_Patent, Partes.Tara, Partes.Tara_Llega, Equipos.Patente2 as Equ_Paten2, parttram.Acoplado, Acoplado.Patente as Aco_Patent, parttram.tipo_Equi as TipoEquipo, Partes.Tipo_Servi, TipoServ.Detalle  As DetServ , TipoEqui.Detalle  as TpE_Detall, Partes.Kilometros, Partes.Adelanto, Partes.AdelEfec, Partes.AdelGasOil, Partes.AdelOtros, Partes.AdelChof, Partes.Negocio, Negocios.Nombre as Neg_Nombre, Partes.TipoViaje, TipoPedi.Nombre as TpV_Nombre, Partes.APagar_Fle, Partes.Pagado_Fle, Partes.Imp_Peajes, Partes.Pagado_Pea, Partes.PorcDescPa, Parttram.LiquidarCh, Parttram.SucuLiqCho, Parttram.LiquiChofe, Partes.SucuRendi, Partes.Rendicion, Partes.Observacio, Partes.SucuOrdCar, Partes.NumeOrdCar, Partes.Cumplido, Partes.Rendido, Partes.Anulado, PREFPVTA.Comprobant as Compr_Pref, PREFPVTA.TipFactu, PREFPVTA.SucFactu, PREFPVTA.NumFactu, ParteVta.SucuPrefac, ParteVta.NumePrefac, CASE WHEN Parttram.nume_cumtr=0 THEN 'No' ELSE 'Si' END as nume_cumtr,CASE WHEN Parttram.nume_rentr=0 THEN 'No' ELSE 'Si' END as nume_rentr, Partes.FechaIngre, Partes.HoraIngre, Partes.SucTranGlo, Partes.IdTranGlob, Partes.IdEmpresa, Origen.Localidad NomOrigen, Destino.Localidad NomDestino, Partes.CantEnvios, Partes.Unidades, Partes.Kilos, Partes.KmRecorri, Partes.Declarado, ParteVta.Importe as ImpVta, ParteVta.Cliente, dbo.clientes.nombre  FROM dbo.clientes, Partes INNER JOIN PARTTRAM ON partes.sucursal_e = parttram.sucursal AND partes.parte = parttram.numero INNER JOIN Choferes ON parttram.Chofer = Choferes.Codigo INNER JOIN Fleteros ON parttram.Fletero = Fleteros.Fletero INNER JOIN Equipos ON parttram.Equipo = Equipos.Equipo INNER JOIN TipoEqui ON parttram.Tipo_Equi = TipoEqui.Codigo LEFT JOIN Equipos as Acoplado ON parttram.Acoplado = Acoplado.Equipo LEFT JOIN Ciudades Origen  ON PartTram.Origen = Origen.Codigo  LEFT JOIN Ciudades Destino ON PartTram.Destino = Destino.Codigo LEFT JOIN Negocios ON Partes.Negocio = Negocios.Codigo LEFT JOIN TipoPedi ON Partes.TipoViaje  = TipoPedi.Codigo LEFT JOIN TipoServ ON Partes.Tipo_Servi = TipoServ.Codigo LEFT JOIN AcomPart ON Partes.Sucursal_E = AcomPart.Sucursal_E AND Partes.Parte = AcomPart.Parte LEFT JOIN Choferes AS Acompa ON AcomPart.Chofer = Acompa.Codigo  INNER JOIN ParteVta ON Partes.Sucursal_E = ParteVta.Sucursal_E AND Partes.Parte = ParteVta.Parte LEFT JOIN PREFCVIA PREFPVTA ON PREFPVTA.Sucursal = ParteVta.SucuPrefac AND PREFPVTA.Numero = ParteVta.NumePrefac WHERE Partes.Anulado = 0 AND datepart(year, Partes.Salida)='$anio' AND datepart(month, Partes.Salida)='$mes' and rendtram=1 $soloExternos AND ParteVta.Cliente=dbo.clientes.codigo ORDER BY fle_nombre, tipoviaje, parttram.Salida, parttram.SalidaHora, Partes.Sucursal_E, Partes.Parte";//AND clientes.idCliente=1641

?>
