<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_NUMERIC, 'Spanish_Spain.28605');
$titulo = "Ingresar";
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.php');
	
//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) { header("Location: index.php"); die(); }


/* 
    Below is a very simple example of how to process a login request.
    Some simple validation (ideally more is needed).
*/

//Forms posted
if(!empty($_POST)){
    $errors = array();
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    if(!isset($_POST['remember_me'])){
        $remember_choice = 0;
    } else {
        $remember_choice = trim($_POST["remember_me"]);
    }
    //Perform some validation
    //Feel free to edit / change as required
    if($username == ""){
        $errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
    }
    if($password == ""){
        $errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");
    }

    //End data validation
    if(count($errors) == 0){
        //A security note here, never tell the user which credential was incorrect
        if(!usernameExists($username)){
            $errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
        }else{
            $userdetails = fetchUserDetails($username);

            //See if the user's account is activation
            if($userdetails["active"]==0){
                $errors[] = lang("ACCOUNT_INACTIVE");
            }else{
                //Hash the password and use the salt from the database to compare the password.
                $entered_pass = generateHash($password,$userdetails["password"]);

                if($entered_pass != $userdetails["password"]){
                    //Again, we know the password is at fault here, but lets not give away the combination incase of someone bruteforcing
                    $errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
                }else{
                    //passwords match! we're good to go'

                    //Construct a new logged in user object
                    //Transfer some db data to the session object
                    $loggedInUser = new loggedInUser();
                    $loggedInUser->email = $userdetails["email"];
                    $loggedInUser->user_id = $userdetails["user_id"];
                    $loggedInUser->hash_pw = $userdetails["password"];
                    $loggedInUser->group_id = $userdetails["group_id"];
                    $loggedInUser->display_username = $userdetails["username"];
                    $loggedInUser->clean_username = $userdetails["username_clean"];
                    $loggedInUser->remember_me = $remember_choice;
                    $loggedInUser->remember_me_sessid = generateHash(uniqid(rand(), true));

                    //Update last sign in
                    $loggedInUser->updatelast_sign_in();

                    if($loggedInUser->remember_me == 0)
                        $_SESSION["userPieUser"] = $loggedInUser;
                    else {
                        $db->sql_query("INSERT INTO users_sessions VALUES('".time()."', '".serialize($loggedInUser)."', '".$loggedInUser->remember_me_sessid."')");
                        setcookie("userPieUser", $loggedInUser->remember_me_sessid, time()+parseLength($remember_me_length));
                    }
                    //Redirect to user account page
                    if(isset($_SESSION['volverLuegoDelLogin'])){
                        header("Location: $_SESSION[volverLuegoDelLogin]");
                        unset($_SESSION['volverLuegoDelLogin']);
                        die;
                    } else {
                        header("Location: index.php");
                        die();
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
      <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/head.php')?>
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
      <?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
      <div class="container">
            <!-- Example row of columns -->
            <form class="form-signin" name="newUser" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
                
                <h2 class="form-signin-heading"><?php if(isset($_SESSION['volverLuegoDelLogin'])){echo"Debe ingresar al sistema para acceder a esa página";}else echo"Ingrese sus datos"?></h2>
                <?php
                if(!empty($_POST)&&count($errors) > 0){ ?>
                    <div id="errors" class='alert alert-danger'>
                        <?php errorBlock($errors); ?>
                    </div>     
                <?php }

                if(isset($_GET['status'])&&$_GET['status'] == "success"){
                    echo "<p>Your account was created successfully. Please login.</p>";
                }
                ?>
                <label for="username" class="sr-only">Usuario</label>
                <input type="text" id="username" name='username' class="form-control" placeholder="Usuario" required autofocus><br/>
                <label for="password" class="sr-only">Contraseña</label>
                <input type="password" id="password" name='password' class="form-control" placeholder="Contraseña" required>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="remember_me" value="1"/> Recordarme durante una semana
                    </label>
                </div>
                <button class="btn btn-lg btn-primary btn-block" type="submit" name="new" id="newfeedform">Ingresar</button><br/>
                <a href="users_register.php">Crear usuario</a> | <a href="users_forgot-password.php">¿Olvidó su contraseña?</a></p>
            </form>
              
          <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
      </div> <!-- /container -->
      <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
      <script>
      </script>
  </body>
</html>
