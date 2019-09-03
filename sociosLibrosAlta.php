<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$titulo = "Nueva foja de libros";
//ChromePhp::log($arrayYER);
?>
<!DOCTYPE html>
<html lang="es">
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
        <div class="row">
              <?php if(isset($_POST['idSocio'])){
                echo "<h2>Modificación foja</h2>";
              } else {
                echo "<h2>Nueva foja</h2>";
              }?>
              <div class='col-md-5 pull-right'>
                <legend>Últimos eventos</legend>
                <ul><li>Aún sin movimientos</li></ul>
              </div>
              <div class='col-md-7'>
              <form name='ingresoSocio' id='ingresoSocio' class="form-horizontal" enctype="multipart/form-data">
                <?php if(isset($_POST['idSocio'])){
                  echo "<input type='hidden' name='idFoja' value='$_POST[idFoja]'/>";
                }?>
                <fieldset>
                <legend>Datos de la nueva foja</legend>
                  <div class="form-group">
                    <label for="libro" class="col-sm-3 control-label">Libro</label>
                    <div class="col-sm-2 input-group">
                      <input type="text" class="form-control" name='libro' id="libro" placeholder="Libro" required='required' data-plus-as-tab='true' /><span class="input-group-addon glyphicon glyphicon-barcode"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="foja" class="col-sm-3 control-label">Foja</label>
                    <div class="col-sm-2 input-group">
                      <input type="text" class="form-control" name='foja' id="foja" placeholder="Foja" required='required' data-plus-as-tab='true' ><span class="input-group-addon glyphicon glyphicon-user"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="archivo" class="col-sm-3 control-label">Archivo</label>
                    <div class="col-sm-7 input-group">
                      <input type="file"  name='archivo' id="archivo" placeholder="Actas separadas por comas" required='required' data-plus-as-tab='true' >
                    </div>
                  </div>
                </fieldset>
                <fieldset>
                <legend>Actas incluídas</legend>
                  <div class='formActa'>
                  <div class="form-group">
                    <label for="actas" class="col-sm-3 control-label">Acta Nº</label>
                    <div class="col-sm-3 input-group">
                      <input type="text" class="form-control" name='actas[]' id="actas" placeholder=""><span class="input-group-addon glyphicon glyphicon-edit"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="fecha" class="col-sm-3 control-label">Fecha</label>
                    <div class="col-sm-3 input-group">
                      <input type="date" class="form-control" name='fecha[]' id="fecha" placeholder="" min="1959-01-01" max="<?php echo date('Y-m-d')?>"><span class="input-group-addon glyphicon glyphicon-calendar"></span>
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label for="altas" class="col-sm-3 control-label">Altas</label>
                    <div class="col-sm-7 input-group">
                      <input type="text" class="form-control" name='altas[]' id="altas" placeholder="Nuevos socios"  data-plus-as-tab='true' ><span class="input-group-addon glyphicon glyphicon-import"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="bajas" class="col-sm-3 control-label">Bajas</label>
                    <div class="col-sm-7 input-group">
                      <input type="text" class="form-control" name='bajas[]' id="bajas" placeholder="Renuncias o expulsiones"  data-plus-as-tab='true' ><span class="input-group-addon glyphicon glyphicon-export"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="detalle" class="col-sm-3 control-label">Detalle</label>
                    <div class="col-sm-7  input-group">
                      <textarea class="form-control" name='detalle[]' id="detalle" placeholder="Resumen de lo tratado en el acta" data-plus-as-tab='true' required='required' rows='4' cols='80'/></textarea>
                    </div>
                  </div>
                  </div>
              </fieldset>
              <div class="modal-footer">
                <div id='msgbox' class='pull-left'></div>
                <button type="submit" class="btn btn-primary" id='cargaLectura'>Graba</button>
              </div>
            </form>
            </div>
          </div>
          <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
    <script>
    $(document).ready(function() {
      $('#cargaLectura').click(function() {
            var opciones= {
              success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
              //beforeSubmit: validate,
              url:       'func/ajaxSociosLibrosABM.php',         // override for form's 'action' attribute 
              type:      'post',       // 'get' or 'post', override for form's 'method' attribute 
              dataType: 'json'
          };
          $('#ingresoSocio').ajaxForm(opciones);    
      });

      function mostrarRespuesta(responseText){
        $('#botonEnviando').hide();
        $('#cargaLectura').fadeIn();
        $('#msgbox').removeClass('alert alert-danger');
        $('#msgbox').removeClass('alert alert-success');
        if(responseText.status==='error'){
          $('#msgbox').addClass('alert alert-danger');
          $('#msgbox').fadeIn('slow').html(responseText.msg);
        } else {
          $('#msgbox').addClass('alert alert-success');
          $('#msgbox').fadeIn('slow').html(responseText.msg);
          $("form").trigger("reset");
        }
      }
    });
    </script>
  </body>
</html>
