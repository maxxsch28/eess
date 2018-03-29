<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$fechaDesde = '2018-02-01';

// acomodar asientos que son de movistar carga de documentos para que la cuenta sea la de movistar.
// idem repsol
// idem peysse y tabacalera

// acomodar Movistar para que si es un gasto de administracion no lo cambie.

$s['cigarrillos']=array(44, 2, "252, 259, 502, 1601", "48");
$s['TarjetasCelulares']=array(39, 2, "348");
$s['Movistar']=array(8, 3, "323, 229, 392, 95, 362", "3, 20");
$s['Servicios']=array(3, 1, "1, 302, 388, 3, 2");
$s['Papeleria']=array(14, 1, "468, 443, 342, 35, 43, 440, 14, 401, 187, 215, 53, 200, 387, 51, 82, 353, 341, 133, 333, 232, 404");
$s['Correos']=array(13, 1, "85,129");
$s['Fletes']=array(4, 1, "306, 254, 39, 81, 9, 307, 313, 115, 329, 357, 319, 183, 258, 340, 159, 71, 165, 462, 42, 84, 91, 505, 154");
$s['Fletes']=array(4, 1, "9, 39, 81, 84, 154, 254, 306, 340, 462, 505, 357");
$s['Honorarios'] = array(35, 1, "369, 227, 327, 354, 248, 373, 247, 381, 13, 257, 185, 220, 380, 402, 331");
$s['HonorariosLegales'] = array(37,1,"453, 420, 206, 292, 391, 311, 458, 484, 385, 178, 73");
$s['HonorariosContables'] = array(18,1,"231");
$s['GastosVariosAdministracion'] = array(20,1,"111, 260, 213, 509, 17, 349, 148, 504, 375, 224, 344, 334, 467, 270, 449, 493, 119, 456");
$s['Donaciones'] = array(22,1,"89, 457, 181");
$s['GastosVariosComercializacion'] = array(27, 1, "508, 492, 412, 216, 382, 436, 512, 176, 305, 441, 396, 269, 233, 194, 482, 481, 511, 112, 507, 332");
$s['Limpieza'] = array(9, 1, "241, 272");
$s['BDUMantenimiento'] = array(10, 1, "57, 324, 470, 408, 288, 286, 366, 138, 76, 146, 228, 221, 134, 300, 103, 204, 398, 100, 261, 157");
$s['BDUCompra'] = array(11, 1, "22, 114, 48, 26, 489, 479, 475, 418, 378, 235, 208, 118, 386, 487, 65, 88, 163");
$s['Sistemas'] = array(11, 1, "394, 416, 5, 427, 463, 170, 438, 500, 365, 510, 117, 450, 419, 70, 490, 249, 476, 405, 320, 454, 477, 222, 211");
$s['Publicidad'] = array(6, 1, "128, 393, 414, 31, 503, 424, 40, 429, 513, 488, 498, 56, 207, 8, 466, 52, 512");
$s['Vestimenta'] = array(7, 1, "55, 499, 497, 486, 472, 330, 430, 139, 121, 67");
$s['MantenimientoPlaya'] = array(36, 1, "243, 399, 496, 101, 322, 44, 69, 190, 58, 397, 230, 27, 50, 59, 236, 72, 122, 426, 352");
$s['MantenimientoSurtidores'] = array(40, 1, "143, 359, 459, 406");
$s['Obras'] = array(49,1, "377, 54, 433, 225, 106, 202, 179, 30, 447, 460, 425, 83, 471, 437, 356, 94, 432, 289, 461, 109, 104, 92, 174, 116, 347, 336, 448, 172, 483, 210, 74, 308, 304, 46, 277, 442, 451, 431");
$s['SeguridadHigiene'] = array(53, 1, "376, 389, 417, 469, 403");
$s['Representacion'] = array(52, 1, "209, 199, 263, 32, 371, 64, 102, 47, 38, 275, 350, 12, 33, 428, 61, 266, 150, 452, 494, 410, 514, 351, 205, 265");
$s['Campo'] = array(38, 1, "284, 368, 363, 188, 280, 246, 41, 355, 203");
$s['Sistemas'] = array(11, 1, "394, 416, 5, 427, 463, 170, 438, 500, 365, 510, 117, 450, 419, 70, 490, 249, 476, 405, 320, 454, 477, 222, 211");
$s['Seguros'] = array(29, 1, "162, 435, 77, 379");
$s['Cafeteria'] = array(57, 2, "168, 28, 485, 501, 480, 107, 173, 219, 1572");
$s['RepuestosYVarios'] = array(41, 1, "242, 201, 491, 29, 149, 25, 7, 167, 515, 36");// 36, 515
$s['Edenred'] = array(17, 1, "547");// 36, 515




