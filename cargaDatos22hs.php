<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
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
      </style>
  </head>
  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
    <div class="container">
        <!-- Cargo por la noche la lectura de lo que dice el CEM mas los aforadores al cierre de las 22hs.-->
		<!-- Example row of columns -->
		<div class="row">
            <div id="ingresaDatos">
                <h2>Ingreso estados al cierre de las 22hs</h2>
				<form name='lecturaAforadores' id='lecturaAforadores' class='well'>
                <input type='hidden' name='turno' value='Noche'/>
                <fieldset>
                <legend>Aforadores mecánicos surtidores</legend>    
                <table class='table' id='aforadores'>
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
                        <tr><th></th><th colspan='3'>Surtidor 1</th><th colspan='3'>Surtidor 2</th><th>Surtidor 3</th><th>Surtidor 4</th><th>Surtidor 5</th><th>Surtidor 6</th><th>Surtidor 7</th></tr>
                        <tr><th></th><th class='alert alert-success'>Euro</th><th class="alert alert-info">Super</th><th class="alert alert-info">Infinia</th><th class='alert alert-success'>Euro</th><th class="alert alert-info">Super</th><th class="alert alert-info">Infinia</th><th class='alert alert-warning'>Ultra</th><th class='alert alert-success'>Euro</th><th class='alert alert-warning'>Ultra</th><th class='alert alert-warning'>Ultra</th><th class='alert alert-success'>Euro</th></tr>
                    </thead>
                    <tbody>
                        <colgroup>
                        <col />
                        <col colspan=3 style="background-color:yellow">
                        <col colspan=3 style="background-color:#abc;">
                        <col colspan=3 style="background-color:#abc;">
                      </colgroup>
                        <tr>
                            <td><?php echo "$fechaCierreAnterior[2]/$fechaCierreAnterior[1]/$fechaCierreAnterior[0]"?></td>
                            <?php foreach($ultimaMedicion as $pico => $aforador){
                                if(strlen($pico)==3)echo "<td><input type='text' id='ant_$pico' name='ant_$pico' class='input-sm form-control anterior' value='$aforador' disabled='disabled'/></td>";
                            }?>
                        </tr>
                        <tr id='ingreso'>
                            <td><input type='text' name='fechaCierre' id='fechaCierre' class="input-sm form-control"  value="<?php echo date("d/m/Y")?>" data-date-format="dd/mm/yyyy" data-plus-as-tab='true'/></td>
                            <?php foreach($ultimaMedicion as $pico => $aforador){
                                if(strlen($pico)==3)echo "<td><input type='number' min='0'  max='1000000' id='$pico' name='$pico' class='input-sm form-control actual' required='required' data-plus-as-tab='true' value='1234556'/></td>";
                            }?>
                        </tr>
                    </tbody>
                </table>
                </fieldset>
                <fieldset>
                <legend>Medición de tanques desde el CEM</legend>  
                <table class='table' id='tanques'>
                    <colgroup>
                        <col class='alert-success'>
                        <col class='alert-warning'>
                        <col class='alert-info'>
                        <col class='alert-success'>
                        <col class='alert-info'>
                        <col class='alert-warning'>
                    </colgroup>
                    <thead><tr><th>1 - Euro</th><th>2 - Ultra</th><th>3 - Super</th><th>4 - Euro</th><th>5 - Infinia</th><th>6 - Ultra</th></tr></thead>
                    <tbody>
                        <tr>
                        <?php for($i=1;$i<=6;$i++){
                           echo "<td><input name='tq$i' id='tq$i' type='number' min='0' max='40000' required='required' class='input-sm form-control tanques' data-plus-as-tab='true'/></td>";
                        }?>
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
            <div id='muestraDatos' style='display:none'>
                <h2>Resumen información ingresada</h2>
            </div>
        </div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
    <script>
		$(document).ready(function() {
            StartDate = new Date("<?php echo "$fechaCierreAnterior[0]/$fechaCierreAnterior[1]/$fechaCierreAnterior[2]"?>");
            testm = new Date("<?php echo "$fechaCierreAnterior[0]/$fechaCierreAnterior[1]/$fechaCierreAnterior[2]"?>");
            testm.setDate(testm.getDate() + 1);
			$('#fechaCierre').datepicker("option", "minDate", testm);
            $('#cargaLectura').click(function() {
                 var opciones= {
                    success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
                    beforeSubmit: validate,
                    url:       'func/cargaLecturaAforadores.php',         // override for form's 'action' attribute 
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
                    
                }
			}
        });
	</script>
  </body>
</html>