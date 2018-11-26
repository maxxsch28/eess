<?php
	/*
		UserPie Langauge File.
		Language: English.
	*/
	
	/*
		%m1% - Dymamic markers which are replaced at run time by the relevant index.
	*/

	$lang = array();
	
	//Account
	$lang = array_merge($lang,array(
		"ACCOUNT_SPECIFY_USERNAME" 				=> "Por favor ingrese su usuario",
		"ACCOUNT_SPECIFY_PASSWORD" 				=> "Por favor ingrese su password",
		"ACCOUNT_SPECIFY_EMAIL"					=> "Por favor ingrese su correo electrónico",
		"ACCOUNT_INVALID_EMAIL"					=> "Correo electrónico inválido",
		"ACCOUNT_INVALID_USERNAME"				=> "Usuario inválido",
		"ACCOUNT_USER_OR_EMAIL_INVALID"			=> "usuario o correo electrónico inválido",
		"ACCOUNT_USER_OR_PASS_INVALID"			=> "usuario o contraseña inválido",
		"ACCOUNT_ALREADY_ACTIVE"				=> "Su usuario ya está activado",
		"ACCOUNT_INACTIVE"						=> "Su usuario está inactivo. Chequee sus mails para ver las instrucciones de activación",
		"ACCOUNT_USER_CHAR_LIMIT"				=> "Su usuario debe tener entre %m1% y %m2% caracteres",
		"ACCOUNT_PASS_CHAR_LIMIT"				=> "Su contraseña debe tener entre %m1% y %m2% caracteres",
		"ACCOUNT_PASS_MISMATCH"					=> "las contraseñas no coinciden",
		"ACCOUNT_USERNAME_IN_USE"				=> "el usuario %m1% ya está en uso",
		"ACCOUNT_EMAIL_IN_USE"					=> "el correo electrónico %m1% ya está en uso",
		"ACCOUNT_LINK_ALREADY_SENT"				=> "Ya enviamos un correo electrónico de activación a esta dirección en la última/s %m1% hora/s",
		"ACCOUNT_NEW_ACTIVATION_SENT"			=> "Enviamos un correo electrónico con un nuevo enlace de activación, revise su casilla, incluso la carpeta de Spam",
		"ACCOUNT_NOW_ACTIVE"					=> "Su cuenta ahora está activa",
		"ACCOUNT_SPECIFY_NEW_PASSWORD"			=> "Por favor ingrese su contraseña",	
		"ACCOUNT_NEW_PASSWORD_LENGTH"			=> "La nueva contraseña debe tener entre %m1% y %m2% caracteres",	
		"ACCOUNT_PASSWORD_INVALID"				=> "La contraseña actual no coincide con nuestro registro",	
		"ACCOUNT_EMAIL_TAKEN"					=> "Este correo electrónico está usado por otro usuario",
		"ACCOUNT_DETAILS_UPDATED"				=> "Datos de usuario actualzados",
		"ACTIVATION_MESSAGE"					=> "You will need first activate your account before you can login, follow the below link to activate your account. \n\n
													%m1%activate-account.php?token=%m2%",							
		"ACCOUNT_REGISTRATION_COMPLETE_TYPE1"	=> "Ya fue creado su usuario. Puede ingresar desde <a href=\"login.php\">aquí</a>.",
		"ACCOUNT_REGISTRATION_COMPLETE_TYPE2"	=> "Ya está registrado. Pronto recibirá un correo electrónico con instrucciones para activar su cuenta. 
													Debe activarla antes de poder ingresar.",
	));
	
	//Forgot password
	$lang = array_merge($lang,array(
		"FORGOTPASS_INVALID_TOKEN"				=> "Token inválido",
		"FORGOTPASS_NEW_PASS_EMAIL"				=> "Se le envió una nueva contraseña por correo electrónico",
		"FORGOTPASS_REQUEST_CANNED"				=> "Se canceló la recuperación de contraseña",
		"FORGOTPASS_REQUEST_EXISTS"				=> "Este usuario ya tiene un pedido de recuperación de contraseña en progreso",
		"FORGOTPASS_REQUEST_SUCCESS"			=> "Se le envió instrucciones por correo electrónico para recuperar el acceso a su cuenta",
	));
	
	//Miscellaneous
	$lang = array_merge($lang,array(
		"CONFIRM"								=> "Confirmar",
		"DENY"									=> "Prohibir",
		"SUCCESS"								=> "Éxito",
		"ERROR"									=> "Error",
		"NOTHING_TO_UPDATE"						=> "Nada que actualizar",
		"SQL_ERROR"								=> "Error de SQL",
		"MAIL_ERROR"							=> "Error al intentar enviar el correo electrónico. Comunicarse con Maxi",
		"MAIL_TEMPLATE_BUILD_ERROR"				=> "Error building email template",
		"MAIL_TEMPLATE_DIRECTORY_ERROR"			=> "Unable to open mail-templates directory. Perhaps try setting the mail directory to %m1%",
		"MAIL_TEMPLATE_FILE_EMPTY"				=> "Template file is empty... nothing to send",
		"FEATURE_DISABLED"						=> "This feature is currently disabled",
	));
?>