<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');


// lev
$sqlUltimaMedicion = "SELECT * FROM aforadores ORDER BY fechaMedicion DESC LIMIT 1";
$tabla="";
if($result = $mysqli->query($sqlUltimaMedicion)){
	//idOrden 	op 	fechaPedido Descendente 	fechaDespacho 	idPedido 	idOrden 	idArticulo 	litrosPedidos 	litrosEntregados 	idEstado 	idOrden 	fechaEstado 	estado
	/* free result set */
	$ultimaMedicion = $result->fetch_assoc();
	$result->close();
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Ingreso estado aforadores mecánicos | YPF</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
  </head>

  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
	<div class="container">
		<div class='row'>
			<div class="col-md-6">
				<h2>Ingreso estado aforadores mecánicos</h2>
				<form name='lecturaAforadores' id='lecturaAforadores' class='form-horizontal well'>
				<input type='hidden' name='inputOculto' id='inputOculto' value=''/>
				<?php
				$surtidor = 0;
				foreach($ultimaMedicion as $aforador => $medicionAnterior){
					if(!isset($encabezado)){$encabezado=1;echo"<legend>Medición anterior: $medicionAnterior</legend>";}
					else{
						if($surtidor<>$surtidores[$aforador]){
							$surtidor = $surtidores[$aforador];
							if($surtidor>1)echo"</fieldset>";
							echo "<fieldset><legend>Surtidor $surtidor</legend>";
						};
						?>
						<div class="form-group">
							<label class="control-label" for="<?php echo $aforador?>"><?php echo $productoPorSurtidor[$aforador].' <b>'.substr($aforador,-1)?></b></label>
							<div class="controls"><div class="input-group">
								<input type="text" id="<?php echo $aforador?>" name="<?php echo $aforador?>" placeholder="lectura" class='input-sm' required="required" pattern="[0-9]{3,10}" /><span class="input-group-addon"><?php echo $medicionAnterior?></span>
							</div></div>
						  </div>
						<?php
					}
				}
				?>
				</fieldset>
				<button class='btn btn-primary btn-big' id='cargaLectura'>Cargar lectura &raquo;</button>
				</form>
			</div>
			<div class='col-md-6'>
				<h2>Stock tanques calculado</h2>
				<div id='stockTanques'>
				<p class='lead'>Una vez que se carguen todos los aforadores y se apriete el botón de "Cargar lectura" en este recuadro aparecerá el estado de los tanques calculado por diferencia entre el stock final del turno anterior, la suma de las descargas de cisternas que haya habido y la resta de todos los despachos del turno.</p><p class='lead'><b>Ese valor sería para cargar en el CEM.</b></p>
				</div>
			</div>
		</div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
	</div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
		$(document).ready(function() {
			$("#c").click(function() {
				//event.preventDefault();
				$(this).addClass('recibiendo').html('&raquo;');
				$.get('func/cargaLecturaAforadores.php?idOrden='+$(this).attr('id'), function(data) {
					$('#tanques').show();
					$('#msgBox').hide();
					$('#tanques tbody').html(data).fadeIn();
					$(".asignacion").each(function() {
						var that = this; // fix a reference to the <input> element selected
						$(this).keyup(function(){
							newSum.call(that); // pass in a context for newsum():
						});
					});
				});
				var borrar = $('#idOrden').val().substr(1);
				$('#op'+borrar).block({ message: null }); 
				return false;
			});
			
			function newSum() {
				var sum = 0;
				//sirvepara ir sumando las cantidades asignadas y que no deje pasar si no se asigno el total
				$('#asignaTanques tbody').find('input:text').each( function(){
					if(!isNaN(this.value)){
						sum += parseFloat(this.value);}
				});
				$('#totalCalculado').val(sum);
			}
			
			var opciones= {
                beforeSubmit: validate, //funcion que se ejecuta antes de enviar el form
                success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
				url:       'func/cargaLecturaAforadores.php',         // override for form's 'action' attribute 
				type:      'post'       // 'get' or 'post', override for form's 'method' attribute
            };
             //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
            $('#lecturaAforadores').ajaxForm(opciones) ; 
 
             //lugar donde defino las funciones que utilizo dentro de "opciones"
            function mostrarLoader(){
                $('#loader_gif').fadeIn("slow"); //muestro el loader de ajax
            };
			
			function validate(formData, jqForm, options) { 
				// valido que todos los aforadores esten ingresados
				var acumulado = 0;
				$(".input-sm").each(function() {
					//add only if the value is number
					if(isNaN(this.value) || this.value.length<3) {
						alert('Por favor ingresar todos los datos pedidos');
						return false;
					}
				});
			}

            function mostrarRespuesta(responseText){
                $("#loader_gif").fadeOut("slow"); // Hago desaparecer el loader de ajax
				$('#stockTanques').html(responseText);
				$('#lecturaAforadores').block();
				// eliminar bloque en box izquierdo
				// mejorar efecto de feedback
				// eliminar opcion dedistribuir el mismo camion de nuevo
			};
		});
		
	</script>
	</body>
</html>
<!--
// definimos las opciones del plugin AJAX FORM
            

-->