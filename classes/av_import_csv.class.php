<?php
	
    /**
     * import_csv.class.php
     * 
     * Gestiona todo el proceso de importar csv -> tabla
     * 
     * paso 1 -> formulario subida archivo csv.
     * paso 2 -> formulario nombres y tipos
     * paso 3 -> crear la tabla en la bbdd.
     * 
     */
     
     
class av_import_csv{
    
    
    var $csv_array = array();
    var $nombres_campos_en_primera_fila = true;
    var $delimitador = ";" ;
    var $n_registros = 0;
    var $n_campos = 0;
    var $nombres_campos = "";
    
    // array que guarda la longitud máxima del valor de cada campo
    var $campos_len = array();
    
    var $tipos_campo = array( 
                        "input_text" => "Texto (hasta 200 char)",
                        "textarea" => "Texto largo (+ de 200 char)",
                        "date" => "Fecha (formato AAAA-mm-dd)",
                        "fecha" => "Fecha (formato dd-mm-AAAA)"
                       );
    
    /**
     * Paso 1 - function avit_form_subir
     * 
     * @return form subir archivo csv
     * 
     */
    function avit_form_subir_csv($action = "import_csv&paso=2"){
       
      $form = ' 
      <div class="ventana" style="width:600px;" >
        <h3>Selecciona un archivo csv de tu ordenador y haz click en subir archivo.</h3>
        <div class="avit_div_form" >
             <form method="post" enctype="multipart/form-data" action="'.AVIT_PAGE.'&action='.$action.'">
                <p>Campo delimitado por:
                    <select name="delimitador" >
                        <option value=";"> ; Punto y coma</option>
                        <option value=","> , Coma</option> 
                    </select>
                </p>
                
                <p>Subir archivo (csv)<input type="file" name="archivo_csv" /></p>
                <p><input type="checkbox" name="nombres_primera_fila" value="1" checked="true" /> La primera fila contiene los nombre de los campos.</p>
                <input type="hidden" name="submit_csv" value="subir" />  
                <p class="submit"><input type="submit"  value="Subir archivo" class="button-primary" /></p>
             </form>
        </div>
      </div>
          ';
          
	   return $form;

        
    }
    
    /**
     * function csv2array
     * Convierte un archivo CSV en un array.
     * 
     * Rellena la propiedad csv_array con el resultado $regs
     * Rellena la propiedad campos_tipo_sug[num_camp][]  // donde sugiere el tipo de campo para el select.
     * @return array bidimensional $regs[num_linia][num_camp] = valor camp
     * 
     */
    function csv2array ($archivo_csv){
        
        if(isset($_POST['delimitador']) && $_POST['delimitador'] != "") 
            $this->delimitador = $_POST['delimitador'];
        
        $fila = 0;
        $regs = array();
        $nombres_campos = array();
        
        if (($gestor = fopen($archivo_csv, "r")) !== FALSE) {
            while (($datos = fgetcsv($gestor, 0, $this->delimitador  )) !== FALSE) {
                $numero = count($datos);
               
                    for ($c=0; $c < $numero; $c++) {
                        
                        $dato = $datos[$c];
                        $valor_len = strlen($dato);
                        
                        
                        // si la primera fila contiene los nombre de los campos - los recogemos aquí
                        if($this->nombres_campos_en_primera_fila && $fila == 0){
                            $nombres_campos[] = substr(av_cadena_limpiar($dato),0,15);
                            $fila = 0;
                        }else{
                            $regs[$fila][$c] =$dato; 
                          
                             //  controlamos el tamaño de los datos (para ofrecer un input o un textarea)
                            if(!key_exists($c,$this->campos_len)){
                                $this->campos_len[$c] = $valor_len;
                            }else{
                                $valor_len_acumulado = $this->campos_len[$c];
                                if($valor_len_acumulado < $valor_len)
                                    $this->campos_len[$c] = $valor_len;
                            }
                        }
                    }
                    
                    $fila++;
                    
            }
            
            fclose($gestor);
        }else{
            $regs = 0;
        }
        
        
        if(!$this->nombres_campos_en_primera_fila){
            // creamos los nombres de los campos
            for($c = 0 ; $c < sizeof($regs[0]) ; $c++){
                $valor_campo = $regs[0][$c];
                $nombres_campos[] = substr($valor_campo,0,15);
            }
            
            // informamos del numero de registros
            $this->n_registros  = $fila;
        }else{
            $this->n_registros  = $fila-1;
        }
        $this->nombres_campos = $nombres_campos;
        $this->n_campos     = $numero;
        $this->csv_array    = $regs;
        
        return $regs;
        
    }
    
