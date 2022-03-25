<?php
// cargaFacturaMovistar.php
// Recibe archivo CSV con el detalle desde el anexo d la factura Movistar


$nivelRequerido = 2;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

$titulo = "Carga archivo con detalla de anexo Movistar";
?>

<!DOCTYPE html>
<html lang="es">
  <head>
      <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/head.php')?>
      <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      </style>
      <link rel="stylesheet" href="css/fileinput.min.css">
  </head>
  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
   
    <div class="container">
        <form id="form"  method="post" enctype="multipart/form-data">
            <input id="uploadImage" type="file" name="subeArchivo" class='file'/>
        </form>
        <div id='err'></div>
        <div id='preview'></div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
    <!--<script src="js/locales/es.js"></script>-->
	<script>
        $(document).ready(function() {
            $("#form").on('submit',(function(e) {
                e.preventDefault();
                $.ajax({
                    url: "func/ajaxCargaDetalleMovistar.php",
                    type: "POST",
                    data:  new FormData(this),
                    contentType: false,
                            cache: false,
                    processData:false,
                    beforeSend : function()
                    {
                        //$("#preview").fadeOut();
                        $("#err").fadeOut();
                    },
                    success: function(data){
                        if(data=='invalid')
                        {
                            // invalid file format.
                            $("#err").html("Invalid File !").fadeIn();
                        }
                        else
                        {
                            // view uploaded file.
                            $("#preview").html(data).fadeIn();
                            $("#form")[0].reset(); 
                        }
                    },
                    error: function(e) {
                        $("#err").html(e).fadeIn();
                    }          
                });
            }));
        })
	</script>
  </body>
</html>
