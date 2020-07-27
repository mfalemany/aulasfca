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
			flex-flow: row nowrap;
			justify-content: space-evenly;
			margin: 0px 0px 20px 0px;
			padding: 10px 0px;
			width: 100vw;
		}
		.checkbox{
			display: flex;
			flex-flow: row nowrap;
		}
		.checkbox input[type='checkbox']{
			align-self: center;	
		    box-shadow: 0px 0px 5px black;
		    border-radius:5px;
		    border:10px solid #111;
		    height:40px;
		    margin-right: 10px;
		    width: 40px;
		}
		.checkbox input[type='checkbox']:checked {
		    background-color: #68a3ce;
		}
		.checkbox label{
			align-self: center;
			font-size: 2.5em;
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
		

	</style>
</head>
<body>
	<div class="contenedor">
		<div id="cabecera">
			<h1></h1>
		</div>
		<div id="opciones">
			<div class="checkbox">
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
				<span class="hora">08:00 a 14:00</span> - 
				<span class="aula">Aula de Microscopía D1</span>
			</div>
			<div class="titulo">Producción y riego en el cultivo de arroz en los plantas</div>
			<div class="detalles">
				<div class="descripcion">Exámen parcial. Prof. Acosta. Grupos 1 y 2 de Matemática</div>
			</div>
		</div>	
	</template>

	<script type="text/javascript">
		d = document;
		var clases, hoy, hora_actual, ahora, $mostrar_pasadas;

		d.addEventListener('DOMContentLoaded', async () => {
			hoy = new Date().toLocaleDateString("es-AR",{year:'numeric',month:'2-digit',day:'2-digit'}).split('/').reverse().join('-');

			hora_actual = new Date().toLocaleTimeString("es-AR",{hour:'2-digit', minute:'2-digit', second:'2-digit'});
			
			//Objeto fecha que me servirá para extraer partes independientes de la hora
			ahora = new Date(`${hoy} ${hora_actual}`);

			const res = await fetch(`http://192.168.0.52/aulas/rest/cronograma_diario/${hoy}`);
			datos = await res.json();
			
			$mostrar_pasadas = d.getElementById('mostrar_pasadas');
			$mostrar_pasadas.addEventListener('change', () => cargarClases() );
			cargarClases(hoy);
		});


		async function cargarClases(fecha = null){
			fecha = (fecha) ? fecha : hoy;
			d.querySelector('#cabecera h1').innerText = ahora.toLocaleDateString('es-AR',{weekday:'long',day:'2-digit',month:'long'});
			let fragmento = d.createDocumentFragment();

			const unaHoraMenos = new Date();
			unaHoraMenos.setHours(unaHoraMenos.getHours() - 1);
			datos.clases.forEach( clase => {
				//Se muestran o no las clases ya pasadas
				if( ! $mostrar_pasadas.checked){
					const inicioClase = new Date(`${fecha} ${clase.hora_inicio}`);
					if(inicioClase.getTime() < unaHoraMenos.getTime() ){
						return;
					}	
				}
				template = d.getElementById('template_clase').content;
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

		async function cargarFiltro(){

		}

	</script>
</body>
</html>