<?php

	// Inicia la sesión
	session_start();

	// motor de plantillas
	include 'lib/kiwi/Kiwi.php';  

	// para pasar variables a las plantillas
	$vars = [];

	// por defecto se va a landing
	$controlador = "landing";

	// si pidieron una seccion lo llevamos a ella
	if(strlen($_GET['slug'])!=0){
		$pag = explode("/",$_GET['slug']);
		$controlador = $pag[0];	
	}


	// averiguamos si existe el controlador
	if(!is_file('controllers/'.$controlador.'Controller.php')){
		$controlador = "error404";
	}

	//=== firewall

	// Listas de acceso dependiendo del estado del usuario
	$controlador_login = ["logout", "perfil", "abandonar"];
	$controlador_anonimo = ["landing", "login", "panel"];

	// sesion iniciada
	if(isset($_SESSION['morphyx'])){

		// recorre la lista de secciones no permitidas
		foreach ($controlador_anonimo as $key => $value) {
			// si esta solicitando una sección no permitida
			if($controlador==$value){
				$controlador = "panel";
				break;
			}
		}

	}else{ // sesión no iniciada

			// recorre la lista de secciones no permitidas
			foreach ($controlador_login as $key => $value) {
			// si esta solicitando una sección no permitida
			if($controlador==$value){
				$controlador = "landing";
				break;
			}
		}

	}

	// === fin firewall


	include 'controllers/'.$controlador.'Controller.php';

 ?>