// ASIENTOS
$sql['RepuestosYVariosAsientos'] = "UPDATE dbo.asientosdetalle set IdCuentaContable=543 WHERE IdCuentaContable IN (706, 734) AND idasiento IN (SELECT dbo.asientos.IdAsiento FROM dbo.asientos, dbo.MovimientosPro where  dbo.asientos.fecha>='$fechaDesde' AND dbo.asientos.IdAsiento=dbo.MovimientosPro.IdAsiento AND idProveedor IN ({$s[RepuestosYVarios][2]}))";

$sql['GastosVariosComercializacionAsientos'] = "UPDATE dbo.asientosdetalle set IdCuentaContable=607 WHERE IdCuentaContable IN (706, 734) AND idasiento IN (SELECT dbo.asientos.IdAsiento FROM dbo.asientos, dbo.MovimientosPro where  dbo.asientos.fecha>='$fechaDesde' AND dbo.asientos.IdAsiento=dbo.MovimientosPro.IdAsiento AND idProveedor IN ({$s[GastosVariosComercializacion][2]}))";

$sql['ServiciosAsientos'] = "UPDATE dbo.asientosdetalle set IdCuentaContable=603 WHERE IdCuentaContable IN (706, 734) AND idasiento IN (SELECT dbo.asientos.IdAsiento FROM dbo.asientos, dbo.MovimientosPro where  dbo.asientos.fecha>='$fechaDesde' AND dbo.asientos.IdAsiento=dbo.MovimientosPro.IdAsiento AND idProveedor IN ({$s[Servicios][2]}))";

$sql['PublicidadAsientos'] = "UPDATE dbo.asientosdetalle set IdCuentaContable=623 WHERE IdCuentaContable IN (706, 734) AND idasiento IN (SELECT dbo.asientos.IdAsiento FROM dbo.asientos, dbo.MovimientosPro where  dbo.asientos.fecha>='$fechaDesde' AND dbo.asientos.IdAsiento=dbo.MovimientosPro.IdAsiento AND idProveedor IN ({$s[Publicidad][2]}))";

$sql['MantenimientoPlayaAsientos'] = "UPDATE dbo.asientosdetalle set IdCuentaContable=763 WHERE IdCuentaContable IN (706, 734) AND idasiento IN (SELECT dbo.asientos.IdAsiento FROM dbo.asientos, dbo.MovimientosPro where  dbo.asientos.fecha>='$fechaDesde' AND dbo.asientos.IdAsiento=dbo.MovimientosPro.IdAsiento AND idProveedor IN ({$s[MantenimientoPlaya][2]}))";

$sql['LimpiezaAsientos'] = "UPDATE dbo.asientosdetalle set IdCuentaContable=763 WHERE IdCuentaContable IN (706, 734) AND idasiento IN (SELECT dbo.asientos.IdAsiento FROM dbo.asientos, dbo.MovimientosPro where  dbo.asientos.fecha>='$fechaDesde' AND dbo.asientos.IdAsiento=dbo.MovimientosPro.IdAsiento AND idProveedor IN ({$s[Limpieza][2]}))";

