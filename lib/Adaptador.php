<?php 
	
	require_once __DIR__."/../lib/MRBS/DB.php";
	require_once __DIR__."/../lib/MRBS/DB_pgsql.php";	
	if(!isset($_SESSION)){
		session_start();
	}	


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

	/** ==========================================================================================
	 *  ============================ MATERIAS ====================================================
	 *========================================================================================== */

	function get_materia($id_materia = NULL)
	{
		$sql = "SELECT
					mat.id_materia,
					mat.materia,
					mat.es_materia,
					mat.carrera,
					mat.color,
					array_to_string(array_agg(mc.codigo),',') as codigos
				FROM materias AS mat
				LEFT JOIN materias_codigos as mc ON mc.id_materia = mat.id_materia";
				if($id_materia){
					$sql .= " WHERE mat.id_materia = ".$this->quote($id_materia);
				}	
				$sql .= " GROUP BY mat.id_materia, mat.materia, mat.es_materia, mat.carrera, mat.color
				ORDER BY materia";

		$resultado = $this->db->query($sql);
		$resultado =  $resultado->all_rows_keyed();
		return ($id_materia && isset($resultado[0])) ? $resultado[0] : $resultado;
	}

	function get_nombre_materia($id_materia)
	{
		$materia = $this->get_materia($id_materia);
		return (count($materia)) ? $materia['materia'] : 'Materia no encontrada';
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

	function editar_materia($detalles)
	{
		
		//ELIMINO TODOS LOS CODIGOS DE ESA MATERIA (PARA VOLVER A GUARDAR LOS NUEVOS RECIBIDOS)
		$sql = "DELETE FROM materias_codigos WHERE id_materia = ".$this->quote($detalles['id_materia']);
		$this->db->command($sql);

		//Si existen códigos, los convierto en un array
		if(isset($detalles["codigos"]) && strlen(trim($detalles["codigos"])) > 0 ){
			$codigos = explode(',',$detalles['codigos']);
		}
		//elimino indices innecesarios
		$id_materia = $detalles['id_materia'];
		unset($detalles['codigos']);
		unset($detalles['id_materia']);
		
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
		$resultado = $this->db->command($sql);
		if($resultado){
			if(isset($codigos)){
				foreach ($codigos as $codigo) {
					if(!$this->nuevo_codigo_materia($id_materia,$codigo)){
						return false;
					}
				}
			}
			return TRUE;
		}else{
			return FALSE;
		}
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
	
	function nuevo_codigo_materia($id_materia,$codigo)
	{
		$codigo = substr($codigo,0,5);
		$sql = "INSERT INTO materias_codigos VALUES ($id_materia,'$codigo')";
		return $this->db->command($sql);
	}

	function existe_codigo($codigo)
	{
		$sql = "SELECT count(*) FROM materias_codigos WHERE codigo = ".$this->quote($codigo);
		return (intval($this->db->query1($sql)) > 0);
	}

	/** ==========================================================================================
	 *  ============================ ESPACIOS ====================================================
	 *========================================================================================== */
	function get_info_aula($id_aula){
		$sql = "select r.room_name as aula, a.area_name as edificio 
				from mrbs_room as r,mrbs_area as a
				where r.area_id = a.id
				and r.id = ".$this->quote($id_aula);
		$resultado = $this->db->query($sql)->all_rows_keyed();
		return (isset($resultado[0])) ? $resultado[0] : FALSE;
	}


	/** ==========================================================================================
	 *  ============================ COLORES ====================================================
	 *========================================================================================== */
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

	
	/** ==========================================================================================
	 *  ============================ AUXILIARES ==================================================
	 *========================================================================================== */
	function get_fecha_reserva($id_reserva)
	{
		$sql = "SELECT * FROM mrbs_entry WHERE id = ".sanitize($id_reserva);
		$resultado = $this->db->query($sql)->all_rows_keyed();
		return $resultado;
	}

	function eliminar_acentos($string)
	{
		return str_replace(array('á','é','í','ó','ú','Á','É','Í','Ó','Ú'),array('a','e','i','o','u','A','E','I','O','U'),$string);
	}

	function quote($texto)
	{
		$texto_limpio = '';
		$invalidos = array('/truncate/i','/update/i','/insert/i','/delete/i','/sleep/i','/drop/i','/--/','/\//');
		$texto_limpio = preg_replace($invalidos,'',$texto);
		return "'".addslashes($texto_limpio)."'";
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

	function cleanInput($input) {
 
		$search = array(
			'@<script[^>]*?>.*?</script>@si',   // Elimina javascript
			'@<[\/\!]*?[^<>]*?>@si',            // Elimina las etiquetas HTML
			'@<style[^>]*?>.*?</style>@siU',    // Elimina las etiquetas de estilo
			'@<![\s\S]*?--[ \t\n\r]*>@'         // Elimina los comentarios multi-línea
		);

		$output = preg_replace($search, '', $input);
			return $output;
		}

	function sanitize($input) {
		if (is_array($input)) {
			foreach($input as $var=>$val) {
				$output[$var] = $this->sanitize($val);
			}
		}
		else {
			if (get_magic_quotes_gpc()) {
				$input = stripslashes($input);
			}
			$output  = $this->cleanInput($input);
			
		}
		return $output;
	}

	/** ==========================================================================================
	 *  ============================ DÍAS NO LABORABLES ==========================================
	 *========================================================================================== */
	function borrar_no_laborable($id)
	{
		if(!is_numeric($id)){
			return FALSE;
		}
		$sql = "DELETE FROM no_laborables WHERE id = $id";
		return $this->db->command($sql);
	}

	function guardar_no_laborable($fecha)
	{
		$sql = "INSERT INTO no_laborables (fecha) VALUES ('".$this->sanitize($fecha)."')";
		try {
			return $this->db->command($sql);

		} catch (MRBS\DBException $e) {
			
			$_SESSION['notificaciones'][] = 'No se pudo guardar el d&iacute;a no laborable. Posiblemente ya se encuentre registrado.';
			return FALSE;
		}
		
	}

	function get_no_laborables($anio = NULL)
	{
		$anio = ($anio) ? $anio : date('Y');
		$sql = "SELECT id, fecha FROM no_laborables WHERE EXTRACT(year FROM fecha) = $anio ORDER BY fecha ASC";
		$resultado = $this->db->query($sql);
		$no_laborables = array();
		$resultado = $resultado->all_rows_keyed();
		foreach ($resultado as $no_laborable) {
			$no_laborables[$no_laborable['id']] = $no_laborable['fecha'];
		}
		return $no_laborables;
	}

	/** ==========================================================================================
	 *  ============================ RESUMEN DE CLASES ===========================================
	 *========================================================================================== */

	/*
	Retorna las reservas de un dia determinado (pasado como parámetro).
	 */
	function get_cronograma_diario($fecha){
		if($fecha){
			$partes = explode('-',str_replace(array('.','-','/',),'-',$fecha));
			if(count($partes) == 3){
				if(    strlen($partes[0]) == 4  &&  is_numeric($partes[0])  
					&& strlen($partes[1]) == 2  &&  is_numeric($partes[1])
					&& strlen($partes[2]) == 2  &&  is_numeric($partes[2])){
					//Es una fecha válida?  (checkdate(month,day,year))
					if(checkdate($partes[1],$partes[2],$partes[0])){
						$fecha = implode('-',$partes);
					}else{
						$fecha = date('Y-m-d');		
					}
				}else{
					$fecha = date('Y-m-d');
				}
			}else{
				$fecha = date('Y-m-d');
			}
		}
		
		$sql = "select 
				    (to_timestamp(e.start_time) AT time zone 'America/Buenos_aires')::date as fecha, 
				    (to_timestamp(e.start_time) AT time zone 'America/Buenos_aires')::time as hora_inicio, 
				    (to_timestamp(e.end_time) AT time zone 'America/Buenos_aires')::time as hora_fin, 
				    a.area_name as lugar,
				    r.room_name as aula,
				    m.materia,
				    e.description as descripcion
				from mrbs_entry as e
				left join mrbs_room as r on r.id = e.room_id
				left join materias as m on m.id_materia = e.name::integer
				left join mrbs_area as a on a.id = r.area_id
				where to_timestamp(e.start_time)::date = '$fecha'
				order by to_timestamp(e.start_time)::time asc";
		return $this->db->query($sql)->all_rows_keyed();
		
	}


	function publicar_anuncio($titulo,$cuerpo,$carrera){
		$raw_content = json_encode(array('titulo'=>$titulo,'cuerpo'=>$cuerpo,'carrera'=>$carrera,'usuario'=>9));
		//Obtengo la configuracion
		$config = file_get_contents(dirname(__FILE__)."/../rest/rest_config.json");
		$config = json_decode($config,TRUE);
		$url = $config['url_rest_anuncios'].'/publicar';
		$ch = curl_init();
		//Opciones de Curl
		curl_setopt_array($ch, 
			[CURLOPT_HTTPAUTH      => CURLAUTH_BASIC,
			CURLOPT_USERPWD        => $config["usuario_anuncios"].':'.$config["clave_anuncios"],
			CURLOPT_URL            => $url,
			CURLOPT_PROXY          => '',
			CURLOPT_POST           => TRUE,
			CURLOPT_HTTPHEADER     => array('Content-Type:application/json'),
			CURLOPT_POSTFIELDS     => $raw_content,
			CURLOPT_RETURNTRANSFER => true
		]);
		
		$res = curl_exec($ch);
		curl_close($ch);
	}
	
	
}

