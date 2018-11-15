<?php 
	
	require_once __DIR__."/../lib/MRBS/DB.php";
	require_once __DIR__."/../lib/MRBS/DB_pgsql.php";		


class Adaptador{
	private $materias;
	private $db;
	
	function __construct()
	{
		$this->conectar_db();
		//$this->ejecutar_carga_materias();
		//$this->conectar_db();
	}

	function conectar_db()
	{
		include __DIR__."/../config.inc.php";
		$this->db = new MRBS\DB_pgsql($db_host, $db_login, $db_password,$db_database, $persist = 0, $db_port = null);
	}

	function get_materias($filtro = array())
	{
		$where = array();
		if(isset($filtro['id_materia'])){
			$where[] = " AND mat.id_materia = ".$filtro['id_materia'];
		}
		$sql = "select id_materia, materia, es_materia, carrera, array_to_string(array_agg(codigo),',') as codigos, color
				from materias as mat
				left join materias_codigos as cod using (id_materia)
				WHERE 1 = 1";
		foreach($where as $cond){
			$sql .= $cond;
		}
		$sql .= "group by id_materia, materia, es_materia, carrera";
		$resultado = $this->db->query($sql);
		return $resultado->all_rows_keyed();
	}

	function get_materia($id_materia)
	{
		if(!is_numeric($id_materia)){
			return array();
	}
		$sql = "SELECT * FROM materias WHERE id_materia = ".$this->quote($id_materia);
		$resultado = $this->db->query($sql);
		return $resultado->all_rows_keyed()[0];

	}
	function get_color_materia($id_materia)
	{
		$sql = "SELECT color FROM materias WHERE id_materia = ".$this->quote($id_materia);
		return $this->db->query1($sql);
	}

	function get_colores()
	{
		$sql = "SELECT id_materia, color FROM materias";
		$resultado = $this->db->query($sql);
		$resultado = $resultado->all_rows_keyed();
		$colores = array();
		foreach($resultado as $color){
			$colores[$color['id_materia']] = $color['color'];
		}
		return $colores;
	}

	function get_nombres_materias()
	{
		$sql = "SELECT id_materia,materia FROM materias";
		$resultado = $this->db->query($sql)->all_rows_keyed();
		foreach ($resultado as $materia) {
			$materias[$materia['id_materia']] = $materia['materia'];
		}
		
		return $materias;

	}

	function eliminar_acentos($string)
	{
		return str_replace(array('á','é','í','ó','ú','Á','É','Í','Ó','Ú'),array('a','e','i','o','u','A','E','I','O','U'),$string);
	}



	function get_nombre_materia($id_materia)
	{
		$codigos = explode(',',$id_materia);
		//si hay varios codigos, obtengo solo el ultimo con el objetivo de buscar el nombre de la materia
		if(count($codigos)> 1 || (substr($id_materia,0,1) == 'I' && is_numeric(substr($id_materia,1,2)))){
			$id_materia = array_pop($codigos);
			$id_materia = "(select id_materia from materias_codigos where codigo = '$id_materia')";
		}else{
			//si es cualquier otra cosa, se retorna vacio
			if(!is_numeric($id_materia)){
				return array();
			}	
			
		}
		
		
		$sql = "SELECT * FROM materias WHERE id_materia = $id_materia";
		$resultado = $this->db->query($sql)->all_rows_keyed();
		return (count($resultado) > 0) ? $resultado[0]['materia'] : array();
		//devuelve 
//array(1) { [0]=> array(4) { ["id_materia"]=> int(69) ["materia"]=> string(12) "MATEMATICA I" ["es_materia"]=> string(1) "S" ["carrera"]=> string(3) "AGR" } }
	}

	function quote($elemento)
	{
		return "'".$elemento."'";
	}

	function generar_select($etiqueta, $name, $seleccionado='', $solo_materias=false)
	{

		$sql = "SELECT id_materia, materia, es_materia FROM materias";
		$sql = ($solo_materias) ? $sql." AND es_materia = 'S'" : $sql;
		$resultado = $this->db->query($sql)->all_rows_keyed();
		foreach($resultado as $materia){	
			$opciones[$materia['id_materia']] = array('materia'=>$materia['materia'],'es_materia'=>$materia['es_materia']);
		}
		asort($opciones);
    	/* -----------------------------------------------------*/
    	$select = '';
    	if(strlen($etiqueta)){
    		$select .= "<div>$etiqueta:</div>";
    	}
		$select .= "<select name='$name'>";
	    foreach ($opciones as $clave => $opcion) {
	      $clase = ($opcion['es_materia'] == 'N') ? "style='background-color:#313f84;color:#FFF'" : '';
	      $selected = ($clave == $seleccionado) ? 'selected' : '';
	      $select .= "<option $clase value='$clave' $selected>".$opcion['materia']."</option>";
	    }
	    $select .= "</select>";
	    return $select;
	}