// NETOS
$sql['GastosVariosComercializacionNetos'] = "update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN ({$s['GastosVariosComercializacion'][2]}) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde'); 
update dbo.MovimientosPro set NetoGastos=netoNoGravado, netoNoGravado=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN ({$s['GastosVariosComercializacion'][2]}) and netoNoGravado>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

$sql['FletesNetos'] = "update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN ({$s['Fletes'][2]}) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde'); update dbo.MovimientosPro set NetoGastos=netoNoGravado, netoNoGravado=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN ({$s['Fletes'][2]}) and netoNoGravado>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

$sql['HonorariosNetos'] = "update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN ({$s['Honorarios'][2]}) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde'); update dbo.MovimientosPro set NetoGastos=netoNoGravado, netoNoGravado=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN ({$s['Honorarios'][2]}) and netoNoGravado>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

$sql['SegurosNetos'] = "update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN ({$s['Seguros'][2]}) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde'); update dbo.MovimientosPro set NetoGastos=netoNoGravado, netoNoGravado=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN ({$s['Seguros'][2]}) and netoNoGravado>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

$sql['VestimentaNetos'] = "update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN (55, 499, 497, 486, 472, 330, 430, 139, 121, 67) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde'); update dbo.MovimientosPro set NetoGastos=netoNoGravado, netoNoGravado=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN (55, 499, 497, 486, 472, 330, 430, 139, 121, 67) and netoNoGravado>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

$sql['SistemasNetos'] = "update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN (394, 416, 5, 427, 463, 170, 438, 500, 365, 510, 117, 450, 419, 70, 490, 249, 476, 405, 320, 454, 477, 222) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

$sql['BDUCompraNetos'] = "update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN (22, 114, 48, 26, 489, 479, 475, 418, 378, 235, 208, 118, 386, 487, 65, 88, 163) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

$sql['BDUMantenimientoNetos'] = "update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN (57, 324, 470, 408, 288, 286, 366, 138, 76, 146, 228, 221, 134, 300, 103, 204, 398, 100, 261, 157) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

$sql['LimpiezaNetos']="update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN ({$s['Limpieza'][2]}) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

$sql['PublicidadNetos'] = "update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN (128, 393, 414, 31, 503, 424, 40, 429, 513, 488, 498, 56, 207, 8, 466, 52) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

$sql['SegurosNetos'] = "update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN (162, 435, 77, 379) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde'); update dbo.MovimientosPro set NetoGastos=netoNoGravado, netoNoGravado=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN (162, 435, 77, 379) and netoNoGravado>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

$sql['VestimentaNetos'] = "update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN (55, 499, 497, 486, 472, 330, 430, 139, 121, 67) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde'); update dbo.MovimientosPro set NetoGastos=netoNoGravado, netoNoGravado=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN (55, 499, 497, 486, 472, 330, 430, 139, 121, 67) and netoNoGravado>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

$sql['CorreoNetos'] = "update dbo.movimientosPro set netoNoGravado=NetoGastos, netoGastos=0 where idproveedor=85 and fecha>='$fechaDesde' and netoNoGravado=0 and netoGastos>0 and total=netoGastos;
update dbo.movimientosPro set netoNoGravado=NetoMercaderias, netoMercaderias=0 where idproveedor=85 and fecha>='$fechaDesde' and netoNogravado=0 and netoMercaderias>0 and total=netoMercaderias;";


foreach($s as $rubro => $array){
  if( isset( $array[3] ) ){
    $excepto = " AND idcuentagastos NOT IN ($array[3])";
  } else {
    $excepto = "";
  }
  $sql[$rubro] = "update dbo.MovimientosDetallePro set idcuentagastos=$array[0], idcentrocostos=$array[1] where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in ($array[2]) AND IdCuentaGastos<>$array[0] AND Fecha>='$fechaDesde' ))$excepto";
  //echo $sql[$rubro].'<br><br>';
}











