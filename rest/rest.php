<?php 
if( ! defined('INICIO')){ die('No se permite acceder directamente al script');}
class Rest{
	protected $conexion;

	function __construct(){
		$this->conexion = new Adaptador();
	}

	function clases($fecha = NULL){
		$fecha = ($fecha) ? $fecha : date('Y-m-d');
		$resumen = $this->conexion->get_cronograma_diario($fecha);
		header('Content-type: application/json; charset=UTF-8');
		echo json_encode(array('fecha' => $fecha, 'clases' => $resumen));		
	}

	function materias($id_materia){
		$materia = $this->conexion->get_materia($id_materia);
		header('Content-type: application/json; charset=UTF-8');
		echo json_encode($materia);	
	}
	
	

	public function dump($variable){
		if(is_array($variable)){
			echo "<table style='border-collapse: collapse; border: 1px solid black;'>\n";
			foreach($variable as $clave => $valor){
				if( is_array($valor)){
					ob_start();
					$nested = dump($valor);
					$tabla = ob_get_contents();
					ob_end_clean();
					echo "<tr style='border: 1px solid black;'>\n
					<td style='border: 1px solid black;'>$clave</td>\n
					<td style='border: 1px solid black;'>$tabla</td>\n</tr>\n";
				}else{
					echo "<tr style='border: 1px solid black;'>\n
					<td style='border: 1px solid black;'>$clave</td>\n
					<td style='border: 1px solid black;'>$valor</td>\n</tr>";
				}
			}
			echo "</table>\n";
		}
		if(is_string($variable)){
			echo $variable;
		}
	}
}
?>