	/**
	 * FUNCIONES DE BD
	*/
	function editar_materia($detalles)
	{
		//ELIMINO TODOS LOS CODIGOS DE ESA MATERIA (PARA VOLVER A GUARDAR LOS NUEVOS RECIBIDOS)
		$sql = "DELETE FROM materias_codigos WHERE id_materia = ".$this->quote($detalles['id_materia']);
		$this->db->command($sql);


		if(isset($detalles["codigos"]) && strlen(trim($detalles["codigos"])) > 0 ){
			$codigos = explode(',',$detalles["codigos"]);
			foreach ($codigos as $codigo) {
				if($this->existe_codigo($codigo)){
					return false;
				}
			}
		} 
		

		//separo los codigos en un nuevo array (y el id de la materia)
		$codigos = explode(',',$detalles['codigos']);
		
		//elimino indices innecesarios
		$id_materia = $detalles['id_materia'];
		unset($detalles['codigos']);
		unset($detalles['id_materia']);
		
		//paso a mayusculas el nombre de la materia (como le gusta a PINITO!);
		$detalles['materia'] = strtoupper($detalles['materia']);

		if(!isset($detalles['es_materia'])){
			$detalles['es_materia'] = 'N';
		}
		//armo los campos y valores para el insert
		foreach ($detalles as $campo => $valor) {
			$campos[] = $campo." = ".$this->quote($valor);
		}
		$campos = implode(',',$campos);

		$sql = "UPDATE materias SET $campos WHERE id_materia = ".$id_materia;
		//si se guarda la materia, guardo los códigos
		if($this->db->command($sql)){

			foreach ($codigos as $codigo) {
				if(!$this->nuevo_codigo_materia($id_materia,$codigo)){
					return false;
				}
			}
			return TRUE;
		}else{
			return FALSE;
		}
	}


	function nueva_materia($detalles)
	{
		//var_dump($detalles);
		$obligs = array('materia','es_materia','carrera');
		$campos = array();
		$valores = array();
		try {
			//verifico que recibí todos los campos obligatorios
			/*foreach ($obligs as $campo) {
				if(!array_key_exists($campo,$detalles)){
					throw new Exception('No se recibieron todos los datos obligatorios: falta $campo');
				}
		}*/	
			//separo los codigos en un nuevo array (y el id de la materia)
			if(isset($detalles['codigos'])){
				if(is_array($detalles['codigos'])){
					$cods = implode(',',$detalles['codigos']);
				}else{
					$cods = $detalles['codigos'];
				}
				$codigos = (strlen($cods) > 0) ? explode(',',$cods) : array();
				unset($detalles['codigos']);
			}else{
				$codigos = array();
			}
			
			//elimino indices innecesarios
			//unset($detalles['codigos']);
			unset($detalles['id_materia']);
			
			//paso a mayusculas el nombre de la materia (como le gusta a PINITO!);
			$detalles['materia'] = strtoupper($detalles['materia']);

			//armo los campos y valores para el insert
			foreach ($detalles as $campo => $valor) {
				$campos[] = $campo;
				$valores[] = $this->quote($valor); 
			}
			$campos = implode(',',$campos);
			$valores = implode(',',$valores);
			
			$sql = "INSERT INTO materias ($campos) VALUES ($valores)";
			//echo $sql;
			
			//si se guarda la materia, guardo los códigos
			if($this->db->command($sql)){
				$id_materia = $this->db->insert_id('materias','id_materia');
				foreach ($codigos as $codigo) {
					if(!$this->nuevo_codigo_materia($id_materia,$codigo)){
						return false;
					}
				}
				return TRUE;
			}else{
				return FALSE;
			}

		} catch (Exception $e) {
			return 'Ocurri&oacute; un error: '.$e->getMessage();
		}
		
		
		
	}
	function nuevo_codigo_materia($id_materia,$codigo)
	{
		$codigo = substr($codigo,0,5);
		$sql = "INSERT INTO materias_codigos VALUES ('$id_materia','$codigo')";
		//echo $sql;
		return $this->db->command($sql);
	}

	function existe_materia($filtro = array())
	{
		$where = array();
		if(isset($filtro['id_materia'])){
			$where[] = 'id_materia = '.$this->quote($filtro['id_materia']);
		}
		if(isset($filtro['materia'])){
			$where[] = 'lower(materia) = '.$this->quote(strtolower($filtro['materia']))." AND carrera = ".$this->quote($filtro['carrera']);
		}
		$where = implode(' OR ',$where);
		$sql = "SELECT count(*) FROM materias WHERE $where";
		return (intval($this->db->query1($sql)) > 0);
	}

	function existe_codigo($codigo)
	{
		$sql = "SELECT count(*) FROM materias_codigos WHERE codigo = ".$this->quote($codigo);
		return (intval($this->db->query1($sql)) > 0);
	}


	function ejecutar_carga_materias()
	{
		$this->materias = json_decode(file_get_contents(__DIR__.'/materias.json'),TRUE);
		
		foreach($this->materias as $materia){

			$carrera = array_key_exists('carrera',$materia) ? $materia['carrera'] : '---';
			$this->nueva_materia($materia);
			$id_materia = $this->db->insert_id('materias', 'id_materia');
			
			/*foreach($materia['codigos'] as $codigo){
				$this->nuevo_codigo_materia($id_materia,$codigo);
			}*/
		}
		die;
	}
	/*
		CREATE TABLE public.materias
		(
		  id_materia integer NOT NULL DEFAULT nextval('materias_id_materia_seq'::regclass),
		  materia character varying(300),
		  es_materia character(1) DEFAULT 'S'::bpchar,
		  carrera character(3),
		  CONSTRAINT pk_materias PRIMARY KEY (id_materia)
		)
		WITH (
		  OIDS=FALSE
		);
		ALTER TABLE public.materias
		  OWNER TO postgres;


	  -- Table: public.materias_codigos

-- DROP TABLE public.materias_codigos;

	CREATE TABLE public.materias_codigos
	(
	  id_materia smallint NOT NULL,
	  codigo character varying(5) NOT NULL,
	  CONSTRAINT pk_materias_codigos PRIMARY KEY (id_materia, codigo),
	  CONSTRAINT "fk_materias_codigos-materias" FOREIGN KEY (id_materia)
	      REFERENCES public.materias (id_materia) MATCH SIMPLE
	      ON UPDATE CASCADE ON DELETE RESTRICT
	)
	WITH (
	  OIDS=FALSE
	);
	ALTER TABLE public.materias_codigos
	  OWNER TO postgres;




	*/
}