//$sql['Cigarrillos'] = "update dbo.MovimientosDetallePro set idcuentagastos=44, idcentrocostos=2 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (252, 259) AND IdCuentaGastos<>44 AND Fecha>='$fechaDesde' )) ";

//$sql['TarjetasCelulares'] = "update dbo.MovimientosDetallePro set idcuentagastos=39, idcentrocostos=2 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (348) AND IdCuentaGastos<>39 AND Fecha>='$fechaDesde')) ";

$sql['TarjetasCredito'] = "update dbo.MovimientosDetallePro set idcuentagastos=17, idcentrocostos=1 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where IdTipoMovimientoProveedor='ACR' AND IdCuentaGastos<>17 AND Fecha>='$fechaDesde')) ";


////////////////////////////////////////////////////////
// MOVISTAR
$sql['MovistarAsientos'] = "update dbo.AsientosDetalle set IdCuentaContable=754 where IdAsientoDetalle in (select IdAsientoDetalle from dbo.AsientosDetalle, dbo.CuentasContables where IdAsiento in (select IdAsiento from dbo.MovimientosPro, dbo.MovimientosDetallePro where dbo.MovimientosPro.IdMovimientoPro=dbo.MovimientosDetallePro.IdMovimientoPro AND IdProveedor in (323, 229, 392, 95, 362) and Fecha>='$fechaDesde' AND IdCuentaGastos<>3) and dbo.CuentasContables.IdCuentaContable=dbo.AsientosDetalle.IdCuentaContable and IdExpresionContable in (63, 64) and dbo.asientosdetalle.IdCuentaContable<>754)"; // agregado filtro para que las boletas de Movistar propias las mantenga como Gastos de Servicios 20/12/16

$sql['MovistarNeto'] = "update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor in (323, 229, 392, 95, 362) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde')";

//$sql['MovistarFacturas'] = "update dbo.MovimientosDetallePro set IdCuentaGastos=8 where IdMovimientoDetallePro in (select IdMovimientoDetallePro from dbo.MovimientosPro, dbo.MovimientosDetallePro where IdProveedor in (323, 229, 392, 95, 362) and Fecha>='$fechaDesde' and dbo.MovimientosDetallePro.IdMovimientoPro=dbo.MovimientosPro.IdMovimientoPro and IdCuentaGastos<>8);";


///////////////////////////////////////////////////
// YPF
$sql['YPFRendicionesVenta'] = "update dbo.MovimientosDetallePro set idcuentagastos=1, idcentrocostos=1 where IdMovimientoPro in (select idMovimientodetallepro from dbo.MovimientosPro where IdProveedor in (4, 422) AND Fecha>='$fechaDesde' AND IdTipoMovimientoProveedor IN ('RV', 'VP')) "; // BORRAR DESPUES


$sql['YPFAjusteNLP'] = "update dbo.MovimientosDetallePro set idcuentagastos=43, idcentrocostos=1 where IdMovimientoPro in (select idMovimientodetallepro from dbo.MovimientosPro where IdProveedor in (4, 422) AND Fecha>='$fechaDesde' ) ";


$sql['YPFLubricantes'] = "UPDATE dbo.MovimientosPro SET NetoLubricantes=NetoMercaderias, NetoMercaderias=0 WHERE IdProveedor in (4, 422) AND NetoLubricantes=0 AND NetoMercaderias>0 AND idTipoMovimientoProveedor='FAA' AND PuntoVenta IN (2020);

UPDATE dbo.MovimientosPro SET NetoLubricantes=NetoCombustibles, NetoCombustibles=0 WHERE IdProveedor in (4, 422) AND NetoLubricantes=0 AND NetoCombustibles>0 AND idTipoMovimientoProveedor='FAA' AND PuntoVenta IN (2020);

UPDATE dbo.MovimientosDetallePro SET IdCuentaGastos=28 WHERE IdCuentaGastos<>28 AND IdMovimientoPro IN (select IdMovimientoPro from dbo.MovimientosDetallePro where IdMovimientoPro IN (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor in (4, 422) AND NetoLubricantes>0 AND idTipoMovimientoProveedor='FAA' AND PuntoVenta IN (2020)));

