<?php 
	namespace MRBS;
	use Adaptador;
	require_once "defaultincludes.inc";
	require_once "functions.inc";

	// Check the user is authorised for this page
	checkAuthorised();
	// Also need to know whether they have admin rights
	$user = getUserName();
	$required_level = (isset($max_level) ? $max_level : 2);
	$is_admin = (authGetUserLevel($user) >= $required_level);

	//obtengo una adaptador
	$a = new Adaptador();

	//se asigna si llega con $_POST
	$materia_busqueda = '';

	
	//VERIFICO SI LLEGA UNA ACCI? POR POST
	if(isset($_POST['action'])){
		switch ($_POST['action']) {
			case 'no_laborables':
				$a->guardar_no_laborable($_POST['fecha']);
				break;
			case 'buscar_materia':
				if(isset($_POST['materia_busqueda']) && $_POST['materia_busqueda']){
					//Variable utilizada para la edici? de la materia
					$materia = $a->get_materia($_POST['materia_busqueda']);
					$materia_busqueda = $materia['id_materia'];
				} 
				break;
			case 'modificar_materia':
				unset($_POST['action']);
				$_POST['es_materia'] = isset($_POST['es_materia']) ? $_POST['es_materia'] : 'N';

				$_SESSION['notificaciones'][] = $a->editar_materia($_POST) ? 'Materia modificada con éxito!' : 'Ocurrió un error al intentar modificar la materia';
				unset($materia);
				break;
			case 'nueva_materia':
				unset($_POST['action']);
				$_POST['es_materia'] = isset($_POST['es_materia']) ? $_POST['es_materia'] : 'N';
				
				$_SESSION['notificaciones'][] = $a->nueva_materia($_POST) ? "Materia guardada!" : "Ocurrió un error al intentar guardar la materia";
				break;

		}
		
	}
	//VERIFICO SI LLEGA UNA ACCI? POR GET
	if(isset($_GET['action'])){
		switch ($_GET['action']) {
			case 'borrar_no_laborable':
				if(!isset($_GET['id']) || !($_GET['id']) ){
					$_SESSION['notificaciones'][] = "Debe indicar un ID para borrar.";
					return;
				}
				$a->borrar_no_laborable($_GET['id']);
				break;
			default:
				# code...
				break;
		}

		
	}
	
	$no_laborables = $a->get_no_laborables();

?>