    /**
     *  Funcion que genera un Grid de salida de datos a partir de un array csv_array
     * 
     * @param $regs - opcional array con los registros a mostrar - por defedto coge la variable $this->csv_array. generada con $this->csv2array
     * @param $orden - muestra el icono para cambiar el orden de los items
     * @param $incluir - muestra un checkbox que permite incluir/exluir items
     * @param $inputs - muestra los valoes en inputs (name=valor_campo[num_registro][num_campo])
     * @return html - tabla grid de datos
     */
    function avit_csv_grid($regs=" ",$orden=true,$incluir=true,$inputs=true){
       
 
        if($regs == " ")
        $regs = $this->csv_array;
       
        $retornar = "";
        $retornar .="<div class='colapsable ventana' style='max-height:300px;overflow:auto;'>";
            $retornar .="<h3 style='padding:10px;'>Registros (".$this->n_registros.") </h3>";
            $retornar .="<div class='inside'>";
                $retornar .="<table class='widefat'>";
                $retornar .="<thead>";
                
                
                // cabeceras del grid
                if($orden)
                $retornar .= "<th style='text-align:center !important;'>Orden</th>";
                
                if($incluir)
                $retornar .= "<th style='text-align:center !important;'>Incluir</th>";
               
                // cabeceras - nombre de los campos
                
                for($c = 0 ; $c < sizeof($this->nombres_campos) ; $c++){
                    $retornar .= "<th style='font-size:10px;'>";
                    $retornar .= $this->nombres_campos[$c];
                    $retornar .= "</th>";
                }
                $retornar .= "</thead>";
                $retornar .= "<tbody class='sortable'>";
                
                // variable que controla el style - color de los items impares
                $odd = true;
                foreach($regs as $reg_orden => $valores){
                    if($odd){
                        $style_odd = "style='background:#ffffff'";
                        $odd = false;
                    }else{
                        $style_odd = "style='background:#f9f9f9'";
                        $odd =true;
                    }
                      
                    $retornar .="<tr class=' item menu-item-".$reg_orden."' ".$style_odd.">";
                    
                    if($orden)
                    $retornar .= "<td class='mover' style='text-align:center !important;'><img src='".AVIT_PLUGIN_URL."/imagenes/mover.png' width='18' height='18' /></td>";
                    
                    if($incluir)
                    $retornar .="<td style='text-align:center !important;'><input type='checkbox' name='incluir_reg[".$reg_orden."]' value='1' checked='true' /> </td>";
                    
                    for($i = 0 ; $i < sizeof($valores) ; $i++){
                        $valor = $valores[$i];
                        
                        $retornar .="<td  style='min-width:60px !important;max-width:230px !important'>";
                        
                        // Se comprueba si los valores deben ir en un input
                        if($inputs){
                            $tipo = $this->get_tipo_de_campo($i);
                            
                            if($tipo=="textarea"){
                               $retornar .="<textarea name='valor_campo[".$reg_orden."][".$i."]'>".$valor."</textarea>"; 
                            }else{
                                if($tipo == "fecha")
                                    $valor = av_data_to_date($valor);
                                $retornar .="<input type='text' name='valor_campo[".$reg_orden."][".$i."]' value='".$valor."' />";
                            }
                        }else{
                            $retornar .=$valor;
                        }
                         $retornar .= "</td>";
                    }
                    $retornar .="</tr>";
                }
                $retornar .= "</tbody>";
                $retornar .="</table>";
            $retornar .="</div>";
        $retornar .="</div>";
        
        return $retornar;
    }
    
