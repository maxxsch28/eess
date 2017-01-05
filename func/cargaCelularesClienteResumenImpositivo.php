<?php
// cargaNuevosCelulares.php
// recibe datos del form y los procesa en mysql
include('../include/inicia.php');
// print_r($_POST);
 // $array=array();
if(isset($_POST['idCliente'])){
  // borro todos los registros de este cliente
  $sqlCliente = "SELECT DISTINCT celular, bb, variosClientes FROM `movistar.clientes` as clientes, `movistar.celulares` as celulares WHERE clientes.idCliente='$_POST[idCliente]' AND clientes.idCliente=celulares.idCliente ORDER BY variosClientes DESC LIMIT 1;";
  //echo $sqlCliente;
  $resultCliente = $mysqli->query($sqlCliente);
  $tabla = "";$a=0;
  $totalIngresado = 0;
  while($fila = $resultCliente->fetch_assoc()){
    $valueCargoFijo = $valueCargoVariable =  $valueCargoBB = $valueImpuestosInternos = $valueOtro =$valueUsoRed=$valueIVAUsoRed= $valueInteresMora =$valueIVAInteresMora =$valueOtro='0';
    $valueIVACargoVariable =$valueIVACargoFijo = $valueIVACargoBB = '';
    if(isset($_POST['idFactura'])){
      $sqlFactura = "SELECT idItemFactura, cargoFijo, cargoBB, cargoVariable, usoRed, interesMora, ingresosBrutos, IVA21, IVA27, IVA10, impuestosInternos, idFacturaRecibida, celular, otros FROM `movistar.facturasitems` WHERE idFacturaRecibida='$_POST[idFactura]' AND celular='$fila[celular]'";
      // echo $sqlFactura;
      $resultFactura = $mysqli->query($sqlFactura);
      $factura = $resultFactura->fetch_assoc();
      
      $valueCargoFijo=$factura['cargoFijo'];
      $valueIVACargoFijo=sprintf("%01.2f",$factura['cargoFijo']*.27);
      $valueCargoVariable=sprintf("%01.2f",$factura['cargoVariable']);
      $valueIVACargoVariable=sprintf("%01.2f",$factura['cargoVariable']*.27);
      $valueCargoBB=sprintf("%01.2f",$factura['cargoBB']);
      $valueIVACargoBB=($factura['cargoBB']<>0)?sprintf("%01.2f",$factura['cargoBB']*.21):'';
      $valueImpuestosInternos=$factura['impuestosInternos'];
      $valueUsoRed=$factura['usoRed'];
      $valueIVAUsoRed=($factura['usoRed']<>0)?$factura['usoRed']*.27:'';
      $valueInteresMora=$factura['interesMora'];
      $valueIVAInteresMora=($factura['interesMora']<>0)?sprintf("%01.2f",$factura['interesMora']*.27):'';
      $valueOtro=sprintf("%01.2f",$factura['otros']);
      $totalIngresado+=$factura['cargoFijo']+$factura['cargoVariable']+$factura['cargoBB']+$factura['usoRed']+$factura['impuestosInternos'];
    }
    if($tabla==''&&$fila['variosClientes']==1)$tabla.="<tr><td colspan='13' class='label label-warning'><b>OJO, esta factura incluye teléfonos de varios clientes. Cargar solo los listados.</b></td></tr>";
    $tabla .= "<tr id='f$fila[celular]'><td>IVA 27%<input type='hidden' name='celular[]' value='$fila[celular]'/></td><td><input type='text' name='cargoFijo[]' id='iva_1_$a' class='input-sm iva form-control' value='$valueCargoFijo' required='required' pattern='[0-9\.]{1,}' maxlength='7' data-plus-as-tab='true'/></td><td><span id='IVA1_$a' class='span' >$valueIVACargoFijo</span></td><td></td><td></td></tr>
    
    <tr><td>IVA 21%</td><td><input type='text' name='cargoBB[]' id='iva_0_$a' class='input-sm iva form-control' value='$valueCargoBB' required='required' pattern='[0-9\.]{1,}' maxlength='7' data-plus-as-tab='true'/></td><td><span id='IVA0_$a' class='span'>$valueIVACargoBB</span></td><td></td><td></td></tr>
    
    <tr><td>Impuestos internos</td><td></td><td></td><td><input type='hidden' name='impuestosInternos[]' id='impInt_$a' class='int' value='$valueImpuestosInternos' pattern='[0-9\.]{1,}' maxlength='5'/><span id='spanImpInt_$a' class='span'>$valueImpuestosInternos</span></td><td class='x'><input type='text' name='totalCalculado' id='totalCalculado' class='input-sm form-control' disabled='disabled'/></td>
    </tr>";
    $a++;
    // <td><input type='text' name='ingresosBrutos[]' class='input-sm' value='0' required='required' pattern='[0-9\.]{1,}' maxlength='5'/></td>
  }
  if(isset($_POST['idFactura'])){
    $tabla.="<input type='hidden' name='total' id='total' value='$totalIngresado'/>";
    $resultFactura->close();
  }
  $resultCliente->close();
  echo $tabla;
}
?>
