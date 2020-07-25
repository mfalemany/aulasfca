<!DOCTYPE html>
<html>
<head>
	<title>Resumen</title>
	<meta charset="utf-8">
	<style type="text/css">
		*,
		::before,
		::after{
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
			background-color: #68a3ce;
			color:#FFFFFF;
			display: flex;
			flex-direction: row;
			margin: 0px 0px 20px 0px;
			max-height: 5vh;
			min-height: 45px;
			text-align: center;
			text-shadow: 0px 0px  5px #222;
			width: 100vw;
		}
		#cabecera h1{
			align-self: center;
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
		    background-color: #FFF;
		    border-radius:5px;
		    border:2px solid #555;
		    height:30px;
		    margin-right: 10px;
			
		    width:30px;
	
		}
		.checkbox input[type='checkbox']:checked {
		    background-color: #68a3ce;
		}
		.checkbox label{
			align-self: center;
			font-size: 1.3em;
		}
		.clase{
			border: 1px solid #22F;
			border-radius: 5px;
			box-shadow: 0px 0px 4px #949494;
			margin-bottom: 10px;
			padding: 7px 20px;
			width: 95%;
		}
		.clase .hora-aula .hora{
			color: #920808;
			font-weight: bold;
		}
		.clase .titulo{
			color: #22A;
			font-size: 1.7em;
			font-weight: bold;
		}
		.clase .detalles .descripcion{
			color:#444;
		}
		

	</style>
</head>
<body>
	<div class="contenedor">
		<div id="cabecera">
			<h1>Miércoles 23 de Julio</h1>
		</div>
		<div id="opciones">
			<div class="checkbox">
				<input type="checkbox" checked>
				<label>Ocultar pasadas</label>	
			</div>
		</div>

		<div class="clase">
			<div class="hora-aula">
				<span class="hora">08:00 a 14:00</span> - 
				<span class="aula">Aula de Microscopía D1</span>
			</div>
			<div class="titulo">Morfología de Plantas Vasculares</div>
			<div class="detalles">
				<div class="descripcion">Exámen parcial. Prof. Acosta. Grupos 1 y 2 de Matemática</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		d = document;
		d.addEventListener('DOMContentLoaded', async () => {
			res = await cargarClases();
			console.log(res);
		});

		async function cargarClases(){
			const clases = await fetch('http://aulas.agr.unne.edu.ar/rest/cronograma_diario');
			return await clases.json();
		}
		async function cargarFiltro(){

		}
	</script>
</body>
</html>