<?php 
function ldap_loguin($user, $pass){
	//if(!isset($user))return false;
	/* using ldap bind
	$ldaprdn  = 'uid=toto,dc=dattabank,dc=com';     // ldap rdn or dn
	$ldapuser = 'toto';
	$ldappass = 'escalar1';  // associated password
	*/
	$ldaprdn = 'uid='.$user.',dc=dattabank,dc=com';
	// connect to ldap server
	$ldapconn = ldap_connect("127.0.0.1")
		or die("Could not connect to LDAP server.");
	ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
	if ($ldapconn){
		// binding to ldap server
		// echo 'user es:'.$user.' pass es:'.$pass;
		// $ldapbind = ldap_bind($ldapconn, $ldaprdn, $pass);
		// $ldapbind = ldap_bind($ldapconn, "dn=\"uid=toto,dc=dattabank,dc=com\"");
		// verify binding
		//echo"dbg: $ldapconn, $ldaprdn, $pass";
		if (@ldap_bind($ldapconn, $ldaprdn, $pass))
			return true;//echo "LDAP bind successful...";
		else
			return false;//echo "LDAP bind failed...";
	}
}

function existe_username($user){
	$ldaprdn = 'dc=dattabank,dc=com';
	// connect to ldap server
	$ldapconn = ldap_connect("127.0.0.1")
		or die("Could not connect to LDAP server.");
		
	ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
	
	if($ldapconn){
		$ldapbind=ldap_bind($ldapconn);
		// puse la @ para que no muestre errores si el php.ini lo permite cuando se chequea un usuario inexistente.
		@$sr=ldap_search($ldapconn, $ldaprdn,"uid=$user");
		//ldap_search(int link_identifier, string base_dn, string filter, array [attributes]);
		//echo 'search: '.$sr;
		$resultado=ldap_count_entries($ldapconn, $sr);
		if(isset($resultado)&&$resultado>0){
			//echo "Existe...";
			return true;
		}else{
			//echo "No existe...";
			return false;
		}
	}else
		return false;
}
/*f
Acomode la funcion existe_username dejandola como está arriba. Esta siguiente siempre retornaba true porque solo validaba que se pudiera conectar al ldap.
unction existe_username($user)
{
	$ldaprdn = 'uid='.$user.',dc=dattabank,dc=com';
	// connect to ldap server
	$ldapconn = ldap_connect("127.0.0.1")
		or die("Could not connect to LDAP server.");
		
	ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
	
	if($ldapconn)
		return true;
	else
		return false;
}*/

function ldap_change_pass($user, $pass, $newpass) {
	$ldaprdn = 'uid='.$user.',dc=dattabank,dc=com';
	// connect to ldap server
	$ldapconn = ldap_connect("127.0.0.1")
		or die("Could not connect to LDAP server.");
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
	
	if ($ldapconn){
		// binding to ldap server
		// echo 'user es:'.$user.' pass es:'.$pass;
		$ldapbind = ldap_bind($ldapconn, $ldaprdn, $pass);
		// $ldapbind = ldap_bind($ldapconn, "dn=\"uid=toto,dc=dattabank,dc=com\"");
		// verify binding
		if ($ldapbind){
			//ldap_modify($ldapconn, $ldaprdn, $pass2); 
			$resultado=ldap_mod_replace ($ldapconn, "uid=".$user.",dc=dattabank,dc=com", 
			array('userpassword' => "{MD5}".base64_encode(pack("H*",md5($newpass)))));
			if ($resultado) { return true; } else { return false; }
			//print ".</p>\n";
			return true;//echo "LDAP bind successful...";
		}else
			return false;//echo "LDAP bind failed...";
	}
}

function ldap_change_attrib($username, $atributo, $nuevovalor){
	$atrib[$atributo]=$nuevovalor;
	//echo"ldap_change_attrib($username, $atributo, $nuevovalor)<br/>";
	$ldaprdn = 'cn=Manager,dc=dattabank,dc=com';
	// connect to ldap server
	$ldapconn = ldap_connect("127.0.0.1")
		or die("Could not connect to LDAP server.");
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
	
	if ($ldapconn){
		//echo "\$ldapconn $ldapconn<br/>";
		// binding to ldap server
		$ldapbind = ldap_bind($ldapconn, $ldaprdn, "d44tta");
		// verify binding
		if ($ldapbind){
			//echo"\$ldapbind $ldapbind<br/>";
			$resultado=ldap_mod_replace ($ldapconn, "uid=$username,dc=dattabank,dc=com", $atrib);
			//$resultado=ldap_mod_add ($ldapconn, "uid=$username,dc=dattabank,dc=com", $atrib);
			//echo "\$resultado=ldap_mod_replace ($ldapconn, \"uid=$username,dc=dattabank,dc=com\", $atrib)<br/>";print_r($atrib);
			if ($resultado) { /*echo"true<br/>";*/ return true; } else { /*echo"false<br/>";*/ return false; }
			//echo"true2<br/>";
			return true;//echo "LDAP bind successful...";
		}else
			return false;//echo "LDAP bind failed...";
	}
}

