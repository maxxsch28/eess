<link rel="shortcut icon" href="/img/favicon.ico" type="image/vnd.microsoft.icon" />
<link rel="icon" href="/img/favicon.ico" type="image/vnd.microsoft.icon" /> 
<meta charset="utf-8">
<title><?php echo $titulo?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Intranet Cooperativa de Transporte de Coronel Suarez">
<meta name="author" content="Maximiliano Schimmel">
<?php if(substr($_SERVER['REMOTE_ADDR'],0,10)=='192.168.1.'){
//ChromePhp::log('intranet');?>
<!--<link rel="stylesheet" href="css/bootstrap.min.css">-->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/jquery-ui.css">
<?php } else { //ChromePhp::log('internet (afuera)');
?>
  <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">-->
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
<?php }?>
<link rel="stylesheet" href="css/bootstrap.css">
<link rel="stylesheet" href="css/datepicker.css">
<!--<meta name="theme-color" content="#FCF8E3" />
<meta name="theme-color" content="#123456" />-->
<link rel="stylesheet" href="css/jquery.contextMenu.min.css">
