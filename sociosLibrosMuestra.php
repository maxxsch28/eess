<?php
$nivelRequerido = 4;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$titulo = "Visualización foja de libros";
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
  header("Location: /");
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
              <h2><?php echo "Libro Nº$datosFoja[libro]";?></h2>
              <div class='col-md-5 pull-right'>
                <fieldset id='actas'>
                <legend>Actas incluídas</legend>
                <?php if(isset($idFoja)){
                  $a=1;
                  while($datosActa = sqlsrv_fetch_array($stmt2)){
                    if($a>1)echo "<br/>";?>
                    <p>
                    <b>Acta Nº <?php echo $datosActa['acta']?>, reunión del <?php echo $datosActa['fecha']->format('d/m/Y')?></b><br/>
                    <u><b>Temas tratados:</b></u><br/>
                    <?php 
                    echo $datosActa['detalle'];
                    if($datosActa['altas']<>''){echo "<br/><u><b>Nuevos socios</b></u><br>$datosActa[altas]";}
                    if($datosActa['bajas']<>''){echo "<br/><u><b>Suspensiones/expulsiones/renuncias</b></u><br>$datosActa[bajas]";}
                    $a++;
                    ?>
                    </p>
                  <?php }
                } ?>
              </div>
              <div class='col-md-7'>
                <fieldset>
                <legend><span class='nav pull-right'>Siguiente >></span><span class='nav'><< Anterior</span><span> Foja Nº<?php echo $datosFoja['foja']?></span></legend>
                <div>
                  <object data="<?php echo "/pdf/$idFoja.pdf"?>" type="application/pdf" width="728" height="800"></object>
                </div>
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
          listaUltimos();
        }
      }
      
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
      function remove(){
        var cloneIndex2 = $(".formActa").length;
        if(cloneIndex2>1){
          $(this).parents(".formActa").remove();
        }
      }
      $(".btn-add").on("click", clone);
      $(".btn-remove").on("click", remove);
      
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
