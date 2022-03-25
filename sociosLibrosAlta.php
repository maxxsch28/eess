<?php
// sociosLibroAlta.php
// Carga solo las fojas escaneadas de cada libro

$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$titulo = "Nueva foja de libros";
//ChromePhp::log($arrayYER);

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


if(isset($_GET['idLibro'])&&is_numeric($_GET['idLibro'])){
  $idFoja = $_GET['idLibro'];
  // levanto datos de la foja y de las actas
  $sql1 = "SELECT idLibro, tipo, fechaDesde, fechaHasta FROM dbo.[socios.libros2] WHERE idLibro=$idLibro";
  $stmt = odbc_exec2( $mssql4, $sql1, __LINE__, __FILE__);
  $datosFoja = sqlsrv_fetch_array($stmt);

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
                    <div class="col-sm-3 input-group">
                      <select id='idLibro' class='btn-bg primary form-control' name='idLibro' data-plus-as-tab='true' >
                        <?php echo $_SESSION['selectorLibro']?>
                      </select>
                      <span class="input-group-addon glyphicon glyphicon-barcode"></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="foja" class="col-sm-3 control-label">Foja</label>
                    <div class="col-sm-3 input-group">
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

                    <object data="<?php echo "/pdf/".str_pad($idFoja, 4, '0', STR_PAD_LEFT).".pdf"?>" type="application/pdf" width="728" height="600"></object>

                  </div>
                <?php }?>
                
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
              url:       'func/ajaxSociosFojasABM.php',         // override for form's 'action' attribute 
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
          $('#foja').val(responseText.proximaFoja);
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
