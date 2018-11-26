<?php // 192.168.1.93  // IP Playa
//192.168.1.5 // Teresa
?>

 <div class="navbar  navbar-fixed-top navbar-default" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="#">
              <img alt="CoopeTrans" src="img/iconoCooperativa.png"  style="margin-top: -8px;">
          </a>
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">

        	<li<?php if($_SERVER['PHP_SELF']=='/ypf/estadoTanques.php')echo ' class="active"';?>><a href="/ypf/estadoTanques.php?m=1">Estado tanques</a></li>
            <!--<li<?php if($_SERVER['PHP_SELF']=='/ypf/descargaCisterna.php')echo ' class="active"';?>><a href="/ypf/descargaCisterna.php">Descarga YPF</a></li>-->
		</ul>
		</div><!--/.navbar-collapse -->
	</div>
    </div>