    /**
     * paso 2 - mostrar datos y nombres de campos a incluir
     */
    function avit_form_csv(){
        $retornar = "";
        // comprobar que el formulario se ha submitado y que tiene un archivo seleccionado
        if(isset($_POST['submit_csv']) && $_POST['submit_csv']=="subir" && isset($_FILES['archivo_csv']['name']) &&  $_FILES['archivo_csv']['name']!=""  ){
            // reset el valor del input de control de formulario.
            $_POST['submit_csv'] = "";
            unset($_POST['submit_csv']);
            $file_name = $_FILES['archivo_csv']['name'];
            $extension = av_arxiu_extensio($file_name);
            
            $nombre_temporal = $file_name.date("Y-m-j-H:i:s");
            $carpeta_destino =   AVIT_PLUGIN_DIR. "/archivos_subidos_csv";

                $ruta_archivo = $_FILES['archivo_csv']['tmp_name'];
               
                // si la primera fila contiene los nombres de los campos, aquí la eliminamos del array.
                if(!isset($_POST['nombres_primera_fila'])){
                    $this->nombres_campos_en_primera_fila = false;
                    
                }
                
                // Convertimos el archivo en array de datos
                $regs = $this->csv2array($ruta_archivo);
                
                
                 /* FORMULARIO -INICIO- */
                $retornar .= "<form method='post' action='".AVIT_PAGE."&action=import_csv&paso=3'>";
                
                
                   // $retornar .="<p class='mensaje ok'>Estado: ".$subida['estado']." - ".$subida['mensaje']."</p>";
                    $retornar .= $this->avit_csv_grid($regs,false,false,true);
                
                
                $retornar .="<div class='ventana' style='width:600px;'>";
                    
                    $retornar .="<h3>Asignar nombre a los campos";
                    if(!$this->nombres_campos_en_primera_fila)
                        $retornar .= "( se muestran los valores de la primera fila)";
                        
                    $retornar .=".</h3>";
                    
                    $retornar .="<div class='avit_div_form' style='width:520px;margin-bottom:5px;' >";
                        $retornar .="<p>Nombre de la Tabla: <input type='text' name='nombre_tabla' size='30' value='".$file_name."' /></p>";
                        
                    $retornar .="</div>";
                    
                    
                    $retornar .="<div class='avit_div_form' style='width:520px;max-height:300px;overflow:auto;' >";
                    
                    $retornar .="<table  class='widefat' >";
                    $retornar .="<thead><th style='text-align:center !important;'>Incluir</th><th>Nombre del campo</th></thead><tbody>";
             
                    
                    for($i = 0 ; $i < sizeof($this->nombres_campos) ; $i++){
                            $valor = $this->nombres_campos[$i];
                            $retornar .="<tr>";
                            $retornar .="<td width='40' align='center'><input type='checkbox' name='incluir_campo[".$i."]' value='1' checked='true' /> </td>";
                            $retornar .="<td width='250'><input type='text' maxlength='50' name='nombre_campo[]' size='30' value='".$valor."' /></td>";
                            
                            $retornar .="<td width='10'><input type='hidden' name='tipo_campo[]' value='".$this->get_tipo_de_campo($i)."' /></td>";
                            $retornar .="</tr>";
                     }
                    $retornar .="</tbody></table>";
                    $retornar .="</div>";
                $retornar .="</div>";
                 $retornar .="<input type='hidden'  name='crear_tabla' value='1' /></p>";
                 $retornar .="<p class='submit'><input type='submit'  value='Crear Tabla' class='button-primary' /></p>";
                $retornar .= "</form>";
                
            }else{
                $retornar .="<p class='mensaje error'>No se ha subido correctamente el archivo. Revisa la informaci&oacute;n y vuelve a probarlo.</p>";
            }         
       
        
        // se borra el archivo csv
        if(isset($ruta_archivo))
            av_arxiu_eliminar($ruta_archivo);
        
        return $retornar;
    }
    /**
     * entra el numero de orden del campo para sugegir tipo
     * 
     * @return el tipo de campo
     */
    function get_tipo_de_campo($num_orden_campo){
       
        // si hay más de un registro (cogemos el segundo (indice 1) para descartar que en la primera esten los nombre de los campos)
        if($this->n_registros > 1)   
            $valor = $this->csv_array[1][$num_orden_campo];
        else
           $valor = $this->csv_array[0][$num_orden_campo]; 
       
        // sugerir tipo como seleccionado.
        $tipo_sugerido = "input_text";
        if(av_es_data($valor))
            $tipo_sugerido = "date";
        else if(av_es_date($valor))
            $tipo_sugerido = "date";
        else if(is_numeric($valor))
            $tipo_sugerido = "input_text";
        else if($this->campos_len[$num_orden_campo] >= 200)
            $tipo_sugerido = "textarea";
        
       return $tipo_sugerido;
        
    }
    /**
     * Paso 3 - guardar datos en bbdd.
     */
    function avit_guardar_en_tabla(){
        global $wpdb;
        
        $incluir_campos = $_POST['incluir_campo'];
        $campos = $_POST['nombre_campo'];
        $tipos = $_POST['tipo_campo'];
        
        $nombre_de_tabla = $_POST['nombre_tabla'];
        if($nombre_de_tabla == ""){
            $nombre_de_tabla = "avit_tabla_".rand(1,100);
        }
        
        $regs = $_POST['valor_campo'];
        
        
        $n_regs = count($regs);
        $n_campos =count($incluir_campos);
        
        $retornar = "";
        $retornar .= "<h2>Tabla ".$nombre_de_tabla." creada con la siguiente estructura:</h2>";
        
        $camps_table = array();
        $camps_table['avit'] = $nombre_de_tabla;
        $camps_table['n_regs'] = $n_regs;
        $camps_table['n_campos'] = $n_campos;
        
        $camps_table['campos'] =  "";
        $camps_table['tipos'] = "";
        
        if($n_regs > 0 && $n_campos > 0){
            
            for($i = 0 ; $i < sizeof($campos) ; $i++){
                $incluir = $incluir_campos[$i];
                $campo = $campos[$i];
                $tipo = $tipos[$i];
                
                if($incluir){
                    $camps_table['campos'] .=  $campo.",";
                    $camps_table['tipos'] .= $tipo.",";
                    $retornar .= "<p>".$campo."</p>";
                }
                    
            }
            $camps_table['campos'] =  substr($camps_table['campos'],0,-1);
            $camps_table['tipos'] = substr($camps_table['tipos'],0,-1);
            
            // crear la avit_tabla
            
             $wpdb->insert( "avit", $camps_table );
                         
                         $ultimo_id = $wpdb->insert_id;
             
            
             
            // insertar registros
            foreach($regs as $n_linia => $campo_valor){
                              
                $array_insert = array();         
                for($i = 0 ; $i < sizeof($campo_valor) ; $i++){
                    $valor = $campo_valor[$i];
                    $incluir = $incluir_campos[$i];
                    if($incluir){
                       $array_insert['reg_num'] = $n_linia;
                       $array_insert['campo'] = $campos[$i];
                       $array_insert['valor'] = $valor;
                       $array_insert['avit_id'] = $ultimo_id; 
                     
                      $wpdb->insert( "avit_datos", $array_insert );  
                    }
                }
             }
        
         }else{
             $retornar .= "<h3 class='mensaje error'>No se ha podido crear la tabla debido a que no se han seleccionado campos, o est&aacute; vac&iacute;a.</h3>";
         }
        
        
        return $retornar;
    }
}
?>