UPDATE dbo.MovimientosDetallePro SET IdCuentaGastos=28 WHERE IdCuentaGastos<>28 AND IdMovimientoPro IN (select IdMovimientoPro from dbo.MovimientosDetallePro where Descripcion LIKE ('%LUBES%') AND IdMovimientoPro IN (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor in (4, 422) AND NetoLubricantes>0 AND idTipoMovimientoProveedor='NCA' AND PuntoVenta IN (2023)));


UPDATE dbo.AsientosDetalle SET IdCuentaContable=705 WHERE IdCuentaContable IN (706, 734) AND IdAsiento IN (SELECT IdAsiento FROM dbo.MovimientosPro where IdProveedor in (4, 422) AND NetoLubricantes>0 AND idTipoMovimientoProveedor='FAA' AND PuntoVenta IN (2020));";

$sql['YPFSeguros'] = "UPDATE dbo.MovimientosDetallePro SET IdCuentaGastos=29 WHERE IdCuentaGastos<>29 AND IdMovimientoPro IN (select IdMovimientoPro from dbo.MovimientosDetallePro where Descripcion LIKE ('%seguro%') AND IdMovimientoPro IN (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor in (4, 422) AND NetoGastos>0 AND idTipoMovimientoProveedor IN ('NDA', 'FAA') AND PuntoVenta IN (2023)));";

$sql['YPFServiclub'] = "UPDATE dbo.MovimientosDetallePro SET IdCuentaGastos=21 WHERE IdCuentaGastos<>21 AND IdMovimientoPro IN (select IdMovimientoPro from dbo.MovimientosDetallePro where Descripcion LIKE ('%serviclub%') AND IdMovimientoPro IN (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor in (4, 422) AND NetoGastos>0 AND idTipoMovimientoProveedor IN ('NDA', 'FAA', 'NCA') AND PuntoVenta IN (2142)));";
// actualiza Facturas de lubricantes, tiene que modificar el asiento y el detalle de la factura.


///////////////////////////////////////////////////
//$sql['Servicios'] = "update dbo.MovimientosDetallePro set idcuentagastos=3, idcentrocostos=1 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (1, 302, 388, 3, 2) AND IdCuentaGastos<>3 AND Fecha>='$fechaDesde' )) ";

///////////////////////////////////////////////////
// PAPELERIA Y UTILES
//$sql['Papeleria'] = "update dbo.MovimientosDetallePro set idcuentagastos=14, idcentrocostos=1 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (468, 443, 342, 35, 43, 440, 14, 401, 187, 215, 53, 200, 387, 51, 82, 353, 341, 133, 333, 232, 404) AND IdCuentaGastos<>14 AND Fecha>='$fechaDesde' )) ";


///////////////////////////////////////////////////
// FLETES Y CORREOS
//$sql['Correos'] = "update dbo.MovimientosDetallePro set idcuentagastos=13, idcentrocostos=1 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (85,129) AND IdCuentaGastos<>13 AND Fecha>='$fechaDesde' )) ";

// sql neto no gravado en facturas Correo Argentino


//$sql['Fletes'] = "update dbo.MovimientosDetallePro set idcuentagastos=4 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (306, 254, 39, 81, 9, 307, 313, 115, 329, 357, 319, 183, 258, 340, 159, 71, 165, 462, 42, 84, 91, 505, 154) AND IdCuentaGastos<>4 AND Fecha>='$fechaDesde' )) ";

///////////////////////////////////////////////////
// HONORARIOS
//$sql['Honorarios'] = "update dbo.MovimientosDetallePro set idcuentagastos=35 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (369, 227, 327, 354, 248, 373, 247, 381, 13, 257, 185, 220, 380, 402, 331) AND IdCuentaGastos<>35 AND Fecha>='$fechaDesde' )) ";


