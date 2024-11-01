<?php




//**************** 		FUNCIONES DE FECHA     *******************//


// converteix data de mysql a data català
function av_date_to_data($date){
	$date=str_replace("/","-",$date);
	$data=date("j-m-Y",strtotime($date));
	return $data;
}
// converteix data de mysql a data català
function av_data_to_date($data){
	$data=str_replace("/","-",$data);
	$date=date("Y-m-d",strtotime($data));
	return $date;
}
// retorna true/false si comproba que l'estring passat compleix el format d'una data (català)
function av_es_data($data){
if(strlen($data)>5 && strlen($data)<11){
	$data_guio=explode("-",$data);
	$data_barra=explode("/",$data);
	if(count($data_guio)==3 || count($data_barra)==3){
		if(((strlen($data_guio[0])==1 || strlen($data_guio[0])==2) && (strlen($data_guio[1])==1 || strlen($data_guio[1])==2) && (strlen($data_guio[2])==2 || strlen($data_guio[2])==4)) || ((strlen($data_barra[0])==2 || strlen($data_barra[0])==1) && (strlen($data_barra[1])==1 || strlen($data_barra[1])==2) && (strlen($data_barra[2])==2 || strlen($data_barra[2])==4))){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}else{
	return false;
}
}
// retorna true/false si comproba que l'estring passat compleix el format d'una date (mysql)
function av_es_date($date){
if(strlen($date)>5 && strlen($date)<11){
	$date_guio=explode("-",$date);
	$date_barra=explode("/",$date);
	if(count($date_guio)==3 || count($date_barra)==3){
		if(((strlen($date_guio[0])==2 || strlen($date_guio[0])==4) && (strlen($date_guio[1])==1 || strlen($date_guio[1])==2) && (strlen($date_guio[2])==1 || strlen($date_guio[2])==2)) || ((strlen($date_barra[0])==2 || strlen($date_barra[0])==4) && (strlen($date_barra[1])==1 || strlen($date_barra[1])==2) && (strlen($date_barra[2])==1 || strlen($date_barra[2])==2))){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}else{
	return false;
}
}
//**************** 		FUNCIONS amb carpetes i arxius     *******************//


/******************  FUNCIONS ARXIUS *************************/


/**
 * funcion que se encarga de copiar un archivo
 */
 function av_archivo_copiar($origen,$destino){
    copy($origen,$destino);
 }
// la funció arxiu crear - està en arxiu_pujar.php

function av_arxiu_eliminar($arxivo){
	@unlink($arxivo);
	if(file_exists($arxivo)){
		return false;
	}else{
		return true;
	}
}




//** Funció que retorna l'extensió de l'arxiu (string) en minuscules
function av_arxiu_extensio($arxiu){
	$pos=strrpos($arxiu,".");
	if($pos>0){
		$ext=strtolower(substr($arxiu,$pos+1,strlen($arxiu)));
	}else{
		$ext=0;
	}
	return $ext;
}
/**
 * av_archivo_tipo_grupos
 */
function av_archivo_tipo_grupos(){
    $grupo['imagen']    = array("jpg","jpeg","png","bmp","gif");
    $grupo['audio']     = array('wma','mp3','mp4','wav');
    $grupo['video']     = array('wmv','avi','mpeg','swf','flv');
    $grupo['word']      = array('doc','docx');
    $grupo['excel']     = array('xls','xlsx');
    $grupo['powerpoint']= array('ppt','pptx','pps','ppsx');
    
    return $grupo;
}
/**
 * av_archivo_tipo
 */
function av_archivo_tipo($ext){
    $grupos = av_archivo_tipo_grupos();
    foreach($grupos as $grupo => $extensiones){
        if(in_array($ext,$grupos[$grupo])){
            return $grupo;
        }
    }
    return "file";
}
/**
 * av_archivo_partes
 */
function av_archivo_partes($archivo){
    $ultima_barra = strrpos($archivo,"/");
    
    if($ultima_barra !== false){
        $partes['carpeta'] = substr($archivo,0,$ultima_barra+1);
        $partes['archivo'] = substr($archivo,$ultima_barra+1);
        
    }else{
        $partes['carpeta'] = "";
        $partes['archivo'] = $archivo;
    }
        $partes['extension'] = av_arxiu_extensio($archivo);
    return $partes;
}
/**
 * function av_archivo_info
 * 
 * devuelve un array con los indices:
 *      archivo   - nombre i extension del archivo
 *      extension - extension del archivo
 *      carpeta   - ruta del archivo (sin el nombre de archivo) . finaliza con /
 * 
 *  devuelve 0 en caso de no encontrar el archivo
 */ 
 function av_archivo_info($archivo){
    
    $retornar['existe'] = (file_exists($archivo)) ? 1 : 0 ;
    
    $retornar['ancho']  = 50;
    $retornar['alto']   = 50;
    $retornar['ruta']   = $archivo;    
    
    $partes = av_archivo_partes($archivo);
    $retornar['carpeta']    = $partes['carpeta'];
    $retornar['archivo']    = $partes['archivo'];
    $retornar['extension']  = $partes['extension'];
    
    $retornar['size']       = (file_exists($archivo)) ? filesize($archivo) : 0 ;
    $retornar['tipo']       = av_archivo_tipo($retornar['extension']);
    
    if($retornar['tipo']=="imagen"){
        $midas = @getimagesize($archivo);
        $retornar['ancho']  = $midas[0];
        $retornar['alto']   = $midas[1];
    }
    return $retornar;
 }
 
 
 function av_cadena_limpiar($cadena){
    $cadena = trim($cadena);
	$cadena = strtr($cadena,
"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
"aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn");
	$cadena = strtr($cadena,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz");
	$cadena = preg_replace('#([^.a-z0-9]+)#i', '_', $cadena);
        $cadena = preg_replace('#-{2,}#','_',$cadena);
        $cadena = preg_replace('#-$#','',$cadena);
        $cadena = preg_replace('#^-#','',$cadena);
	return $cadena;
 }
 
 
  
/**
 *  function av_subir_archivo
 *  Función que intenta subir un archivo al servidor.
 * 
 *  Entran el archivo $_FILE, el archivo destino y limitaciones ([extensiones_permitidas],[max_mb],[sobreescribir])
 * 
 *  Devuelve un array $retornar con los indices:
 *          estado   - 0 error / 1 ok  
 *          mensaje  - en funcion del estado
 *          archivo  - nombre del archivo con la extension (sin la ruta hacia el archivo)
 *          ruta     - nombre completo (ruta + archivo)
 *          extension - extension del archivo;                        
 */
function av_archivo_subir($archivo,$carpeta_destino,$nombre_archivo_destino,$extensiones_permitidas="",$max_mb=2,$sobreescribir=false){
    // códigos de error en $_FILE[]['error']
    $uploadErrors = array(
        0=>"Archivo subido correctamente",
        1=>"El archivo subido excede la directiva upload_max_filesize en php.ini",
        2=>"El archivo subido excede la directiva MAX_FILE_SIZE",
        3=>"El archivo se ha subido solo parcialmente",
        4=>"No se ha subido ningun archivo",
        6=>"No se encuentra el directorio temporal"
	);
    
    if(!is_array($extensiones_permitidas)) $extensiones_permitidas = explode(",",$extensiones_permitidas);
    
    // recogemos i separamos la información del archivo destino
    
    $extension              = av_arxiu_extensio($archivo['name']);
    $nombre_archivo_destino = av_cadena_limpiar($nombre_archivo_destino);
    
    $ruta_archivo_destino = $carpeta_destino."/".$nombre_archivo_destino.".".$extension;
    $max_size             = $max_mb * 1000000;
    
    $retornar = array();
    $retornar['estado']     = 0;                               // se inicializa el estado como NO valido 0 - error / 1 - ok
    $retornar['mensaje']    = "Archivo subido correctamente";  // Mensaje sobre el estado
    $retornar['archivo']    = $nombre_archivo_destino;                 
    $retornar['ruta']       = $ruta_archivo_destino;           // la ruta del archivo subido
    $retornar['extension']  = $extension;
    $retornar['tipo']       = av_archivo_tipo($extension);
    // Comprobar las dimensiones maximas permitidas por el servidor
	$INI_MAX_SIZE = ini_get('post_max_size');
	$unit = strtoupper(substr($INI_MAX_SIZE, -1));
	$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));
	
    if ($max_size > $multiplier * (int)$INI_MAX_SIZE && $INI_MAX_SIZE) {
        $retornar['mensaje'] = "El servidor no permite archivos tan grandes.";
        return $retornar;
	}

    // Validatar el archivo subido por el servidor
	if ($archivo['name'] == "") {   
        $retornar['mensaje'] = "No se ha encontrado el archivo.";
        return $retornar;
	} else if ($archivo['error'] != 0) {      
        $retornar['mensaje'] = $uploadErrors[$archivo['error']];
        return $retornar;
	} else if (!@is_uploaded_file($archivo['tmp_name'])) {        
        $retornar['mensaje'] = "No se ha podido comprobar que exista el archivo temporal (is_uploaded_file).";
        return $retornar;
	} 

    // Validar el tamaño de archivo segun la propiedad max_size
	if ($archivo['size'] > $max_size) {      
        $retornar['mensaje'] = "El archivo no puede ser mayor de ".$max_size." Kb";
        return $retornar;
	}
	if ($archivo['size'] <= 0) {       
        $retornar['mensaje'] = "Archivo con 0 Kb";
        return $retornar;
	}

    // Validar el nombre del archivo (for our purposes we'll just remove invalid characters)
	if (strlen($archivo['name']) == 0 || $archivo['name'] =="" || strlen($ruta_archivo_destino) < 6) {       
        $retornar['mensaje'] = "Nombre de archivo incorrecto (o demasiado peque&ntilde;o) o el archivo no tiene nombre.";
        return $retornar;
	}
    // comprobar que el archivo no existe
	if (file_exists($ruta_archivo_destino) && $sobreescribir == false) {       
        $retornar['mensaje'] = "El archivo ya existe en esta carpeta. \n Prueba de cambiar el nombre.";
        return $retornar;
	}
    
    // Validar la extensión del archivo
	if(!in_array($extension,$extensiones_permitidas) && $extensiones_permitidas!=""){      
        $retornar['mensaje'] = "El tipo de archivo '.".$extension."' No est&aacute; permitido en (".implode(" - ",$extensiones_permitidas).")";
        return $retornar;
	}
    
    // validar que se ha subido correctamente el archivo.
    if (!@move_uploaded_file($archivo['tmp_name'], $ruta_archivo_destino)) {    
        $retornar['mensaje'] = "No se ha podido subir el archivo (move_uploaded_file).";
        return $retornar;
	}
    
    $retornar['estado'] = 1;
    return $retornar;

 }
function av_redimensionar($ancho,$alto,$ancho_max,$alto_max){
    
    if(trim($ancho) !="" && trim($ancho) !=0 && trim($alto) !="" && trim($alto) !=0){
        if(trim($ancho) =="" || trim($ancho) ==0)
            $ancho = $alto;
        if(trim($alto) =="" || trim($alto) ==0)
            $alto = $ancho;
        $ratioh = @($alto_max/$alto); 
        $ratiow = @($ancho_max/$ancho); 
        $ratio = min($ratioh, $ratiow); 
        // nuevas dimensiones 
        $dim['ancho'] = intval($ratio*$ancho); 
        $dim['alto']  = intval($ratio*$alto);
    }else{
        $dim['ancho'] = $ancho; 
        $dim['alto']  = $alto;
    }
     
    
    return $dim;
}




	
?>