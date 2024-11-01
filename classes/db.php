<?php

/**
 * Clase Db
 * creado: 28-05-2011
 * @author Xiaobai
 */

 class Db{
    /**
     * Datos de conexión a la Base de datos
     */
  
       public $host = DB_HOST;
       public $db   = DB_NAME;
       public $user = DB_USER;
       public $pass = DB_PASSWORD;
  
    
    
    
    // recurso identificador de la conexion
    public $con = "";
    
    function __construct(){
        $this->con = $this->conectar();
    }
    
    function set_db($host,$db_name,$user,$pass){
         $this->host = $host;
         $this->db   = $db_name;
         $this->user = $user;
         $this->pass = $pass;
    }
    function conectar(){
        
        $con = mysql_connect($this->host,$this->user,$this->pass) or die ("Fallo conexion");
        mysql_select_db($this->db,$con);
        mysql_query("SET NAMES UTF8");
        return $con;
    }
    
    function insert($taula,$camps,$valors){
	
    	$va=array();
    	for($c=0 ; $c < sizeof($valors) ; $c++){
    		if(is_numeric($valors[$c])){
    			$va[]=$valors[$c];
    		}else{
    		     $valor = str_replace(",",";:",$valors[$c]);
                  $valor = addslashes($valor);
    			$va[]="'".$valor."'";
    		}
    	}
    	if(is_array($camps))
    	   $camps = implode(",",$camps);
    	
    	
    	$valores=implode(",",$va);
        
    	$query="INSERT INTO $taula ($camps) VALUES ($valores)";
      
       $result = mysql_query($query);
        
        $retornar['estado'] = 1;
        $retornar['mensaje'] = "Registro Insertado correctamente.";
        
        if(!$result){
            $retornar['estado']  = 0;
            $retornar['mensaje'] = mysql_error($query);
        }
        
        return $retornar;
       
          
    }
    function insertar(){
    //  print_r($_POST);
	$taula=$_POST['nom_taula'];
	
	$camps="";
	$valors=array();
	foreach($_POST as $key => $value){
		if($key!="submit" && $key!="nom_taula"){
			$camps.=$key.",";
			if(av_es_data($value)){
				$valors[]=av_data_to_date($value);
			}else{
				$valors[]=addslashes(str_replace(",",";:",$value));
			}
		$camps_post[] = $key;
		}
	}
	
	
	
	$camps=substr($camps,0,strlen($camps)-1);

	$html = $this->insert($taula,$camps,$valors);
    return $html;
	
    }
    function update($taula,$camps,$valors,$consulta){
        if(!is_array($camps)){
		  $camps=explode(",",$camps);
	    }
        if(!is_array($valors)){
		  $valors=explode(",",$valors);
	    }

        	$query="UPDATE $taula SET ";
        	$query_igual="";
        	for($c=0 ; $c < sizeof($camps) ; $c++){
        		if(is_numeric($valors[$c])){
        			$query_igual.= "".$camps[$c]."=".$valors[$c].",";
        		}else{
        		  $valor = str_replace(",",";:",$valors[$c]);
                  $valor = addslashes($valor);
        			$query_igual.= "".$camps[$c]."='".$valor."',";
        		}
        	}
       	
        	
        	$query.=substr($query_igual,0,strlen($query_igual)-1);
        	$query.=" WHERE {$consulta}";
        	//echo $query."<br />";
            
        	$result =  mysql_query($query);
            $retornar['estado'] = 1;
            $retornar['mensaje'] = "Registro Modificado correctamente.";
            
            if(!$result){
                $retornar['estado']  = 0;
                $retornar['mensaje'] = mysql_error($query);
            }
            
            return $retornar;
                
             
    }
    function modificar(){
        
        extract($_POST);
    	$taula=$nom_taula;
    	$id=$id_reg;
        //print_r($_POST);
    	
   	    $camps="";
     	$valors=array();
     	foreach($_POST as $key => $value){
     		if($key!="submit" && $key!="nom_taula" && $key!="id_reg"){
     			$camps.=$key.",";
     			if(av_es_data($value)){
     				$valors[]=av_data_to_date($value);
     			}else{
     				$valors[]=addslashes(str_replace(",",";:",$value));
     			}
     		}
     		
     	}

     	
     	$camps=substr($camps,0,strlen($camps)-1);
     	$html = $this->update($taula,$camps,$valors,"id=$id");
  
        return $html;
    	 
    }
    function delete($tabla,$consulta){
             $query = "delete from $tabla where {$consulta}";
            mysql_query($query) or die (mysql_error());
            
    }
    function eliminar(){
        return "Accion eliminar";
    }
    
    function dato($campo,$taula,$consulta){
        if($this->aux_existe_campo_en_tabla($taula,$campo)){
            $query="select $campo from $taula where {$consulta}";
            $res = mysql_query($query);
            
            $num_filas = mysql_num_rows($res);
            if($num_filas == 0){
                return 0;
            }else{
                 
             while($row = mysql_fetch_array($res)){
                 $dato = stripslashes(str_replace(";:",",",$row[$campo]));   
             }
             return $dato;
            }
        }else{
            return 0;
        }
        
        
    }
    function dato_externo($nombre_campo,$valor){
            
            $nuevo_valor = $valor;
            $dos_primeros = substr($nombre_campo,0,2);
            if($dos_primeros == "id"){
                $tabla_externa = substr($nombre_campo,2);
                $nuevo_valor = $this->dato($tabla_externa,$tabla_externa,"id=".$valor);
            }
            return $nuevo_valor;
        
    }
    function regs($taula,$consulta){
  
        $query="select * from $taula where {$consulta}";
        
        $res = mysql_query($query);
        if(!$res)
            return 0;
        $num_filas = mysql_num_rows($res);
        if($num_filas == 0){
            return 0;
        }else{
             $num_campos = mysql_num_fields($res);
             $index_campo = 0;
             while($row = mysql_fetch_array($res)){
                for($c = 0 ; $c < $num_campos ; $c++){
                    $campo = mysql_field_name($res,$c);
                    $regs[$index_campo][$campo] = stripslashes(str_replace(";:",",",$row[$campo]));   
                }
                $index_campo++;
                
             }
             $retornar = $regs;
        }
        return $retornar;
    }
    function regs_camps($taula,$camps,$consulta){
  
        $query="select $camps from $taula where {$consulta}";
        
        $res = mysql_query($query);
        $num_filas = mysql_num_rows($res);
        if($num_filas == 0){
            $retornar = 0;
        }else{
             $num_campos = mysql_num_fields($res);
             $index_campo = 0;
             while($row = mysql_fetch_array($res)){
                for($c = 0 ; $c < $num_campos ; $c++){
                    $campo = mysql_field_name($res,$c);
                    $regs[$index_campo][$campo] = stripslashes(str_replace(";:",",",$row[$campo]));   
                }
                $index_campo++;
                
             }
             $retornar = $regs;
        }
        return $retornar;
    }
    
    // devuelve un array (autonumerado) con el valor de un campo
    function regs_camp($taula,$campo,$consulta,$distinto=false){
  
        $dis = "";
        if($distinto){
            $dis = "DISTINCT";
        }
        $query="select $dis $campo from $taula where {$consulta}";
        
        $res = mysql_query($query);
        $num_filas = mysql_num_rows($res);
        $regs = array();
        if($num_filas == 0){
            $retornar = 0;
        }else{
             $num_campos = mysql_num_fields($res);
             while($row = mysql_fetch_array($res)){
                    $regs[] = stripslashes(str_replace(";:",",",$row[$campo]));   
             }
             $retornar = $regs;
        }
        return $retornar;
    }
    function regs_id_camp($taula,$camp,$consulta,$nombre_campo_id="id"){
  
        $query="select $nombre_campo_id,$camp from $taula where {$consulta}";
        
        $res = mysql_query($query);
        $num_filas = mysql_num_rows($res);
        if($num_filas == 0){
            $retornar = 0;
        }else{
             while($row = mysql_fetch_array($res)){
                    $regs[$row[$nombre_campo_id]] = stripslashes(str_replace(";:",",",$row[$camp]));   
             }
             $retornar = $regs;
        }
        return $retornar;
    }
    function n_regs($taula,$consulta){
  
        $query="select * from $taula where {$consulta}";
        $res = mysql_query($query);
        
        $num_filas = mysql_num_rows($res);
        
        return $num_filas;
    }
    function aux_camps_taula_arr($taula){
         $result = mysql_query("SHOW COLUMNS FROM $taula"); 
         if (!$result) { 
            return 0;
         } 
         
         if (mysql_num_rows($result) > 0) { 
             while ($row = mysql_fetch_assoc($result)) { 
                 $retornar[] = $row['Field'];
            }
            return $retornar;
         } 
         return 0;
    }
    function aux_existe_campo_en_tabla($tabla,$campo){
        $campos = $this->aux_camps_taula_arr($tabla);
        if(in_array($campo,$campos))
            return true;
        return false;
    }
    
    function get_tables(){
        $query = "SHOW TABLES FROM $this->db";
    
        $result = mysql_query($query, $this->con);		
    	
        while ($table = mysql_fetch_row($result))
        {
            $tables[] = $table[0];
        }
    	
        return $tables;
        
    }
    // configuracion especifica para Wordpress
    function av_create_table_db($nom_taula,$camps,$tipus,$camp_autoincrement="",$sobreescribir=0){
    	if(!is_array($camps)) $camps=explode(",",$camps);
    	if(!is_array($tipus)) $tipus=explode(",",$tipus);
    
        $query="CREATE TABLE IF NOT EXISTS ".$nom_taula." (";
        if($sobreescribir)
     	  $query="CREATE TABLE ".$nom_taula." (";
    
        
 	  
    	for($c=0 ; $c<sizeof($camps) ; $c++){
    		$query.=$camps[$c]." ".$tipus[$c]." NOT NULL ";
            if($camps[$c] == $camp_autoincrement)
                $query .= "AUTO_INCREMENT";
            $query.= ",";
    	}
        $query = substr($query,0,-1);
        if($camp_autoincrement!="")
         $query.=", UNIQUE KEY id (".$camp_autoincrement.")";
      	  
    
    	$query.=");" ;
   
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($query);
    
    
    


    
    }
    
 }
 ?>