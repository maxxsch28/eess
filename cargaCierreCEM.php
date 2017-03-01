<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$titulo = "Carga de datos al cierre 22hs";

$sqlUltimaMedicion = "SELECT * FROM cierres_cem_aforadores ORDER BY fechaCierre DESC LIMIT 1";
if($result = $mysqli->query($sqlUltimaMedicion)){
	$ultimaMedicion = $result->fetch_assoc();
	$result->close();
}
$fechaCierre = explode(' ', $ultimaMedicion['fechaCierre']);
$horaCierreAnterior = $fechaCierre[1];
$fechaCierreAnterior = explode('-', $fechaCierre[0]);
$arrayProductos = array('ns','ni','ed','ud');

$sqlPenultimaMedicionTanques = "SELECT * FROM cierres_cem_tanques WHERE fechaCierre='$fechaCierreAnterior[0]-$fechaCierreAnterior[1]-$fechaCierreAnterior[2] 22:00' and turno='Noche'";
if($result = $mysqli->query($sqlPenultimaMedicionTanques)){
	$penultimaMedicionTanques = $result->fetch_assoc();
	$result->close();
}

$ultimaFechaCargada = new DateTime("$fechaCierreAnterior[0]-$fechaCierreAnterior[1]-$fechaCierreAnterior[2] 22:00:00");
$fecha1 = $ultimaFechaCargada;
//echo 'date before day adding: ' . $ultimaFechaCargada->format('Y-m-d H:i:s'); 
$ultimaFechaCargada->modify('+1 day');
$ultimaFechaCargada2 = $ultimaFechaCargada->format('Y-m-d H:i:s');
$fecha2 = $ultimaFechaCargada2;
$sqlUltimaMedicionTanques = "SELECT * FROM cierres_cem_tanques WHERE fechaCierre='$ultimaFechaCargada2' and turno='Noche'";
if($result = $mysqli->query($sqlUltimaMedicionTanques)){
	$ultimaMedicionTanques = $result->fetch_assoc();
	$result->close();
}


$sqlLitrosCalculados = array();
foreach($tanques as $idTanque => $IdArticulo){
  $sql = "select top 1 FechaHora, IdTanque, IdArticulo, Litros, (Litros - (select sum(cantidad) from dbo.Despachos where Fecha>(select top 1 FechaHora from dbo.TanquesMediciones where LastUpdated<='$ultimaFechaCargada2' and idtanque=$idTanque order by LastUpdated desc) and fecha<='$ultimaFechaCargada2' and IdManguera in (select IdManguera from dbo.Mangueras where IdTanque=$idTanque))) as LitrosTotales from dbo.TanquesMediciones where LastUpdated<='$ultimaFechaCargada2' and idtanque=$idTanque order by LastUpdated desc;";
  $stmt = odbc_exec2($mssql, $sql,__LINE__, __FILE__);
    $sqlLitrosCalculados[$idTanque] = (odbc_fetch_array($stmt));
  settype($sqlLitrosCalculados[$idTanque]['LitrosTotales'], "int"); 
}


$ultimaFechaCargada = new DateTime("$fechaCierreAnterior[0]-$fechaCierreAnterior[1]-$fechaCierreAnterior[2] 00:00:00");
//echo 'date before day adding: ' . $ultimaFechaCargada->format('Y-m-d H:i:s'); 
//$ultimaFechaCargada->modify('+1 day');

// levanto los datos de los tanques a las 22hs del día correspondiente
$sqlTanquesAlCierre = "";



// calculo los litros YER facturados, sirve para comparar contra lo del cierres_cem_aforadores
$sqlYER = "select IdArticulo, SUM(Cantidad) as q from dbo.MovimientosFac, dbo.MovimientosDetalleFac where dbo.MovimientosFac.IdMovimientoFac=dbo.MovimientosDetalleFac.IdMovimientoFac and IdCliente=1283 and Fecha>='".$ultimaFechaCargada->format('Y/m/d')." 22:00:00' AND Fecha<'".$ultimaFechaCargada->modify('+1 day')->format('Y/m/d')." 22:00:00' group by IdArticulo";
$stmt = odbc_exec2($mssql, $sqlYER,__LINE__, __FILE__);

