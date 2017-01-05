<?php
if(substr($_SERVER['REMOTE_ADDR'], 0, 10)=='192.168.1.'){
    $nivelRequerido=5;
}
include('include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$titulo = "Carga de datos al cierre 22hs";

$sqlUltimaMedicion = "SELECT * FROM cierres_aforadores ORDER BY fechaCierre DESC LIMIT 1";
if($result = $mysqli->query($sqlUltimaMedicion)){
	$ultimaMedicion = $result->fetch_assoc();
	$result->close();
}
$fechaCierre = explode(' ', $ultimaMedicion['fechaCierre']);
$horaCierreAnterior = $fechaCierre[1];
$fechaCierreAnterior = explode('-', $fechaCierre[0]);
$arrayProductos = array('ns','ni','ed','ud');

?>
<!DOCTYPE html>
<html lang="es">
  <head>
      <?php include ('/include/header.php')?>
      <style>
          .table th{text-align: center}
          .container {
                width: 1301px;
            }
      </style>
  </head>
  <body>
	<?php include('include/menuSuperior.php') ?>
    <div class="container">
        <!-- Cargo por la noche la lectura de lo que dice el CEM mas los aforadores al cierre de las 22hs.-->
		<!-- Example row of columns -->
		<div class="row" id="ingresaDatos">
                <h2>Ingreso estados al inicio del día 6 hs</h2>
           
				<form name='lecturaAforadores' id='lecturaAforadores' class='well'>
                    <div>
                <input type='hidden' name='turno' value='Mañana'/>
                <div class='col-md-6'>
                <fieldset>
                <legend>Aforadores mecánicos por surtidor</legend>    
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
                        <tr><th rowspan="2">Pico</th><th>Anterior</th><th>Actual</th><th rowspan="2">Lts</th></tr>
                        <tr><th><?php echo "$fechaCierreAnterior[2]/$fechaCierreAnterior[1]/$fechaCierreAnterior[0]"?></th><th><input type='text' name='fechaCierre' id='fechaCierre' class="input-sm form-control"  value="<?php echo date("d/m/Y")?>" data-date-format="dd/mm/yyyy" data-plus-as-tab='true'/></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($ultimaMedicion as $pico => $aforador){
                            if(isset($arrayPicos[trim($pico)])){
                            echo "<tr class='alert alert-{$arrayClasses[$pico]}'><td>{$arrayPicos[$pico]}</td><td><input type='text' id='ant_$pico' name='ant_$pico' class='input-sm form-control anterior' value='$aforador' disabled='disabled'/></td><td><input type='number' min='0'  max='1000000' step='any' id='$pico' name='$pico' class='input-sm form-control actual' required='required' data-plus-as-tab='true'/></td><td id='calc_$pico'></td></tr>";
                            }
                        }?>
                    </tbody>
                </table>
                </fieldset>
                </div>
                <div class='col-md-6'>
                <fieldset>
                <legend>Medición de tanques desde el CEM</legend>  
                <table class='table' id='tanques'>
                    <thead><tr><th>Tq - Producto</th><th>Anterior</th><th>Actual</th><th>Dif</th></tr></thead>
                    <tbody>
                        <tr>
                        <?php for($i=1;$i<=6;$i++){
                            echo "<tr class='alert alert-{$classArticulo[$tanques[$i]]}'><td>$i - {$articulo[$tanques[$i]]}</td><td></td><td><input name='tq$i' id='tq$i' type='number' min='0' max='40000' required='required' class='input-sm form-control tanques' data-plus-as-tab='true'/></td><td id='calc_$i'></td></tr>";
                        }?>
                        </tr>
                    </tbody>
                </table>
                </fieldset>
                </div>
                    </div>
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
            <div id='muestraDatos' class='row' style='display:none'>
                <h2>Resumen información ingresada</h2>
            </div>
        <?php include ('include/footer.php')?>
    </div> <!-- /container -->
	<?php include('include/termina.php');?>
    <script>
		$(document).ready(function() {
            StartDate = new Date("<?php echo "$fechaCierreAnterior[0]/$fechaCierreAnterior[1]/$fechaCierreAnterior[2]"?>");
            testm = new Date("<?php echo "$fechaCierreAnterior[0]/$fechaCierreAnterior[1]/$fechaCierreAnterior[2]"?>");
            testm.setDate(testm.getDate() + 1);
            
            $('.actual').focusout(function() {
                var pico = $(this).attr('name');
                var actual = $('#' + pico).val();
                var anterior = $('#ant_'+pico).val();
                $( "#calc_" + pico).html((actual-anterior).toFixed(2));
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