<!DOCTYPE html>
<html>
<head>
	<title>Resumen</title>
	<meta charset="utf-8">
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" />
	<style type="text/css">
		*{
		
			margin: 0px;
			padding: 0px;
			box-sizing: border-box;
		}
		.contenedor{
			align-items: center;
			display: flex;
			flex-direction: column;
			height: 100vh;
			justify-content: flex-start;
		}
		#cabecera{
			justify-content: center;
			background-color: #2b5094;
			color:#FFFFFF;
			display: flex;
			flex-direction: row;
			font-size: 2rem;
			margin: 0px 0px 0px 0px;
			text-align: center;
			text-shadow: 0px 0px  5px #222;
			width: 100vw;

		}
		#cabecera #reload{
		    border-radius: 50%;
		    display: inline-block;
		    font-size: 70px;
		    height: 60px;
			line-height: 0px;
		    padding: 0px;	
			position: absolute;
		    right: 40px;
		    top: 8px;
		    width: 60px;
		}
		#cabecera h1:first-letter{
			align-self: center;
			
		}
		#cabecera h1:first-letter{
			text-transform: capitalize;
		}
		#opciones{
			align-items: center;
			border: 1px solid #AAA;
			display:flex;
			flex-flow: row wrap;
			justify-content: center;
			margin: 0px 0px 10px 0px;
			padding: 10px 0px;
			width: 100vw;
		}
		.checkbox{
			display: flex;
			flex-flow: row nowrap;
			margin-bottom: 20px;
			width:45%;
		}
		.checkbox input[type='checkbox']{
			align-self: center;	
		    box-shadow: 0px 0px 5px black;
		    border-radius:5px;
		    border:10px solid #111;
		    height:70px;
		    margin-right: 10px;
		    width: 70px;
		}
		.checkbox input[type='checkbox']:checked {
		    background-color: #68a3ce;
		}
		.checkbox label{
			align-self: center;
			font-size: 2rem;
		}
		.clase{
			border: 1px solid #22F;
			border-radius: 5px;
			box-shadow: 0px 0px 10px #111;
			margin-bottom: 20px;
			padding: 7px 20px;
			width: 95vw;
		}
		.clase .hora-aula{
			color: #920808;
			font-size: 35px;
			font-weight: bold;
		}
		.clase .titulo{
			color: #22A;
			font-size: 2.5em;
			font-weight: bold;
		}
		.clase .detalles .descripcion{
			color:#444;
			font-size: 30px;
		}
		.hidden_display{
			display: none;
		}
		.hidden_visibility{
			visibility: hidden;
		}
		

	</style>
</head>
<body>
	<div class="contenedor">
		<div id="cabecera">
			<button id="reload">&#x21bb;</button>
			<h1></h1>
		</div>
		<div id="opciones">
			<div class="checkbox filtro">
				<input type="checkbox" id="mostrar_pasadas">
				<label>Mostrar pasadas</label>	
			</div>
		</div>
		<div id="contenedor_clases">
			
		</div>
		
	</div>

	<template id="template_clase">
		<div class="clase">
			<div class="hora-aula">
				<span class="hora"></span> - 
				<span class="aula"></span>
			</div>
			<div class="titulo"></div>
			<div class="detalles">
				<div class="descripcion"></div>
			</div>
		</div>	
	</template>
	<template id="template_opcion">
		<div class="checkbox filtro">
			<input type="checkbox">
			<label></label>	
		</div>
	</template>

	<script type="text/javascript">
		d = document;
		var clases, hoy, hora_actual, ahora, $mostrar_pasadas, url_base;
		
		hoy = new Date().toLocaleDateString("es-AR",{year:'numeric',month:'2-digit',day:'2-digit'}).split('/').reverse().join('-');
		hora_actual = new Date().toLocaleTimeString("es-AR",{hour:'2-digit', minute:'2-digit', second:'2-digit'});
		//Objeto fecha que me servirá para extraer partes independientes de la hora
		ahora = new Date(`${hoy} ${hora_actual}`);

		d.addEventListener('DOMContentLoaded', async () => {
			//Obtengo las clases de hoy
			datos = await obtenerClases(hoy);
			
			//Filtro para mostrar u ocultar las clases que ya pasaron
			$mostrar_pasadas = d.getElementById('mostrar_pasadas');
			
			//Cargar filtro y clases
			cargarFiltro(datos.clases);

			//Se agregan los listeners a los filtros
			$mostrar_pasadas.addEventListener('change', () => cargarClases(datos));	
			d.querySelectorAll('.filtro input[type=checkbox]').forEach( filtro => {
				filtro.addEventListener('change', () => cargarClases(datos))
			});
			programarReload();
			cargarClases(datos);
		});

		function programarReload(){
			setInterval( async () => {
				datos = await obtenerClases();
				cargarClases(datos);
			}, (1000*60*5));
		}

		async function obtenerClases(fecha = null){
			fecha = (fecha) ? fecha : hoy;
			const config = await fetch('../config_rest.json').then( r => r.json() ).then( json => json);
			const res = await fetch(`${config.url_base}/cronograma_diario/${hoy}`);
			return res.json();
		}


		function cargarClases(datos){
			
			d.querySelector('#cabecera h1').innerText = ahora.toLocaleDateString('es-AR',{weekday:'long',day:'2-digit',month:'long'});
			let fragmento = d.createDocumentFragment();

			const unaHoraMenos = new Date();
			unaHoraMenos.setHours(unaHoraMenos.getHours() - 1);
			
			datos.clases.forEach( clase => {
				if( ! $mostrar_pasadas.checked){
					const inicioClase = new Date(`${datos.fecha} ${clase.hora_inicio}`);
					if(inicioClase.getTime() < unaHoraMenos.getTime() ){
						return;
					}	
				}

				//verifico si el filtro de lugar, correspondiente al lugar de esta clase, está activo o no
				if( ! d.querySelector(`.filtro[data-lugar='${clase.lugar}'] input[type=checkbox]`).checked){
					return;
				}
				
				let template = d.getElementById('template_clase').content;
				template.querySelector('.clase').dataset.lugar = clase.lugar;
				template.querySelector('.clase').dataset.hora = clase.hora_inicio;
				template.querySelector('.clase').dataset.fecha = datos.fecha;
				let horario = `${clase.hora_inicio.substring(0,5)} a ${clase.hora_fin.substring(0,5)}`;
				template.querySelector('.hora').innerText = horario;
				template.querySelector('.aula').innerText = clase.aula;
				template.querySelector('.titulo').innerText = clase.materia;
				template.querySelector('.descripcion').innerText = clase.descripcion;
				let clone = document.importNode(template, true);
				fragmento.appendChild(clone);
			});
			d.getElementById('contenedor_clases').innerHTML = '';
			d.getElementById('contenedor_clases').appendChild(fragmento);
			return clases;
		}

		async function cargarFiltro(clases){
			//Obtengo todos los lugares, sin duplicados
			lugares = new Set(clases.map( clase => clase.lugar));

			let template = d.getElementById('template_opcion').content;
			let fragmento = d.createDocumentFragment();

			lugares.forEach( lugar => {
				template.querySelector('label').innerText = lugar;
				template.querySelector('input[type=checkbox]').checked = true;
				template.querySelector('.filtro').dataset.lugar = lugar;
				let clone = document.importNode(template, true);
				fragmento.appendChild(clone);
			})
			d.getElementById('opciones').appendChild(fragmento);
		}

	</script>
</body>
</html>