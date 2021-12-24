<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$titulo = "Nueva foja de libros";
//ChromePhp::log($arrayYER);
if(isset($_GET['idFoja'])&&is_numeric($_GET['idFoja'])){
  $idFoja = $_GET['idFoja'];
  // levanto datos de la foja y de las actas
  $sql1 = "SELECT idFoja, libro, foja FROM dbo.[socios.libros] WHERE idFoja=$idFoja";
  $stmt = odbc_exec2( $mssql4, $sql1, __LINE__, __FILE__);
  $datosFoja = sqlsrv_fetch_array($stmt);
  
  $sql2 = "SELECT idActa, idFoja, acta, fecha, altas, bajas, detalle FROM dbo.[socios.actas] WHERE idFoja=$idFoja";
  $stmt2 = odbc_exec2( $mssql4, $sql2, __LINE__, __FILE__);
  ChromePhp::log($sql2);
  
} else if (isset($_GET['idFoja'])&&!is_integer($_GET['idFoja'])){
  unset($_GET['idFoja']);
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
              <?php if(isset($idFoja)){
                echo "<h2>Modificación foja</h2>";
              } else {
                echo "<h2>Nueva foja</h2>";
              }?>
              <div class='col-md-5 pull-right'>
                <legend>Últimas fojas</legend>
                <ul id='ultimas'></ul>
              </div>
              <div class='col-md-7'>
              <form name='ingresoSocio' id='ingresoSocio' class="form-horizontal" enctype="multipart/form-data">
                <?php if(isset($idFoja)){
                  echo "<input type='hidden' name='idFoja' value='$idFoja'/>";
                }?>
                <fieldset>
                <legend>Datos de la nueva foja</legend>
                  <div class="form-group">
                    <label for="libro" class="col-sm-3 control-label">Libro</label>
                    <div class="col-sm-2 input-group">
                      <input type="text" class="form-control" name='libro' id="libro" placeholder="Libro" required='required' data-plus-as-tab='true' <?php if(isset($idFoja)){echo "value='$datosFoja[libro]'";}?>/><span class="input-group-addon glyphicon glyphicon-barcode"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="foja" class="col-sm-3 control-label">Foja</label>
                    <div class="col-sm-2 input-group">
                      <input type="text" class="form-control" name='foja' id="foja" placeholder="Foja" required='required' data-plus-as-tab='true' <?php if(isset($idFoja)){echo "value='$datosFoja[foja]'";}?> ><span class="input-group-addon glyphicon glyphicon-user"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="archivo" class="col-sm-3 control-label">Archivo</label>
                    <div class="col-sm-7 input-group">
                      <input type="file"  name='archivo' id="archivo" placeholder="Actas separadas por comas" required='required' data-plus-as-tab='true' >
                    </div>
                  </div>
                </fieldset>
                <?php if(isset($idFoja)){?>
                  <legend>Visualización</legend>
                  <div>

                    <object data="<?php echo "/pdf/$idFoja.pdf"?>" type="application/pdf" width="728" height="600"></object>

                  </div>
                <?php }?>
                <fieldset id='actas'>
                <legend>Actas incluídas</legend>
                <?php if(isset($idFoja)){
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
                        <input type="number" class="form-control" name='acta[]' id="acta" placeholder="" data-plus-as-tab='true' value='<?php echo $datosActa['acta']?>'><span class="input-group-addon glyphicon glyphicon-edit"></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="fecha" class="col-sm-3 control-label">Fecha</label>
                      <div class="col-sm-3 input-group">
                        <input type="date" class="form-control" name='fecha[]' id="fecha" placeholder="" min="1959-01-01" max="<?php echo date('Y-m-d')?>" data-plus-as-tab='true' value='<?php echo $datosActa['fecha']->format('Y-m-d')?>'><span class="input-group-addon glyphicon glyphicon-calendar"></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="altas" class="col-sm-3 control-label">Altas</label>
                      <div class="col-sm-7 input-group">
                        <input type="text" class="form-control" name='altas[]' id="altas" placeholder="Nuevos socios"  data-plus-as-tab='true' value='<?php echo $datosActa['altas']?>'><span class="input-group-addon glyphicon glyphicon-import"></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="bajas" class="col-sm-3 control-label">Bajas</label>
                      <div class="col-sm-7 input-group">
                        <input type="text" class="form-control" name='bajas[]' id="bajas" placeholder="Renuncias o expulsiones"  data-plus-as-tab='true' value='<?php echo $datosActa['bajas']?>' ><span class="input-group-addon glyphicon glyphicon-export"></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="detalle" class="col-sm-3 control-label">Detalle</label>
                      <div class="col-sm-7  input-group">
                        <textarea class="form-control" name='detalle[]' id="detalle" placeholder="Resumen de lo tratado en el acta" data-plus-as-tab='true' required='required' rows='4' cols='80'/><?php echo $datosActa['detalle']?></textarea>
                      </div>
                    </div>
                  </div>
                  
                  <?php $a++;
                  }
                } else {
                  // cambio el sistema de multiples adds a máximo 3 actas por foja
                  $actasPorFoja = 3;
                  for($acta=0;$acta<3;$acta++){
                  ?>
                  <div class='formActa' id='formActa<?php echo $acta?>'>
                    <div class='botonera pull-right'>
                      <!-- <span class='btn-add glyphicon glyphicon-plus'>&nbsp;</span>-->
                      <?php if($acta > 0){?> 
                      <span class='btn-remove glyphicon glyphicon-remove'>&nbsp;</span>
                      <?php }?>
                    </div>
                    <div class="form-group">
                      <label for="acta" class="col-sm-3 control-label">Acta Nº</label>
                      <div class="col-sm-3 input-group">
                        <input type="number" class="form-control" name='acta[]' id="acta" placeholder="" data-plus-as-tab='true'><span class="input-group-addon glyphicon glyphicon-edit"></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="fecha" class="col-sm-3 control-label">Fecha</label>
                      <div class="col-sm-3 input-group">
                        <input type="date" class="form-control" name='fecha[]' id="fecha" placeholder="" min="1959-01-01" max="<?php echo date('Y-m-d')?>" data-plus-as-tab='true'><span class="input-group-addon glyphicon glyphicon-calendar"></span>
                      </div>
                    </div>
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
                    <div class="form-group">
                      <label for="detalle" class="col-sm-3 control-label">Detalle</label>
                      <div class="col-sm-7  input-group">
                        <textarea class="form-control" name='detalle[]' id="detalle" placeholder="Resumen de lo tratado en el acta" data-plus-as-tab='true' required='required' rows='4' cols='80'/></textarea>
                      </div>
                    </div>
                  </div>
                  <?php if($acta < $actasPorFoja - 1) echo "<div class='modal-footer'></div>";
                }}?>
              </fieldset>
              <div class="modal-footer">
                <div id='msgbox' class='pull-left'></div>
                <button type="submit" class="btn btn-primary" id='cargaLectura'> <?php if(!isset($idFoja)){echo "Graba";} else {echo "Modifica";}?></button>
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
          $("#socios_"+que).append("<span class='abmSocio label label-"+label+"'>  <span aria-hidden='true'>&times;</span> "+ui.item.label+"<input name='"+que+"[]["+ui.item.value+"]' type='hidden' value='"+ui.item.value+"'/></span>&nbsp;");

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

      
      /* Deprecated, lo cambie para no complicarme la vida */
      /*
      var regex = /^(.+?)(\d+)$/i;
      var cloneIndex = $(".formActa").length;

      function clone(){
        $(this).parents(".formActa").clone()
          .appendTo("#actas")
          .attr("id", "formActa" +  cloneIndex)
          .find("*")
          .each(function() {
            var id = this.id || "";
            var match = id.match(regex) || [];
            if (match.length == 3) {
              this.id = match[1] + (cloneIndex);
            }
          })
          .on('click', '.btn-add', clone)
          .on('click', '.btn-remove', remove);
        cloneIndex++;
      }

      $(".btn-add").on("click", clone);
      */

      function remove(){
        var cloneIndex2 = $(".formActa").length;
        if(cloneIndex2>1){
          $(this).parents(".formActa").hide();
        }
      }
      $(".btn-remove").on("click", remove);
      

      function remove2(){
        var cloneIndex2 = $(".formActa").length;
        if(cloneIndex2>1){
          $(this).parents(".formActa").remove();
        }
      }
      $(".btn-remove").on("click", remove2);




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
