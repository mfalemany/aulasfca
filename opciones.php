<?php 
	namespace MRBS;
	include_once('opciones_procesar.php'); 

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
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
	<title>Configuración</title>
</head>
<body>
<?php print_header(); ?>
<?php if(isset($_SESSION['notificaciones']) && count($_SESSION['notificaciones'])) : ?>
<div id="notificacion" style="background-color:#feffa0; font-size: 1.2em; color:#c54242; padding: 5px 0px 5px 20px; font-weight: bold;">
	<?php 
		foreach ($_SESSION['notificaciones'] as $notificacion) {
			echo $notificacion."<br>";
		}
		unset($_SESSION['notificaciones']);
	?>	

</div>
<?php endif; ?>


<div id='contenedor_materias'>
	<fieldset>
		<legend>Seleccione una materia para modificar</legend>
		<form action="opciones.php" method="post">
			<input type="hidden" name="action" value="buscar_materia">
			<?php 
			echo $a->generar_select('Seleccione una materia para editar','materia_busqueda',$materia_busqueda);
			?>
			<input type="submit" value="Editar">
		</form>
	</fieldset>
	<fieldset>
		
		<legend>Detalles de la materia</legend>
		<form action="opciones.php" method="post">
			<input type="hidden" name="id_materia" value="<?php echo (isset($materia['id_materia'])) ? $materia['id_materia'] : ''; ?>">
			<input type="hidden" name="action" value=<?php echo (isset($materia)) ? "modificar_materia" : "nueva_materia" ; ?>>
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
	<fieldset>
		<legend>D&iacute;as no laborables</legend>
		<form method="POST" action="opciones.php" >
			<input type="hidden" name="action" value="no_laborables">
			<input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>">
			<input type="submit" value="Agregar">
		</form>	
			<ul>
				<?php foreach($no_laborables as $id => $fecha) : ?>
					<li>
						<?php echo date('d/m/Y',strtotime($fecha)); ?> 
						<a href="opciones.php?action=borrar_no_laborable&id=<?php echo $id; ?>" 
							onclick="confirmar(event,'Borrar día seleccionado?')">[Borrar]
						</a>
					</li>
				<?php endforeach; ?>
				
			</ul>
			

		
	</fieldset>
</div>
</body>
<script type="text/javascript">
	setTimeout(function(){ if( $("#notificacion") != 'undefined'){$("#notificacion").fadeOut()} } , 3000);
	
	//recibe el evento ocurrido y un mensaje para mostrar al usuario. Si el usuario no confirma, el evento se anula.
	function confirmar(evento,mensaje)
	{
		if(!confirm(mensaje)){
			evento.preventDefault();
		}
	}

	window.history.pushState({}, '', 'opciones.php');
	
</script>
</html>