function ldap_add_attrib($username, $atributo, $nuevovalor){
	$atrib[$atributo]=$nuevovalor;
	$ldaprdn = 'cn=Manager,dc=dattabank,dc=com';
	// connect to ldap server
	$ldapconn = ldap_connect("127.0.0.1")
		or die("Could not connect to LDAP server.");
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
	
	if ($ldapconn){
		//echo "\$ldapconn $ldapconn<br/>";
		// binding to ldap server
		$ldapbind = ldap_bind($ldapconn, $ldaprdn, "d44tta");
		// verify binding
		if ($ldapbind){
			$resultado=ldap_mod_add ($ldapconn, "uid=$username,dc=dattabank,dc=com", $atrib);
			if ($resultado) { return true; } else {  return false; }
			return true;//echo "LDAP bind successful...";
		}else
			return false;//echo "LDAP bind failed...";
	}
}

function consulta_ldap($variable, $usuario=""){
	if($usuario=="")$usuario=$_SESSION['username'];
	$ds=ldap_connect("localhost");  // must be a valid LDAP server!
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

	if ($ds){
		$r=ldap_bind($ds);
		$sr=ldap_search($ds, "dc=dattabank, dc=com", "uid=".$usuario);//
		$info = ldap_get_entries($ds, $sr);
		//print_r($info);
		if(isset($info[0][$variable]))
			$valor=$info[0][$variable];
		ldap_close($ds);
		if(!isset($valor))$valor=false;
	}else
		echo "<h4>Unable to connect to LDAP server</h4>";

	return $valor;
	/*Array (
		[count] => 1 [0] => Array (
				[cn] => Array ( [count] => 1 [0] => maxi cuspide maxi cuspide ) [0] => cn 
				[gidnumber] => Array ( [count] => 1 [0] => 100 ) [1] => gidnumber 
				[givenname] => Array ( [count] => 1 [0] => maxi cuspide ) [2] => givenname 
				[homedirectory] => Array ( [count] => 1 [0] => /storage/mibackup/maxicuspide ) [3] => homedirectory 
				[objectclass] => Array ( [count] => 4 [0] => top [1] => posixAccount [2] => shadowAccount [3] => inetOrgPerson ) [4] => objectclass 
				[shadowinactive] => Array ( [count] => 1 [0] => -1 ) [5] => shadowinactive 
				[shadowlastchange] => Array ( [count] => 1 [0] => 13474 ) [6] => shadowlastchange 
				[shadowmax] => Array ( [count] => 1 [0] => 99999 ) [7] => shadowmax 
				[shadowmin] => Array ( [count] => 1 [0] => 0 ) [8] => shadowmin 
				[shadowwarning] => Array ( [count] => 1 [0] => 7 ) [9] => shadowwarning 
				[sn] => Array ( [count] => 1 [0] => maxi cuspide ) [10] => sn 
				[uid] => Array ( [count] => 1 [0] => maxicuspide ) [11] => uid 
				[uidnumber] => Array ( [count] => 1 [0] => 1004 ) [12] => uidnumber 
				[mail] => Array ( [count] => 1 [0] => mschimmel@gmail.com ) [13] => mail 
				[loginshell] => Array ( [count] => 1 [0] => /usr/bin/rssh ) [14] => loginshell 
				[count] => 15 [dn] => uid=maxicuspide,dc=dattabank,dc=com 
			)
	)*/
}

function ldap_user($usuario=""){
	if($usuario=="")$usuario=$_SESSION['username'];
	$ds=ldap_connect("localhost");  // must be a valid LDAP server!
	        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	if ($ds){
		$r=ldap_bind($ds);
		$sr=ldap_search($ds, "dc=dattabank, dc=com", "uid=".$usuario);//
		$info = ldap_get_entries($ds, $sr);
		//print_r($info);
		return $info;
		ldap_close($ds);
	}else
		echo "<h4>Unable to connect to LDAP server</h4>";
}
?>
