<?php
require_once("include/inicia.php");
require_once("include/config.php");
$titulo = "Crear un usuario";
//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) { header("Location: index.php"); die(); }

//Forms posted
if(!empty($_POST)){
    $errors = array();
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_pass = trim($_POST["passwordc"]);

    //Perform some validation
    //Feel free to edit / change as required

    if(minMaxRange(5,25,$username)) {
        $errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(5,25));
    }
    
    if(minMaxRange(8,50,$password) && minMaxRange(8,50,$confirm_pass)) {
        $errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50));
    } else if($password != $confirm_pass){
        $errors[] = lang("ACCOUNT_PASS_MISMATCH");
    }
    
    if(!isValidemail($email)){
        $errors[] = lang("ACCOUNT_INVALID_EMAIL");
    }
    
    //End data validation
    if(count($errors) == 0){	
        //Construct a user object
        $user = new User($username,$password,$email);

        //Checking this flag tells us whether there were any errors such as possible data duplication occured
        if(!$user->status) {
            if($user->username_taken) $errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
            if($user->email_taken) 	  $errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));		
        } else {
            //Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
            if(!$user->userPieAddUser()){
                if($user->mail_failure) $errors[] = lang("MAIL_ERROR");
                if($user->sql_failure)  $errors[] = lang("SQL_ERROR");
            }
        }
    }
   if(count($errors) == 0) {
            if($emailActivation)
            {
                 $message = lang("ACCOUNT_REGISTRATION_COMPLETE_TYPE2");
            } else {
                 $message = lang("ACCOUNT_REGISTRATION_COMPLETE_TYPE1");
            }
   } else {
            $message = '<span style="color: red;">'.implode(", ", $errors).'</span>';
   }
}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
      <?php include ('/include/head.php')?>
      <style>
            body {
              padding-top: 40px;
              padding-bottom: 40px;
              background-color: #eee;
            }

            .form-signin {
              max-width: 330px;
              padding: 15px;
              margin: 0 auto;
            }
            .form-signin .form-signin-heading,
            .form-signin .checkbox {
              margin-bottom: 10px;
            }
            .form-signin .checkbox {
              font-weight: normal;
            }
            .form-signin .form-control {
              position: relative;
              height: auto;
              -webkit-box-sizing: border-box;
                 -moz-box-sizing: border-box;
                      box-sizing: border-box;
              padding: 10px;
              font-size: 16px;
            }
            .form-signin .form-control:focus {
              z-index: 2;
            }
            .form-signin input[type="email"] {
              margin-bottom: -1px;
              border-bottom-right-radius: 0;
              border-bottom-left-radius: 0;
            }
            .form-signin input[type="password"] {
              margin-bottom: 10px;
              border-top-left-radius: 0;
              border-top-right-radius: 0;
            }
      </style>
  </head>
  <body>
	<?php include('include/menuSuperior.php') ?>
    <div class="container">
		<!-- Example row of columns -->
		<form class="form-signin" name="newUser" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
                <h2 class="form-signin-heading">Ingrese sus datos</h2>
                <?php
                if(!empty($_POST)&&count($errors) > 0){ ?>
                    <div id="errors" class='alert alert-danger'>
                        <?php errorBlock($errors); ?>
                    </div>     
                <?php }

                if(isset($_GET['status'])&&$_GET['status'] == "success"){
                    echo "<p>Your account was created successfully. Please login.</p>";
                }
                if(isset($message)){?>
                <div id="success" class='alert alert-success'>
                    <p><?php  echo $message; ?></p>
                 </div>
                <?php }?>
                <label for="username" class="sr-only">Usuario</label>
                <input type="text" id="username" name='username' class="form-control" placeholder="Usuario" required autofocus><br/>
                <label for="password" class="sr-only">Contraseña</label>
                <input type="password" id="password" name='password' class="form-control" placeholder="Contraseña" required>
                <label for="passwordc" class="sr-only">Repita contraseña</label>
                <input type="password" id="passwordc" name='passwordc' class="form-control" placeholder="Contraseña" required>
                <label for="email" class="sr-only">Correo electrónico</label>
                <input type="email" id="email" name='email' class="form-control" placeholder="Correo electrónico" required><br/>
 
                <button class="btn btn-lg btn-primary btn-block" type="submit" name="new" id="newfeedform">Registrar</button><br/>
                <a href="login.php">Ya tengo usuario</a> | <a href="users_forgot-password.php">¿Olvidó su contraseña?</a></p>
            </form>
        <?php include ('include/footer.php')?>
    </div> <!-- /container -->
	<?php include('include/termina.php');?>
	<script>
	</script>
  </body>
</html>
