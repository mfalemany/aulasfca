<?php 
class Adaptador{
	private $materias;
	function __construct()
	{
		//echo 'LLEGA'; die;
		$this->materias = json_decode(file_get_contents(__DIR__.'/materias.json'),TRUE);
		if(count($this->get_materias()) == 0){
			die("Ocurri&oacute; un error al cargar las opciones: ".json_last_error_msg());
		}
	}

	function get_materias()
	{
		return $this->materias;
	}

	function get_nombre_materia($codigos)
	{
		foreach($this->materias as $materia){
			if(implode(',',$materia['codigos']) == $codigos){
				return $materia['materia'];
			}
		}
	}

	function get_opciones_select($solo_materias=false)
	{
		$opciones = array();
		foreach($this->materias as $materia){	
			$opciones[implode(',',$materia['codigos'])] = $materia['materia'];	
		}
		asort($opciones);
		return $opciones;

		
	}
}