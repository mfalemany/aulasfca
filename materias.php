<?php 
	namespace MRBS;
	require_once "defaultincludes.inc";
	require_once "functions.inc";
	use Adaptador;

	// Check the user is authorised for this page
	checkAuthorised();
	// Also need to know whether they have admin rights
	$user = getUserName();
	$required_level = (isset($max_level) ? $max_level : 2);
	$is_admin = (authGetUserLevel($user) >= $required_level);

	//obtengo una adaptador
	$a = new Adaptador();

	//Si tengo un ID de materia, es porque se está guardando una nuevo, o editando una existente
	if(isset($_POST['materia'])){
		if($_POST['id_materia']){
			if(editar_materia($_POST)){
				$notificacion = "SE HA MODIFICADO LA MATERIA CON &Eacute;XITO";
			}else{
				$notificacion = "Ocurri&oacute; un error al intentar guardar. Es posible que los c&oacute;digos ingresados ya existan en otras materias";
			}
		}else{
			$filtro = array('materia' => $_POST['materia'], 'carrera' => $_POST['carrera']);
			if(!$a->existe_materia($filtro)){ 
				if(!existen_codigos($_POST['codigos'])){
					if(nueva_materia($_POST)){
						$notificacion = "SE HA GUARDADO LA MATERIA CON &Eacute;XITO";
					}else{
						$notificacion = "OCURRI&Oacute; UN ERROR DESCONOCIDO AL INTENTAR GUARDAR LA MATERIA";					
					}
				}else{
					$notificacion = "ALGUNO DE LOS C&Oacute;DIGOS INGRESADOS YA EST&Aacute; EN USO";
				}
			}else{
				$notificacion = "YA EXISTE UNA MATERIA CON EL MISMO NOMBRE EN ESA CARRERA";
			}
		}
	}

	
	//Extraigo las variables contenidas en $_POST
	extract($_POST);
	//Por defecto, la variable materia no contiene nada
	$materia = array();
	//Si se está buscando una materia, realizo la busqueda
	if(isset($materia_busqueda)){
		$materia = $a->get_materias(array('id_materia'=>$materia_busqueda));
		if($materia){
			$materia = $materia[0];
		}
	}else{
		$materia_busqueda = '-1';
	}


	function editar_materia($datos)
	{
		global $a;
		return $a->editar_materia($datos);
	}
	function nueva_materia($datos)
	{
		global $a;
		return $a->nueva_materia($datos);
	}
	function existen_codigos($codigos)
	{
		global $a;
		$codigos = explode(',',$codigos);
		foreach ($codigos as $codigo) {
			if($a->existe_codigo($codigo)){
				return TRUE;
			}
		}
		return FALSE;
	}
?>
<!DOCTYPE html>
<html>
<head>
	<style type="text/css">
		#contenedor_materias{
			margin-top: 20px;
		}
		.derecha{
			text-align: right;
		}
		fieldset{
			margin-bottom: 20px !important; 
			border:1px solid black !important;
			padding: 15px 0px 15px 10px !important;
			
		}
		fieldset legend{
			font-size: 0.7em;
		}
		table tr{
			margin: 5px 0px 5px 0px;
		}
	</style>
	<title>Modificacion de Materias</title>
</head>
<body>
<?php print_header(); ?>

<div id='contenedor_materias'>
	<fieldset>
		<legend>Seleccione una materia para modificar</legend>
		<form action="materias.php" method="post">
			<?php 
			echo $a->generar_select('Seleccione una materia para editar','materia_busqueda',$materia_busqueda);
			?>
			<input type="submit" value="Editar">
		</form>
	</fieldset>
	<fieldset>
		<?php if(isset($notificacion)) : ?>
		<div id="notificacion" style="background-color:#feffa0; font-size: 1.2em; color:#c54242; padding: 5px 0px 5px 20px; font-weight: bold;">
			<?php echo $notificacion; ?>	
		</div>
		<?php endif; ?>
		<legend>Detalles de la materia</legend>
		<form action="materias.php" method="post">
			<input type="hidden" name="id_materia" value="<?php echo (isset($materia['id_materia'])) ? $materia['id_materia'] : ''; ?>">
			<table>
				<tr>
					<td>Materia:</td>
					<td>
						<input type="text" name="materia"  size="75" 
								value="<?php echo (isset($materia['materia'])) ? $materia['materia'] : ''; ?>" 
								required>
					</td>
				</tr>
				<tr>
					<td>Es una materia?</td>
					<td><input type="checkbox" name="es_materia" <?php echo (isset($materia['es_materia']) && $materia['es_materia'] == 'S') ? 'checked' : '' ; ?> value='S'></td>
				</tr>
				<tr>
					<td>Color:</td>
					<td><input type="color" name="color" 
								value="<?php echo isset($materia['color']) ? $materia['color'] : ''; ?>" required></td>
				</tr>
				<tr>
					<td>Carrera:</td>
					<td>
						<select name="carrera" style="width:475px;" required>
							<option value="---" <?php echo (isset($materia['carrera']) && $materia['carrera'] == '---')?'SELECTED':'';?> >Ninguna</option>
							<option value="AGR" <?php echo (isset($materia['carrera']) && $materia['carrera'] == 'AGR')?'SELECTED':'';?>>Agronomia</option>
							<option value="IND" <?php echo (isset($materia['carrera']) && $materia['carrera'] == 'IND')?'SELECTED':'';?>>Industrial</option>
						</select>

					</td>
				</tr>
				<tr>
					<td>C&oacute;digos:</td>
					<td>
						<input type="text" name="codigos"  size="35" 
								value="<?php echo (isset($materia['codigos'])) ? $materia['codigos'] : ''; ?>" >
								 <span style='font-size:0.7em;'> (Ingrese valores separados por coma: Ejemplo: 01,50,51.)</span>
					</td>
				</tr>
				<tr>
					<td colspan=2 class="derecha"><input type="submit" value="Guardar"></td>
				</tr>
			</table>

		</form>
	</fieldset>
</div>
</body>
<script type="text/javascript">
	setTimeout(function(){ if( $("#notificacion") != 'undefined'){$("#notificacion").fadeOut()} } , 3000);
</script>
</html>