//$sql['HonorariosLegales'] = "update dbo.MovimientosDetallePro set idcuentagastos=37 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (453, 420, 206, 292, 391, 311, 458, 484, 385, 178, 73) AND IdCuentaGastos<>37 AND Fecha>='$fechaDesde' )) ";


//$sql['HonorariosContables'] = "update dbo.MovimientosDetallePro set idcuentagastos=18 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (231) AND IdCuentaGastos<>18 AND Fecha>='$fechaDesde' )) ";


///////////////////////////////////////////////
//$sql['GastosVariosAdministracion'] = "update dbo.MovimientosDetallePro set idcuentagastos=20 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (111, 260, 213, 509, 17, 349, 148, 504, 375, 224, 344, 334, 467, 270, 449, 493, 119, 456) AND IdCuentaGastos<>20 AND Fecha>='$fechaDesde' )) ";

//$sql['Donaciones'] = "update dbo.MovimientosDetallePro set idcuentagastos=22 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (89, 457, 181) AND IdCuentaGastos<>22 AND Fecha>='$fechaDesde' )) ";

//$sql['GastosVariosComercializacion'] = "update dbo.MovimientosDetallePro set idcuentagastos=27 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (508, 492, 412, 216, 382, 436, 512, 176, 305, 441, 396, 269, 233, 194, 482, 481, 511, 112, 507, 332) AND IdCuentaGastos<>27 AND Fecha>='$fechaDesde' )) ";



////////////////////////////////////////////////////
//$sql['Limpieza'] = "update dbo.MovimientosDetallePro set IdCuentaGastos=9, idcentrocostos=1 where idMovimientodetallepro in (select idMovimientodetallepro from dbo.MovimientosDetallePro where idmovimientopro in (select idmovimientopro from dbo.movimientospro where idproveedor in (241, 272) AND IdCuentaGastos<>9 AND Fecha>='$fechaDesde'));

//update dbo.MovimientosPro set NetoGastos=NetoMercaderias, NetoMercaderias=0 where IdMovimientoPro in (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor IN (241, 272) and NetoMercaderias>0 and NetoGastos=0 and Fecha>='$fechaDesde')";


////////////////////////////////////////////
// SEGUROS


//////////////////////////////////////////////




// select * from dbo.MovimientosDetallePro where IdMovimientoPro IN (select IdMovimientoPro from dbo.MovimientosPro where IdProveedor in (4, 422) AND NetoLubricantes=0 AND NetoMercaderias>0 AND idTipoMovimientoProveedor='FAA' AND PuntoVenta IN (2020))



// SELECT * FROM dbo.AsientosDetalle WHERE IdAsiento IN (SELECT IdAsiento FROM dbo.MovimientosPro where IdProveedor in (4, 422) AND NetoLubricantes=0 AND NetoMercaderias>0 AND idTipoMovimientoProveedor='FAA' AND PuntoVenta IN (2020))



$sql['Donaciones1'] = "update dbo.MovimientosFac set IdCliente=5233 where IdTipoMovimiento='REM' and IdCliente=1132 and Fecha>='$fechaDesde'";
$sql['Donaciones2'] = "update dbo.MovimientosFac set IdCliente=5235 where IdTipoMovimiento='REM' and IdCliente=4438 and Fecha>='$fechaDesde'";

//$smtpD1 = odbc_exec( $mssql, $sqlDonaciones1);
//$smtpD2 = odbc_exec( $mssql, $sqlDonaciones2);

// sql neto no gravado en acreditaciones





asort($sql);
foreach ($sql as $rubro => $acomoda){
  $stmt = odbc_exec2( $mssql, $acomoda, __LINE__, __FILE__);
  //echo $acomoda;
  if(sqlsrv_num_rows($stmt)>0){
    echo "<b>$rubro</b>: ".sqlsrv_num_rows($stmt)." cambios<br><small>$acomoda</small><br><br>";
  } else {
    echo "<b>$rubro</b>: Sin cambios<br>";
  }
}


?>