$arrayYER = array();
while($rowYER = odbc_fetch_array($stmt)){
  $arrayYER[$rowYER['IdArticulo']] = $rowYER['q'];
}
fb($sqlYER);
fb($arrayYER);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
      <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php')?>
      <style>
          .table th{text-align: center}
          .container {
                width: 1301px;
            }
          body {
          padding-top: 60px;
          padding-bottom: 40px;
        }
        input {
              text-align:right;
          }
    </style>
    <link href="css/print.css" rel="stylesheet" type="text/css" media="print"/><meta name="viewport" content="width=device-width, initial-scale=1.0">
   </head>
  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
    <div class="container">
        <!-- Cargo por la noche la lectura de lo que dice el CEM mas los aforadores al cierre de las 22hs.-->
		<!-- Example row of columns -->
		<div class="row" id="ingresaDatos">
            <h2>Ingreso estados al cierre de las 22hs</h2>
            <div class='col-md-5'>
		<form name='lecturaAforadores' id='lecturaAforadores' class='well'>
                <input type='hidden' name='turno' value='Noche'/>
                <input type='hidden' name='tipoDeCargaCierreCEM' value='litros'/>
                <fieldset>
                <legend>Volumen expedido por surtidor</legend>    
                <table class='table table-condensed' id='aforadores'>
                    <colgroup>
                        <col />
                        <col span=3 >
                        <col span=3 style="background-color:#bbb;">
                        <col span=1 class=' alert alert-warning'>
                        <col span=1 class=' alert alert-success'>
                        <col span=2 class=' alert alert-warning'>
                        <col span=1 class=' alert alert-success'>
                    </colgroup>
                    <thead>
                        <tr><th width='15%'>Pico</th><th width='22%'><?php echo "$fechaCierreAnterior[2]/$fechaCierreAnterior[1]/$fechaCierreAnterior[0]"?></th><th width='22%'><input type='text' name='fechaCierre' id='fechaCierre' class="input-sm form-control"  value="<?php echo $ultimaFechaCargada->format('d/m/Y')?>" data-date-format="dd/mm/yyyy" data-plus-as-tab='true'/></th><th width='20%'>Litros</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($ultimaMedicion as $pico => $aforador){
                            if(isset($arrayPicos[trim($pico)])){
                            echo "<tr class='alert alert-{$arrayClasses[$pico]}'><td>{$arrayPicos[$pico]}</td>
                            <td><input type='text' id='ant_$pico' name='ant_$pico' class='input-sm form-control anterior' value='$aforador' disabled='disabled'/></td>
                            <td><input type='hidden' name='calc_$pico' id='hcalc_$pico'/><input type='number' min='0'  id='calc_$pico' class='input-sm form-control' disabled='disabled'/></td>
                            <td><input type='number' min='0'  max='2000000' step='any' id='$pico' name='$pico' class='input-sm form-control actual' required='required' data-plus-as-tab='true'/></td></tr>";
                            
                            }
                        }?>
                    </tbody>
                </table>
                </fieldset>
            </div>
            <div class='col-md-2'>
              <h4 class='bg-primary'>Super <span id='totNS' class='pull-right'>&nbsp;0.00</span></h4>
              <h4 class='alert-warning'>Ultra <span id='totUD' class='pull-right'>&nbsp;0.00</span></h4>
              <h4 class='alert-info'>Infinia <span id='totNI' class='pull-right'>&nbsp;0.00</span></h4>
              <h4 class='alert-success'>Euro <span id='totED' class='pull-right'>&nbsp;0.00</span></h4>
              
            </div>
            <div class='col-md-5 well'>
                <fieldset>
                <legend>Medición de tanques desde el CEM</legend>  
                <table class='table' id='tanques'>
                    <thead><tr><th>Tq - Producto</th><th width='22%'>Anterior</th><th width='22%'>Actual</th><th>Dif</th><th>Desvio</th></tr></thead>
                    <tbody>
                        <tr>
                        <?php for($i=1;$i<=6;$i++){
                            echo "<tr class='alert alert-{$classArticulo[$tanques[$i]]}'>
                            <td>$i - {$articulo[$tanques[$i]]}</td>
                            <td><input type='number' min='0'  max='2000000' step='any' id='ant_tq$i' name='ant_tq$i' class='input-sm form-control tanqueAnterior' disabled='disabled' value='{$penultimaMedicionTanques["tq$i"]}'/></td>
                            <td><input name='tq$i' id='tq$i' type='number' min='0' max='40000' required='required' class='input-sm form-control tanques' data-plus-as-tab='true' value='".(($ultimaMedicionTanques["tq$i"]>0)?$ultimaMedicionTanques["tq$i"]:$sqlLitrosCalculados[$i]['LitrosTotales'])."'/></td>
                            <td id='calc_$i'></td>
                            <td id='desvio_$i'></td></tr>";
                        }?>
                        </tr>
                    </tbody>
                </table>
                </fieldset>
                <fieldset>
                <legend>Despachos YER</legend>    
                <table class='table' id='yer'>
                    <colgroup>
                        <col class='bg-primary'>
                        <col class='alert-warning'>
                        <col class='alert-info'>
                        <col class='alert-success'>
                    </colgroup>
                    <thead>
                        <tr><th>1 - Super</th><th>3 - Ultra</th><th>4 - Infinia</th><th>6 - Euro</th></tr>
                    </thead>
                    <tbody>
                        <tr id='ingreso'>
                            <td><input type='number' id='yerNS' name='yerNS' step='any' class='input-sm form-control yer' data-plus-as-tab='true' value='<?php echo (isset($arrayYER[2078])?$arrayYER[2078]:0)?>'/></td>
                            <td><input type='number' id='yerUD' name='yerUD' step='any' class='input-sm form-control yer' data-plus-as-tab='true' value='<?php echo (isset($arrayYER[2069])?$arrayYER[2069]:0)?>'/></td>
                            <td><input type='number' id='yerNI' name='yerNI' step='any' class='input-sm form-control yer' data-plus-as-tab='true' value='<?php echo (isset($arrayYER[2076])?$arrayYER[2076]:0)?>'/></td>
                            <td><input type='number' id='yerED' name='yerED' step='any' class='input-sm form-control yer' data-plus-as-tab='true' value='<?php echo (isset($arrayYER[2068])?$arrayYER[2068]:0)?>'/></td>
                        </tr>
                    </tbody>
                </table>
                </fieldset>
                
                <div id='error' style='display:none; text-align: center' class='alert alert-danger'></div>
				<button class='btn btn-primary btn-big' id='cargaLectura'>Grabar lectura &raquo;</button>
                <div class="form-group" id='botonEnviando' style="display:none">
				<label for='enviandor' class="control-label"></label>
				<div class="controls"> 
					<button class="btn btn-primary btn-lg" >Grabando....</button>
				</div>
                </div>
				</form>
            </div>
        </div>
            <div id='muestraDatos' class='row' style='display:none'>
                <h2>Resumen información ingresada</h2>
            </div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
    <script>
          $(document).ready(function() {
            StartDate = new Date("<?php echo "$fechaCierreAnterior[0]/$fechaCierreAnterior[1]/$fechaCierreAnterior[2]"?>");
            testm = new Date("<?php echo "$fechaCierreAnterior[0]/$fechaCierreAnterior[1]/$fechaCierreAnterior[2]"?>");
            testm.setDate(testm.getDate() + 1);
            
            $('.actual').focusout(function() {
                var pico = $(this).attr('name');
                var actual = +$('#' + pico).val();
                var anterior = +$('#ant_'+pico).val();
                var aforadorActual = actual+anterior;
                $("#calc_" + pico).val(aforadorActual);
                $("#hcalc_" + pico).val(aforadorActual);
                var totED = +1*($('#ed1').val()) + 1*($('#ed2').val()) + 1*($('#ed4').val()) + 1*($('#ed7').val());
                var totNS = +1*($('#ns1').val()) + 1*($('#ns2').val());
                var totNI = +1*($('#ni1').val()) + 1*($('#ni2').val());
                var totUD = +1*($('#ud3').val()) + 1*($('#ud5').val()) + 1*($('#ud6').val());
                
                //var totED = +$('#ed2').val() + totED;
                //var totED = +$('#ed4').val() + totED;
                //var totED = +$('#ed7').val() + totED;
                $("#totNS").html(totNS.toFixed(2));
                $("#totUD").html(totUD.toFixed(2));
                $("#totNI").html(totNI.toFixed(2));
                $("#totED").html(totED.toFixed(2));
                
            })
            $('.tanques').focusout(function(){
                var tanque = $(this).attr('name');
                var actual = +$('#tq' + tanque).val();
                var anterior = +$('#ant_tq'+tanque).val();
                var litrosDespachados = actual-anterior;
                var litrosTq1 = +1*($('#ed4').val()) + 1*($('#ed7').val());
                var litrosTq2 = +1*($('#ud3').val());
                var litrosTq3 = +1*($('#ns1').val()) + 1*($('#ns2').val());
                var litrosTq5 = +1*($('#ni1').val()) + 1*($('#ni2').val());
                var litrosTq4 = +1*($('#ed1').val()) + 1*($('#ed2').val());
                var litrosTq6 = +1*($('#ud5').val()) + 1*($('#ud6').val());
                $('#calc_1').html(($('#ant_tq1').val() - litrosTq1).toFixed(2));
                $('#calc_2').html(($('#ant_tq2').val() - litrosTq2).toFixed(2));
                $('#calc_3').html(($('#ant_tq3').val() - litrosTq3).toFixed(2));
                $('#calc_4').html(($('#ant_tq4').val() - litrosTq4).toFixed(2));
                $('#calc_5').html(($('#ant_tq5').val() - litrosTq5).toFixed(2));
                $('#calc_6').html(($('#ant_tq6').val() - litrosTq6).toFixed(2));
                $('#desvio_1').html(($('#tq1').val() - $('#ant_tq1').val() + litrosTq1).toFixed(2));
                $('#desvio_2').html(($('#tq2').val() - $('#ant_tq2').val() + litrosTq2).toFixed(2));
                $('#desvio_3').html(($('#tq3').val() - $('#ant_tq3').val() + litrosTq3).toFixed(2));
                $('#desvio_4').html(($('#tq4').val() - $('#ant_tq4').val() + litrosTq4).toFixed(2));
                $('#desvio_5').html(($('#tq5').val() - $('#ant_tq5').val() + litrosTq5).toFixed(2));
                $('#desvio_6').html(($('#tq6').val() - $('#ant_tq6').val() + litrosTq6).toFixed(2));
                
            })
            $('#fechaCierre').datepicker("option", "minDate", testm);
            $('#cargaLectura').click(function() {
                 var opciones= {
                    success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
                    beforeSubmit: validate,
                    url:       'func/cargaLecturaAforadores2.php',         // override for form's 'action' attribute 
                    type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
                };
                $('#lecturaAforadores').ajaxForm(opciones);    
            });
            function validate() {
                $('.actual').removeClass('alert-danger alert has-error');
                $(".actual").each(function() {
                    //alert($(this).val());
                    //anterior
                    var anterior = $('#ant_'+this.name).val();
                    if(this.value-anterior<0){
                        //alert(this.value-anterior);
                        //error
                        $(this).addClass('alert-danger');
                        $('#error').fadeIn('slow').html('Compruebe valor ingresado, debe ser mayor a lectura anterior');
                        return false;
                    }
                });
                $('#cargaLectura').hide();
                $('#botonEnviando').show();
                $('#error').hide().html('');
            }
            function mostrarRespuesta(responseText){
                $('#botonEnviando').hide();
                $('#cargaLectura').fadeIn();
                if(responseText==='error'){
                    $('#fechaCierre').addClass('alert-danger');
                    if($('#error').html()===''){
                        $('#error').fadeIn('slow').html('Ya se ingresaron valores para esa fecha<br/>Verifique los recuadros resaltados en ROJO');
                    } else {
                        $('#error').fadeIn('slow').html('Ya se ingresaron valores para esa fecha<br/>Compruebe valor ingresado, debe ser mayor a lectura anterior<br/>Verifique los recuadros resaltados en ROJO');
                    }
                } else {
                    $('#error').hide().html('');
                    $('#fechaCierre').removeClass('alert-danger');
                    $('#ingreso td input.actual').val('');
                    $('#ingresaDatos').hide('slow');
                    $('#muestraDatos').show('fast');
                    //$('#ingresaDatos').html(responseText).show();
                }
			}
        });
	</script>
  </body>
</html>
