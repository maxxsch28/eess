<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$titulo = "Nuevo acta";
//ChromePhp::log($arrayYER);
if(isset($_GET['idActa'])&&is_numeric($_GET['idActa'])){
  $idActa = $_GET['idActa'];
  // levanto datos de la foja y de las actas
  $sql1 = "SELECT idActa, libro, foja FROM dbo.[socios.libros] WHERE idActa=$idActa";
  $stmt = odbc_exec2( $mssql4, $sql1, __LINE__, __FILE__);
  $datosFoja = sqlsrv_fetch_array($stmt);
  
  $sql2 = "SELECT idActa, idActa, acta, fecha, altas, bajas, detalle FROM dbo.[socios.actas] WHERE idActa=$idActa";
  $stmt2 = odbc_exec2( $mssql4, $sql2, __LINE__, __FILE__);
  ChromePhp::log($sql2);
  
} else if (isset($_GET['idActa'])&&!is_integer($_GET['idActa'])){
  unset($_GET['idActa']);
}



// Si no existe el slector de libros lo crea, te deja preestablecido el último usado.
if(!isset($_SESSION['selectorLibro'])){
    $sql = "SELECT * FROM dbo.[socios.libros2] order by tipo ASC, numeroLibro ASC;";
    $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);
    $selectorLibro = "";
    $_SESSION['libros'] = array();
    while($row = sqlsrv_fetch_array($stmt)){
      $_SESSION['libros'][$row['idLibro']] = ucfirst($row['tipo'])." Nº$row[numeroLibro]";
      $selected = (isset($_SESSION['idLibro'])&&$row['idLibro']==$_SESSION['idLibro'])?" selected='selected'":'';
      $selectorLibro .="<option value='$row[idLibro]' $selected>".ucfirst($row['tipo'])." Nº$row[numeroLibro]</option>";
    }
    $_SESSION['selectorLibro'] = $selectorLibro;
  }








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
              <?php if(isset($idActa)){
                echo "<h2>Modificación acta</h2>";
              } else {
                echo "<h2>Nueva acta</h2>";
              }?>
              <div class='col-md-5 pull-right'>
                <legend>Últimas actas</legend>
                <ul id='ultimas'></ul>
              </div>
              <div class='col-md-7'>
              <form name='ingresoSocio' id='ingresoSocio' class="form-horizontal" enctype="multipart/form-data">
                <?php if(isset($idActa)){
                  echo "<input type='hidden' name='idActa' value='$idActa'/>";
                }?>
                <fieldset>
                <?php if(isset($idActa)){?>
                  <legend>Visualización</legend>
                  <div>

                    <object data="<?php echo "/pdf/$idActa.pdf"?>" type="application/pdf" width="728" height="600"></object>

                  </div>
                <?php }?>
                <fieldset id='actas'>
                <legend>Datos registrales</legend>
                <?php if(isset($idActa)){
                  $a=1;
                  while($datosActa = sqlsrv_fetch_array($stmt2)){?>
                  <div class='formActa' id='formActa<?php echo $a?>'>
                    <div class='botonera pull-right'>
                      <span class='btn-add glyphicon glyphicon-plus'>&nbsp;</span>
                      <span class='btn-remove glyphicon-minus'>&nbsp;</span>
                    </div>
                    <div class="form-group">
                      <label for="acta" class="col-sm-3 control-label">Acta Nº</label>
                      <div class="col-sm-3 input-group">
                        <input type="number" class="form-control" name='acta' id="acta" placeholder="" data-plus-as-tab='true' value='<?php echo $datosActa['acta']?>'><span class="input-group-addon glyphicon glyphicon-edit"></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="fecha" class="col-sm-3 control-label">Fecha</label>
                      <div class="col-sm-3 input-group">
                        <input type="date" class="form-control" name='fecha' id="fecha" placeholder="" min="1959-01-01" max="<?php echo date('Y-m-d')?>" data-plus-as-tab='true' value='<?php echo $datosActa['fecha']->format('Y-m-d')?>'><span class="input-group-addon glyphicon glyphicon-calendar"></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="fojaFirmas" class="col-sm-3 control-label">Asistencia</label>
                      <div class="col-sm-3 input-group">
                        <input type="number" class="form-control" name='fojaFirmas' id="fojaFirmas" placeholder="" data-plus-as-tab='true' value='<?php echo $datosActa['fojaFirmas']?>'><span class="input-group-addon glyphicon glyphicon-edit"></span>
                      </div>
                    </div>
                      <div class="col-sm-3 input-group">
                        <input type="number" class="form-control" name='fojaFirmas' id="fojaFirmas" placeholder="" data-plus-as-tab='true' value='<?php echo $datosActa['fojaFirmas']?>'><span class="input-group-addon glyphicon glyphicon-edit"></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="tipo" class="col-sm-3 control-label">Tipo especial</label>
                      <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-primary active">
                          <input type="checkbox" checked> Checkbox 1 (pre-checked)
                        </label>
                        <label class="btn btn-primary">
                          <input type="checkbox"> Checkbox 2
                        </label>
                        <label class="btn btn-primary">
                          <input type="checkbox"> Checkbox 3
                        </label>
                      </div>
                    <legend>Movimientos socios</legend>
                    <div class="form-group">
                      <label for="altas" class="col-sm-3 control-label">Altas</label>
                      <div class="col-sm-7 input-group">
                        <input type="text" class="form-control" name='altas' id="altas" placeholder="Nuevos socios"  data-plus-as-tab='true' value='<?php echo $datosActa['altas']?>'><span class="input-group-addon glyphicon glyphicon-import"></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="bajas" class="col-sm-3 control-label">Bajas</label>
                      <div class="col-sm-7 input-group">
                        <input type="text" class="form-control" name='bajas' id="bajas" placeholder="Renuncias o expulsiones"  data-plus-as-tab='true' value='<?php echo $datosActa['bajas']?>' ><span class="input-group-addon glyphicon glyphicon-export"></span>
                      </div>
                    </div>
                    <legend>Minuta</legend>
                    <div class="form-group">
                      <label for="detalle" class="col-sm-3 control-label">Detalle</label>
                      <div class="col-sm-7  input-group">
                        <textarea class="form-control" name='detalle' id="detalle" placeholder="Resumen de lo tratado en el acta" data-plus-as-tab='true' required='required' rows='4' cols='80'/><?php echo $datosActa['detalle']?></textarea>
                      </div>
                    </div>
                  </div>
                  
                  <?php $a++;
                  }
                } else {
                  // cambio el sistema de multiples adds a máximo 3 actas por foja
                  ?>
                  <div class='formActa' id='formActa<?php echo $acta?>'>
                      <div class="form-group">
                        <label for="acta" class="col-sm-3 control-label">Acta Nº</label>
                        <div class="col-sm-6  input-group">
                          <input type="number" class="form-control" name='acta' id="acta" placeholder="" data-plus-as-tab='true'><span class="input-group-addon glyphicon glyphicon-edit"></span>
                       
                          <select id='idLibro' class='btn-bg primary form-control' name='idLibro' data-plus-as-tab='true' >
                              <?php echo $_SESSION['selectorLibro']?>
                          </select>
                          </div>
                      </div>
                      <div class="form-group">
                        <label for="acta" class="col-sm-3 control-label">Fojas Nº</label>
                        <div class="col-sm-3 input-group">
                          <input type="text" class="form-control" name='fojas' id="fojas" placeholder="Separadas por comas" data-plus-as-tab='true'><span class="input-group-addon glyphicon glyphicon-edit"></span>
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="fecha" class="col-sm-3 control-label">Fecha</label>
                        <div class="col-sm-3 input-group">
                          <input type="date" class="form-control" name='fecha' id="fecha" placeholder="" min="1959-01-01" max="<?php echo date('Y-m-d')?>" data-plus-as-tab='true'><span class="input-group-addon glyphicon glyphicon-calendar"></span>
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="fojaFirmas" class="col-sm-3 control-label">Asistencia</label>
                        <div class="col-sm-3 input-group">
                          <input type="number" class="form-control" name='fojaFirmas' id="fojaFirmas" placeholder="" data-plus-as-tab='true'><span class="input-group-addon glyphicon glyphicon-edit"></span>
                        </div>
                      </div>
                      
                    <div class="form-group">
                      <label for="tipo" class="col-sm-3 control-label">Tipo especial</label>
                      <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-primary">
                          <input type="radio" name='tipo' id='tipo_asamblea' value='asamblea'> Asamblea
                        </label>
                        <label class="btn btn-primary">
                          <input type="radio" name='tipo' id='tipo_convocatoria' value='convocatoria'> Convocatoria
                        </label>
                        <label class="btn btn-primary">
                          <input type="radio" name='tipo' id='tipo_distribucion' value='distribucion'> Distribución
                        </label>
                      </div>

                    <legend>Movimientos socios</legend>
                    <div class="form-group">
                      <label for="altas" class="col-sm-3 control-label">Altas</label>
                      <div class="col-sm-3 input-group">
                        <input type="text" class="form-control buscaSocioABM" name='altas' id="altas_<?php echo $acta?>" placeholder="Nuevos socios"  data-plus-as-tab='true' ><span class="input-group-addon glyphicon glyphicon-import"></span>
                      </div>
                      <div class="col-sm-7 col-md-offset-3" id='socios_altas_<?php echo $acta?>'>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="bajas" class="col-sm-3 control-label">Bajas</label>
                      <div class="col-sm-3 input-group">
                        <input type="text" class="form-control buscaSocioABM" name='bajas' id="bajas_<?php echo $acta?>" placeholder="Renuncias o expulsiones"  data-plus-as-tab='true' ><span class="input-group-addon glyphicon glyphicon-export"></span>
                      </div>
                      <div class="col-sm-7 col-md-offset-3" id='socios_bajas_<?php echo $acta?>'>
                      </div>
                    </div>
                    <legend>Minuta</legend>
                    <div class="form-group">
                      <label for="detalle" class="col-sm-3 control-label">Detalle</label>
                      <div class="col-sm-7  input-group">
                        <textarea class="form-control" name='detalle' id="detalle" placeholder="Resumen de lo tratado en el acta" data-plus-as-tab='true' required='required' rows='4' cols='80'/></textarea>
                      </div>
                    </div>
                  </div>
                  <?php 
                }?>
              </fieldset>
              <div class="modal-footer">
                <div id='msgbox' class='pull-left'></div>
                <button type="submit" class="btn btn-primary" id='cargaLectura'> <?php if(!isset($idActa)){echo "Graba";} else {echo "Modifica";}?></button>
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
              url:       'func/sociosLibrosABM.php',         // override for form's 'action' attribute 
              type:      'post',       // 'get' or 'post', override for form's 'method' attribute 
              dataType: 'json'
          };
          $('#ingresoSocio').ajaxForm(opciones);    
      });
      
      $(".buscaSocioABM").focusout(function(){
        $(this).val("");
      }).click(function(){
        $(this).val("");
      });
      
      $(".buscaSocioABM").autocomplete({
        source: "func/ajaxBuscaSocio.php",
        minLength: 2,
        select: function( event, ui ) {
          var que = $(this).attr('id');
          
          var label = "danger";
          if( que.substr(0,5) == 'altas') {
            label = "success";
          }
          $("#socios_"+que).append("<span class='abmSocio label label-"+label+"'>  <span aria-hidden='true'>&times;</span> "+ui.item.label+"<input name='"+que+"["+ui.item.value+"]' type='hidden' value='"+ui.item.value+"'/></span>&nbsp;");

          $(this).val("  ");
          
          $('.abmSocio').click(function(){
            $(this).remove();
          });
        }
      });

      $('.abmSocio').click(function(){
        $(this).remove();
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
          listaUltimos();
        }
      }

      function listaUltimos(){
        $.post('func/ajaxListaUltimasFojas.php', function(data) {
          $('#ultimas').html(data);
        });
      }
      listaUltimos();
    });
    </script>
  </body>
</html>
