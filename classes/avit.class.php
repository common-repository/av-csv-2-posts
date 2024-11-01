<?php
	
    /**
     * Classe avit
     * 
     * Classe que gestiona todo el proceso de crear nuevas entradas a partir de los datos almacenados en las tablas (avit y avit_datos)
     * 
     * (nota - la tablas avit y avit_datos se rellenan en el proceso de subir los archivos CSV)
     * 
     * Editar nombre de las tablas guardadas
     * Crear categorías y etiquetas de forma masiva (una cat / tag por cada registro del campo seleccionado)
     * Gestiona todo el proceso de generar posts (en 3 pasos):
     *      1 - Seleccionar campos:
     *              - En titulo
     *              - En contenido (texto - imagen)
     *              - Campos personalizados
     *              - Imagen destacada.
     *              - Assignar categorias (permite relacionar cada valor distinto del campo seleccionado y las categorias existentes) 
     *      2 - Configurar:
     *              (ENTRADAS)
     *              - Texto delante del titulo
     *              - Orden de aparición de los campos del título
     *              - Orden de aparición de los campos del contenido
     *              - Opción de incluir <!--More--> en cualquier parte del contenido
     *              
     *              - Seleccionar el tipo de contenido donde incluir los posts (post_type)
     *              - Seleccionar el estado para los comentarios (comment_status)
     *              - Seleccionar el estado de las entradas (publicado  - Borrador )
     *              
     *              (IMAGENES)
     *              - Seleccionar incluir enlace en las imagenes
     *              - Seleccionar tamaños para las imagenes (tamaños de visualización en contenido)
     *              - Informar de la carpeta donde buscar las imagenes (a partir de wp-content/uploads/)
     * 
     *      3 - Crear posts y ver previsualización de un post.
     * 
     * Una vez creados los post - el proceso es irreversible desde este plugin. (se deben utilizar las opciones de Wordpress para modificar o eliminar entradas)
     * 
     * 
     */
     
     
class avit{
    
    
    
    /**
     * Formulario que permite modificar el nombre de la tabla
     *  
     * @return $_POST['nombre_tabla']
     */
    function form_editar_tabla($avit_id,$action="tablas&proceso=editar&paso=2"){
        
        global $avit_db;
        $reg = $avit_db->regs("avit","id=$avit_id");
        $nombre_tabla = $reg[0]['avit'];
        $id_tabla = $reg[0]['id'];
        $retornar = "";
        $retornar .= "<div class='ventana' style='width:500px;'>";
            $retornar .= "<h3>Modificar Nombre Tabla: ".$nombre_tabla."</h3>";
                $retornar .= "<form method='post' action='".AVIT_PAGE.'&action='.$action."' >";
                $retornar .= "<div class='avit_div_form'>";
                     $retornar .= "<input type='hidden' name='avit_id' value='".$avit_id."'/>";
                     $retornar .= "<p>Nombre: <input name='nombre_tabla' value='".$nombre_tabla."' /></p>";
                     
                $retornar .= "</div>";
                $retornar .= "<p class='submit'><input type='submit' class='button-primary' value='Modificar nombre tabla' /></p>";
                $retornar .= "</form>";
        $retornar .= "</div>";
        
        
       
            $retornar .= "<form method='post' action='".AVIT_PAGE.'&action='.$action."' >";
               $retornar .= $this->get_tabla_grid($avit_id,"","","",true);
            $retornar .= "</form>";
       

        
        
        return $retornar;
        
    }
    
    /**
     * Actualiza el nombre de la tabla (en la bbdd -> avit)
     * Recoge los datos del POST del form_editar_tabla
     */
    function editar_tabla(){
        global $wpdb;
        print_r($_POST);
        $nombre_tabla = $_POST['nombre_tabla'];
        $avit_id = $_POST['avit_id'];
        $wpdb->update( 
        	'avit', 
        	array( 
        		'avit' => $nombre_tabla,	// string 
        	), 
        	array( 'id' => $avit_id ), 
        	array( 
        		'%s'	// value1
        	) 
        );
        
        
    }
    
  /**
   * Formulario para crear nuevas categorias / etiquetas de forma masiva
   * Muestra la lista de campos de la tabla activa un formulario para seleccionar un campo (radio)
   * Se creará una categoria por cada registro de la tabla
   * 
   * @return  $_POST['avit_id'] - el id de la tabla   
   * @return  $_POST['crear_cat'] - el nombre del campo seleccionado
   * @return  $_POST['cat'] - combobox (solo para categorias)que muestra las categorias existentes (permite seleccionar el parent)
   */
    function form_crear_categorias($avit_id,$mostrar_parent=true,$action="tablas&proceso=crear_categorias&paso=2"){
        global $wpdb;
        
        $campos_i_tipos = $this->get_campos_y_tipos($avit_id);
        $campos = $campos_i_tipos['campos'];
        $retornar = "";
        
        $retornar .= "<div class='ventana' style='width:290px;'>";
        $retornar .= "<form method='post' action='".AVIT_PAGE.'&action='.$action."' >";
        $retornar .= "<input type='hidden' name='avit_id' value='".$avit_id."'/>";
        $retornar .= "<table class='widefat'>";
        $retornar .= "<thead>";
        $retornar .= "<th>Campos</th><th style='text-align:center;'>Seleccionar</th>";
        $retornar .= "</thead>";
        $retornar .= "<tbody>";
        for($i = 0 ; $i < sizeof($campos) ; $i++){
            $campo = $campos[$i];
            $check = "";
            if($i==0) $check = "checked";
            $retornar .= "<tr>";
            $retornar .= "<td>".$campo."</td>";
            $retornar .= "<td align='center'><input type='radio' name='crear_cat' value='".$campo."' ".$check."/></td>";
            $retornar .= "</tr>";
        }
        
        $retornar .= "</tbody>";
        $retornar .= "</table>";
        
        if($mostrar_parent)
            $retornar .= "<p><strong>Categor&iacute;a padre:</strong> ". wp_dropdown_categories('hierarchical=1&hide_empty=0&echo=0&show_option_none=No pertenece a ninguna')."</p>";
        
        $retornar .= "<p class='submit'><input type='submit' class='button-primary' value='Crear' /></td>";
        $retornar .= "</form>";
        
        
        $retornar .= "</div>";
        return $retornar;
    }
    
    /**
     * Crea categorias de forma masiva
     * Recoge los datos del POST que vienen del form_crear_categorias
     * 
     * @return html - lista de categorías creadas
     */
    function crear_categorias(){
        global $avit_db;
        $retornar = "";
        $campo_categoria = $_POST['crear_cat'];
        $avit_id = $_POST['avit_id'];
        $parent = $_POST['cat'];
        if($parent != "-1"){
              $parent_nombre = get_cat_name( $parent );
            }else{
              $parent_nombre = "Sin categor&iacute;a padre";
            }
        $retornar .= "<h3>Categor&iacute;as creadas. <p><strong>Categor&iacute;a padre: ".$parent_nombre."</strong></p></h3>";
        
        $registros = $avit_db->regs_camp("avit_datos","valor","avit_id=$avit_id AND campo='".$campo_categoria."'");
        for($i = 0 ; $i < sizeof($registros) ; $i++){
            $retornar .= "<p>".$registros[$i]."</p>";
            if($parent != "-1"){
              wp_create_category( $registros[$i],$parent );
            }else{
              wp_create_category( $registros[$i] );
            }
        }
        return $retornar;
    }  
    
    /**
     * Crea etiqutas de forma masiva
     * Recoge los datos del POST que vienen del form_crear_categorias
     * 
     * @return html - lista de etiquetas creadas
     */
    function crear_etiquetas(){
        global $avit_db;
        $retornar = "";
        $campo_etiqueta = $_POST['crear_cat'];
        $avit_id = $_POST['avit_id'];
        $retornar .= "<h3>Etiquetas creadas.</h3>";
        
        $registros = $avit_db->regs_camp("avit_datos","valor","avit_id=$avit_id AND campo='".$campo_etiqueta."'");
        for($i = 0 ; $i < sizeof($registros) ; $i++){
           
           if(!term_exists( $registros[$i], 'propost_tagduct' )) {
                $retornar .= "<p>".$registros[$i]."</p>";
                wp_insert_term(
                      $registros[$i], // the term 
                      'post_tag'
                 );
           }    
        }
        
        return $retornar;
    } 
    /**
     * Formulario de confirmación para eliminar los datos de una tabla (en avit y avit_datos)
     * Proceso irreversible - Para volver a disponer de los datos se debe volver a subir el archivo CSV
     */
    function form_eliminar_tabla($avit_id,$action="tablas&proceso=eliminar_tabla&paso=2"){
        global $avit_db;
        $tabla_av = $avit_db->regs("avit","id=$avit_id");
        
       $retornar .= "<h3>Eliminar Tabla</h3>";
        $retornar .= "<div class='ventana' style='width:320px;'>";
        
        $retornar .= "<form method='post' action='".AVIT_PAGE.'&action='.$action."' >";
        $retornar .= "<input type='hidden' name='avit_id' value='".$avit_id."'/>";
        $retornar .= "<table class='widefat'>";
        $retornar .= "<thead>";
        $retornar .= "<th>Tabla</th><th style='text-align:center;'>Registros</th>";
        $retornar .= "</thead><tbody><tr>";
            $retornar .= "<td>".$tabla_av[0]['avit']."</td>";
            $retornar .= "<td align='center'>".$tabla_av[0]['n_regs']."</td>";
            $retornar .= "</tr></tbody></table>";
        $retornar .= "<p class='submit'><input type='submit' class='button-primary' value='Eliminar tabla y datos' /></p>";
        $retornar .= "</form>";
        
        
        $retornar .= "</div>";
        return $retornar;
    }
    
    /**
     * Recoge los datos del form_eliminar_tabla y procesa la eliminación
     */  
    function eliminar_tabla(){
        global $avit_db;
        $avit_id = $_POST['avit_id'];
        $avit_db->delete("avit","id=$avit_id");
        $avit_db->delete("avit_datos","avit_id=$avit_id");
    }  
    
    /**
     *  CREAR ENTRADAS - PASO 1 -  seleccionar campos
     * 
     *  Formulario de seleccion de campos a incluir en cada parte de la entrada.
     *  Seleccionar el patrón html para la salida de los campos
     */
    function form_crear_entradas($avit_id,$action="tablas&proceso=crear_entradas&paso=2"){
        
        $campos_i_tipos = $this->get_campos_y_tipos($avit_id);
        $campos = $campos_i_tipos['campos'];
        
        $retornar = "";
        $retornar .= "<div class='ventana' style='width:860px;'>";
        $retornar .= "<h3 style='color:#3871A2'>Asignar campos a incluir en cada parte de la entrada.</h3>";
        $retornar .= "<form method='post' action='".AVIT_PAGE.'&action='.$action."' >";
        $retornar .= "<input type='hidden' name='avit_id' value='".$avit_id."'/>";
        $retornar .= "<table class='widefat'><thead>";
        $retornar .= "<th>Campos</th>";
        $retornar .= "<th style='text-align:center;'>En el T&iacute;tulo</th>";
        $retornar .= "<th style='text-align:center;'>En el Contenido</th>";
        $retornar .= "<th style='text-align:center;'>Campo personalizado</th>";
        $retornar .= "<th style='text-align:center;'>Imagen Destacada</th>";
        $retornar .= "<th style='text-align:center;'>Asignar categor&iacute;as</th>";
        $retornar .= "</thead><tbody>";
        for($i = 0 ; $i < sizeof($campos) ; $i++){
            $campo = $campos[$i];
            
            $retornar .= "<tr>";
            $retornar .= "<td>".$campo."<input type='hidden' name='nombre_campo[]' value='".$campo."' /></td>";
            $retornar .= "<td align='center'><input type='checkbox' name='en_titulo[]' value='".$campo."'/></td>";
            $retornar .= "<td align='center' style='font-size:9px;color:#999;'><input type='checkbox' name='en_contenido_texto[]' value='".$campo."'/> Texto &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='en_contenido_imagen[]' value='".$campo."'/> Imagen </td>";
            $retornar .= "<td align='center' style='font-size:9px;color:#999;'><input type='checkbox' name='campo_personalizado_visible[]' value='".$campo."'/> Visible &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='campo_personalizado_oculto[]' value='_".$campo."'/> Oculto</td>";
            $retornar .= "<td align='center'><input type='radio' name='imagen_destacada' value='".$campo."' /></td>";
            $retornar .= "<td align='center'><input type='checkbox' name='asignar_categorias[]' value='".$campo."'/></td>";
            $retornar .= "</tr>";
        }
        $retornar .= "</tbody></table>";
        $retornar .="</div>";
        
    // configuración
    
      // cogemos los tipos de contenido para crear un select
      $post_types=get_post_types('','names');
      $select_tipo_contenido = "<select name='type_contenido' >";
        foreach ($post_types as $post_type ) {
          $select_tipo_contenido .= "<option value='".$post_type."'>". $post_type. "</option>";
        }
      $select_tipo_contenido .= "</select>";
      
      
            
    $retornar .= "<div style='clear:both;width:860px;margin-top:30px;' class='ventana'>";
        $retornar .= "<h3 style='color:#3871A2'>Configuraci&oacute;n</h3>";
        $retornar .= "<table class='widefat'><thead>";
        $retornar .= "<th>Conceptos</th>";
        $retornar .= "<th>Valores</th>";
        $retornar .= "</thead><tbody>";
        // entradas - posts
        $retornar .= "<tr style='background:#fff;'>";
        $retornar .= "<td colspan='2' style='border-bottom:1px solid #666;'>
                        <p style='color:#3871A2;margin:8px 0px;font-weight:bold;font-size:12px;'>Entradas</p>
                    </td>";
        $retornar .= "</tr>";
        $retornar .= "<tr style='background:#f5f5f5;'>";
            $retornar .= "<td style='padding-left:20px;'>
                            <strong>Asignar los posts al tipo de contenido:</strong>
                        </td>";
            $retornar .= "<td>".$select_tipo_contenido."</td>";
            $retornar .= "<td></td>";
        $retornar .= "</tr>";
        $retornar .= "<tr style='background:#f5f5f5;'>";
            $retornar .= "<td style='padding-left:20px;'>
                            <strong>Asignar los posts al autor:</strong>
                        </td>";
            $retornar .= "<td>".wp_dropdown_users(array('echo' => false, 'name' => 'autor','selected' => get_current_user_id()))."</td>";
            $retornar .= "<td></td>";
        $retornar .= "</tr>";
        $retornar .= "<tr  style='background:#f5f5f5;'>";
            $retornar .= "<td style='padding-left:20px;'>
                            <strong>Permitir comentarios:</strong>
                        </td>";
            $retornar .= "<td>
                            <input type='radio' name='permitir_comentarios' value='open' /> S&iacute; &nbsp;&nbsp;&nbsp;
                            <input type='radio' name='permitir_comentarios' value='closed' checked='true' /> No 
                         </td>";
            $retornar .= "<td></td>";
        $retornar .= "</tr>";
        $retornar .= "<tr  style='background:#f5f5f5;'>";
        $retornar .= "<td style='padding-left:20px;'>
                        <strong>Estatus de las entradas:</strong>
                    </td>";
        $retornar .= "<td>
                        <input type='radio' name='estatus_entrada' value='publish' checked='true' /> Publicado &nbsp;&nbsp;&nbsp;
                        <input type='radio' name='estatus_entrada' value='draft'  /> Borrador
                     </td>";
        $retornar .= "<td></td>";
        $retornar .= "</tr>";
            
        // patron html delante y detrás
        $retornar .= "<tr style='background:#fff;'>";
        $retornar .= "<td colspan='2' style='border-bottom:1px solid #666;'>
                        <p style='color:#3871A2;margin:8px 0px;font-weight:bold;font-size:12px;'>Definir patr&oacute;n HTML para cada campo.</p></td>";
        $retornar .= "</tr>";
        $retornar .= "<tr  style='background:#f5f5f5;'>";
        $retornar .= "<td style='font-weight:bold;padding-left:20px;'>
                        <p>Para cada campo del contenido:</p>
                        <p><small>%campo% para el nombre del campo.</small></p>
                        <p><small>En el siguiente paso se puede individualizar.</small></p>
                     </td>";
        $retornar .= "<td >
                        Delante: <textarea name='en_contenido_html_pre' cols='60' rows='2'><h3>%campo%</h3><p></textarea><br />
                        Detr&aacute;s:&nbsp;&nbsp; <textarea name='en_contenido_html_post'  cols='60' rows='1'></p></textarea>
                     </td>";

        $retornar .= "</tr>";
        $retornar .= "</tbody></table>";        
        $retornar .= "<p class='submit'><input type='submit' class='button-primary' value='Crear Entradas' /></td>";
        $retornar .= "</form>";
        $retornar .= "</div>";

        return $retornar;
    } 
    
    /**
     *  CREAR ENTRADAS - PASO 2 -  Configurar
     *  
     *  Recoge los datos que vienen del form_crear_entradas
     *  Muestra un Formulario de:
     *      - configuración para (campos - en titulo y contenido- ) Orden y salida html.
     *      - configuración del tipo de contenido - estado de las entrada y comentarios
     *      - configuración de la salida para las imagenes
     *      - asignación de categorias. 
     */
    function crear_entradas_configurar($action="tablas&proceso=crear_entradas&paso=3"){
        global $avit_db;
        $campos_titulo = $_POST['en_titulo'];
        $campos_contenido_texto = $_POST['en_contenido_texto'];
        $campos_contenido_imagenes = $_POST['en_contenido_imagen'];
        $en_contenido_html_pre = $_POST['en_contenido_html_pre'];
        $en_contenido_html_post = $_POST['en_contenido_html_post'];
        $per_visibles = (is_array($_POST['campo_personalizado_visible'])) ? $_POST['campo_personalizado_visible'] : array() ;
        $per_ocultos = (is_array($_POST['campo_personalizado_oculto']))   ? $_POST['campo_personalizado_oculto']  : array();
        $campos_personalizados = array_merge($per_visibles,$per_ocultos);
        $campo_imagen_destacada = $_POST['imagen_destacada'];
        $asignar_categorias = $_POST['asignar_categorias'];
        $avit_id = $_POST['avit_id'];
        
        $nombre_tabla = $avit_db->dato("avit","avit","id=$avit_id");
        
        $retornar = "";
        
        
        
        
        $retornar .= "<div class='avit_div_form'>";
        $retornar .= "<form method='post' action='".AVIT_PAGE.'&action='.$action."' >";
        $retornar .= "<input type='hidden' name='avit_id' value='".$avit_id."'/>";
       
        // campos en el titulo
        $retornar .= "<div style='width:660px;float:left;' class='ventana'>";
        $retornar .= "<h3 style='color:#3871A2'>Campos en el T&iacute;tulo</h3>";
        $retornar .= "<table class='widefat'><thead>";
        $retornar .= "<th width='50'>Orden</th>";
        $retornar .= "<th width='50'>Tipo</th>";
        $retornar .= "<th width='90'>Campos</th>";
        $retornar .= "<th width='200'>Texto Delante (sin html)</th>";
        $retornar .= "</thead><tbody class='sortable'>";
        for($i = 0 ; $i < sizeof($campos_titulo) ; $i++){
            $campo = $campos_titulo[$i];
            $retornar .= "<tr>";
            $retornar .= "<td class='mover' style='text-align:center !important;'><img src='".AVIT_PLUGIN_URL."/imagenes/mover.png' width='18' height='18' /></td>";
            $retornar .= "<td >
                            <img src='".AVIT_PLUGIN_URL."/imagenes/contenido_texto.jpg' width='18' height='18' />
                         </td>";
            $retornar .= "<td>".$campo."<input type='hidden' name='en_titulo[]' size='40' value='".$campo."' /></td>";
            $retornar .= "<td><input type='text' name='en_titulo_texto_pre[".$campo."]' size='20' maxlength='40' value='' /> </td>";
            
            $retornar .= "</tr>";
        }
        $retornar .= "</tbody></table>";
    $retornar .= "</div>";
      
      
         
         // datos configuracion entradas
        $retornar .= "<div style='width:230px;float:left;' class='ventana'>".$this->get_config_global()."</div>";
        
        
          
        // campos en el contenido - texto e imagenes
   $retornar .= "<div style='width:660px;float:left;' class='ventana'>";
        $retornar .= "<h3 style='color:#3871A2'>Campos en el Contenido</h3>";
        $retornar .= "<table class='widefat'><thead>";
        $retornar .= "<th width='50'>Orden</th>";
        $retornar .= "<th width='50'>Tipo</th>";
        $retornar .= "<th width='90'>Campos</th>";
        $retornar .= "<th width='250'>Html (Delante y Detr&aacute;s)</th>";
        $retornar .= "</thead><tbody class='sortable'>";
        for($i = 0 ; $i < sizeof($campos_contenido_texto) ; $i++){
            $campo = $campos_contenido_texto[$i];
            $retornar .= "<tr>";
            $retornar .= "<td class='mover' align='center'>
                            <img src='".AVIT_PLUGIN_URL."/imagenes/mover.png' width='18' height='18' />
                         </td>";
            $retornar .= "<td >
                            <img src='".AVIT_PLUGIN_URL."/imagenes/contenido_texto.jpg' width='18' height='18' />
                         </td>";
             $retornar .= "<td>
                            ".$campo."
                            <input type='hidden' name='en_contenido[".$i."][texto]' size='40' value='".$campo."' />
                         </td>";
            $en_contenido_pre = str_replace("%campo%",ucfirst($campo),$en_contenido_html_pre);
            $en_contenido_post = str_replace("%campo%",ucfirst($campo),$en_contenido_html_post);
            $retornar .= "<td >
                           <textarea name='en_contenido[".$i."][html_pre]' cols='40' rows='2'>".$en_contenido_pre."</textarea><br />
                           <textarea name='en_contenido[".$i."][html_post]'  cols='40' rows='1'>".$en_contenido_post."</textarea>
                         </td>";
            $retornar .= "</tr>";
        }
        $orden = $i++;
        for($i = 0 ; $i < sizeof($campos_contenido_imagenes) ; $i++){
            $campo = $campos_contenido_imagenes[$i];
            $retornar .= "<tr>";
            $retornar .= "<td class='mover' style='text-align:center !important;'>
                            <img src='".AVIT_PLUGIN_URL."/imagenes/mover.png' width='18' height='18' />
                          </td>";
            $retornar .= "<td>
                            <img src='".AVIT_PLUGIN_URL."/imagenes/contenido_imagen.jpg' width='18' height='18' />
                         </td>";
            $retornar .= "<td>".$campo."<input type='hidden' name='en_contenido[".$orden."][imagen]' size='40' value='".$campo."' /></td>";
            $retornar .= "<td>
                                <textarea name='en_contenido[".$orden."][html_pre]' cols='40' rows='2'><p></textarea><br />
                                <textarea name='en_contenido[".$orden."][html_post]'  cols='40' rows='1'></p></textarea>
                         </td>";
            $retornar .= "</tr>";
            $orden++;
        }
        // seguir leyendo more...
            $retornar .= "<tr  style='background-color:#FDF5D2;'>";
            $retornar .= "<td class='mover' style='text-align:center !important;'>
                            <img src='".AVIT_PLUGIN_URL."/imagenes/mover.png' width='18' height='18' />
                         </td>";
            $retornar .= "<td  width='50' style='font-weight:bold;'>More...</td>";
            $retornar .= "<td>
                            Seguir leyendo... Separador 
                            <input type='hidden' name='en_contenido[".$orden."][more]' value='more' />
                         </td>";
            $retornar .= "<td colspan='2'>
                            (no se mostrar&aacute; si est&aacute; al final)
                            <input type='hidden' name='en_contenido[".$orden."][html_pre]' />
                            <input type='hidden' name='en_contenido[".$orden."][html_post]' />
                         </td>";
            $retornar .= "</tr>";
        $retornar .= "</tbody></table>";
$retornar .= "</div>";
       
       // imagen destacada
        if(isset($campo_imagen_destacada)){
        $retornar .= "<div style='width:230px;float:left;'  class='ventana'>";
            $retornar .= "<h3 style='color:#3871A2'>Imagen Destacada</h3>";
            $retornar .= "<table class='widefat'>";
            $retornar .= "<thead><th>Campo</th></thead><tbody class='sortable'>";
            $retornar .= "<tr>";
            $retornar .= "<td>".$campo_imagen_destacada."<input type='hidden' name='imagen_destacada' value='".$campo_imagen_destacada."' /></td>";
            $retornar .= "</tr>";
            $retornar .= "</tbody></table>";
        $retornar .= "</div>";
        }
       
       
        // campos personalizados
        if(isset($campos_personalizados) && count($campos_personalizados) != 0){
        $retornar .= "<div style='width:230px;float:left;' class='ventana'>";
            $retornar .= "<h3 style='color:#3871A2'>Campos Personalizados</h3>";
            $retornar .= "<table class='widefat'><thead>";
            $retornar .= "<th>Nombre</th>";
            $retornar .= "</thead><tbody class='sortable'>";
            $retornar .= "<tr>";
           
            $nombre_grupo = strtolower(substr(str_replace(" ","_",$nombre_tabla),0,10));
            $retornar .= "<td style='background:#fff;padding:15px;'>Sufijo: &nbsp;&nbsp;&nbsp;<input type='text' name='campos_personalizados_sufijo' size='10' maxlength='10' value='".$nombre_grupo."' /><br /> ";
            $retornar .= "</td>";
            $retornar .= "</tr>";

            for($i = 0 ; $i < sizeof($campos_personalizados) ; $i++){
                $campo = $campos_personalizados[$i];
                $retornar .= "<tr><td style='background:#f5f5f5;'>";
                $retornar .=$campo."<input type='hidden' name='campo_personalizado[]' size='20' value='".$campo."' />";
                $retornar .= "</td></tr>";
            }   
            $retornar .= "</tbody></table>";
        $retornar .= "</div>";
        }
        
        
        
        // recogemos los valores de configuración del paso 1 para pasarlos al paso 3
        $retornar .= "<input type='hidden' name='type_contenido' value='".$_POST['type_contenido']."' />";
        $retornar .= "<input type='hidden' name='permitir_comentarios' value='".$_POST['permitir_comentarios']."' />";
        $retornar .= "<input type='hidden' name='estatus_entrada' value='".$_POST['estatus_entrada']."' />";
        $retornar .= "<input type='hidden' name='autor' value='".$_POST['autor']."' />";
        
        // configurar imágenes  
        
        if($campos_contenido_imagenes != ""){
            
        
            $retornar .= "<div style='clear:both;width:925px;' class='ventana'>";
            $retornar .= "<h3 style='color:#3871A2'>Configuraci&oacute;n</h3>";
            $retornar .= "<table class='widefat'>";
            $retornar .= "<thead>
                            <th width='180'>Concepto</th>
                            <th  width='160'>Valor</th>
                            <th>Info</th>
                         </thead><tbody>";
            $retornar .= "<tr style='background:#fff;'>";
            $retornar .= "<td colspan='3'>
                            <p style='color:#3871A2;margin:8px 0px;font-weight:bold;font-size:12px;'>Im&aacute;genes</p>
                         </td>";
            $retornar .= "</tr>";
            $retornar .= "<tr>";
            $retornar .= "<td style='padding-left:20px;'>
                            <strong>A&ntilde;adir enlace a la imagen:</strong>
                        </td>";
            $retornar .= "<td>
                            <input type='checkbox' name='imagenes_enlace' value='true' checked='true' /> 
                        </td>";
            $retornar .= "<td>
                            <small>(Para activar efecto lightbox ver el plugin: 
                             <a href='http://wordpress.org/extend/plugins/simple-lightbox/' target='_blank'>Simple lightbox</a>)
                            </small>
                         </td>";
            $retornar .= "</tr>";
            $retornar .= "<tr>";
            $retornar .= "<td style='padding-left:20px;'>
                            <strong>Medidas de la imagen:</strong><br />
                             
                          </td>";
            $retornar .= "<td>
                            <input type='text' name='imagenes_ancho' size='6' /> Ancho max. <br />
                            <input type='text' name='imagenes_alto' size='6' />Alto max.
                         </td>";
            $retornar .= "<td>
                            <small>(Redimensiona la imagen original sin crear una copia)</small>
                         </td>";
            $retornar .= "</tr>";
            
            $retornar .= "<tr>";
            $retornar .= "<td style='padding-left:20px;'>
                            <strong>Carpeta para im&aacute;genes:</strong><br />
                            
                         </td>";
            $retornar .= "<td>
                            <input type='text' name='carpeta_imagenes' size='40' /> 
                            
                         </td>";
            $retornar .= "<td>
                            (ruta a partir de wp-content/uploads/) <br />
                            <small>(para las im&aacute;genes del contenido y la destacada)</small>
                         </td>";
            $retornar .= "</tr>";
            $retornar .= "</tbody></table>";
            $retornar .= "<br /><br />";
            $retornar .= "</div>";
        }
        
        
        // asignar categorias
        if(isset($asignar_categorias)){
        $retornar .= "<div style='clear:both;width:925px;' class='ventana'>";
            $retornar .= "<h3 style='color:#3871A2'>Asignar categor&iacute;as</h3>";
            $retornar .= "<table class='widefat'>";
            $retornar .= "<thead>
                            <th>Campo</th>
                            <th>Para cada valor - asignar categor&iacute;a</th>
                         </thead><tbody class='sortable'>";
            for($i = 0 ; $i < sizeof($asignar_categorias) ; $i++){
                $campo = $asignar_categorias[$i];
                $datos = $avit_db->regs_camp("avit_datos","valor","avit_id=$avit_id AND campo='".$campo."'",1);
                $n_datos = count($datos);
                
                $retornar .= "<tr>";
                $retornar .= "<td style='border-bottom:1px solid #aaa;background:#fff;padding:10px;font-weight:bold;font-size:1.2em'>
                            ".$campo."<input type='hidden' name='asignar_categoria[".$i."]' value='".$campo."' />
                            </td>";
                
                $retornar .= "<td><table>";
                for($d = 0 ; $d < sizeof($datos) ; $d++){
                    $dato = $datos[$d];
                     $retornar .= "<tr><td>Para el valor: <strong>".$dato."</strong><input type='hidden' name='para_el valor[".$i."][]' value='".$dato."' /></td>";
                    $retornar .= "<td>Asignar la categor&iacute;a: ".wp_dropdown_categories('name=cat['.$i.'][]&hierarchical=1&hide_empty=0&echo=0&show_option_none=No asignar')."</td></tr>";
                    
                }
                $retornar .= "</table></td>";
                $retornar .= "</tr>";
            }
            $retornar .= "</tbody></table>";
        $retornar .= "</div>";
        }
         $retornar .= "<p class='submit'><input type='submit' class='button-primary' value='Crear Entradas' /></td>";
        $retornar .= "</form>";
        $retornar .= "</div>";
        
        return $retornar;
    }
    
    /**
     *  CREAR ENTRADAS - PASO 3 -  Crear las entradas (post) y mostrar una previsualización
     *  
     *  Recoge los datos que vienen del crear_entradas_configurar.
     *  Configura los datos en un array $posts y genera la creación de entradas. 
     */
    function crear_entradas(){
    
     
        // creando el array posts - a partir de los regs.
       $posts = $this->get_posts_array();  
       
       echo "<div class='ventana'><h3>N&uacute;mero entradas creadas: ".count($posts)."</h3></div>";
               
        // datos configuracion entradas
       echo "<div class='ventana'>".$this->get_config_global()."</div>";
        
        
       // previsualización del primer registro (entrada)
       echo $this->get_post_previ($posts[2]); 
    
  
     
       
        foreach($posts as $num_post => $valores){
                     $n_post_insertar = $num_post;
                     $titulo = $posts[$n_post_insertar]['titulo'];
                     $contenido = $posts[$n_post_insertar]['contenido'];
                     $categorias = $posts[$n_post_insertar]['categorias']; // array de ids de categorias
                     $autor_id = $posts[$n_post_insertar]['autor_id'];
                     $type_contenido = $posts[$n_post_insertar]['type_contenido'];
                     $estatus_entrada = $posts[$n_post_insertar]['estatus_entrada'];
                     $permitir_comentarios = $posts[$n_post_insertar]['permitir_comentarios'];
                    
                     
                     // Titulo - contenido - categorias
                     $my_post = array(
                          'post_title' => $titulo,
                          'post_content' => $contenido,
                          'post_status' => $estatus_entrada,
                          'comment_status' => $permitir_comentarios,
                          'post_author' => $autor_id,
                          'post_category' => $categorias,
                          'post_type' => $type_contenido
                     );
                    
                      $post_id =  wp_insert_post( $my_post );
                                         
                       // imagen destacada
                       if($posts[$n_post_insertar]['imagen_destacada'] != ""){
                           $filename = $posts[$n_post_insertar]['imagen_destacada'];
                               $wp_filetype = wp_check_filetype(basename($filename), null );
                               $attachment = array(
                                    'post_mime_type' => $wp_filetype['type'],
                                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                                    'post_content' => '',
                                    'post_status' => 'inherit',
                                    'guid' => $filename
                               );
                               $attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
                               // you must first include the image.php file
                               // for the function wp_generate_attachment_metadata() to work
                               require_once(ABSPATH . 'wp-admin/includes/image.php');
                               $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                               wp_update_attachment_metadata( $attach_id, $attach_data ); 
                               update_post_meta($post_id, "_thumbnail_id", $attach_id);
                       }
                       
                       // campos personalizados
                       foreach($posts[$n_post_insertar]['campos_personalizados'] as $nombre_meta => $valor_meta){
                          add_post_meta($post_id, $nombre_meta, $valor_meta);
                       }
                      
             } 
       
                                                 
         
         return $retornar;
    }
    
     /**
     *  Recoge los datos de una tabla en avit_datos y los convierte en un array util para crear posts.
     * 
     *  @return un array con los posts.
     *  
     */
    function get_posts_array(){
        
        $campos_titulo = $_POST['en_titulo'];
        $campos_titulo_pre = $_POST['en_titulo_texto_pre'];
        $campos_contenido = $_POST['en_contenido'];
        $campos_personalizados = (is_array($_POST['campo_personalizado'])) ? $_POST['campo_personalizado'] : array();
        $campos_personalizados_sufijo = $_POST['campos_personalizados_sufijo'];
        $campo_imagen_destacada = $_POST['imagen_destacada'];
        $campos_asignar_categoria = $_POST['asignar_categoria'];
        $para_el_valor = $_POST['para_el_valor'];
        $categoria_asignada = $_POST['cat'];
        
        $autor_id = $_POST['autor'];
        $autor_nombre = get_author_name($autor_id);
        
        $type_contenido = $_POST['type_contenido'];
        $estatus_entrada = $_POST['estatus_entrada'];
        $permitir_comentarios = $_POST['permitir_comentarios'];
      
        // Datos config imagenes
        $imagenes_enlace = $_POST['imagenes_enlace'];
        $imagenes_alto = $_POST['imagenes_alto'];
        $imagenes_ancho = $_POST['imagenes_ancho'];
        $carpeta_imagenes = $_POST['carpeta_imagenes'];
        
        $avit_id = $_POST['avit_id'];
        $carpeta_uploads = wp_upload_dir();
        
        // array con todos los registros de la tabla avit_datos
        $regs = $this->get_datos_array($avit_id);
      
        // creando el array posts - a partir de los regs.
        $posts = array();
        foreach($regs as $r => $valores){
            $reg = $regs[$r];
            
            // autor
            $posts[$r]['autor_id'] = $autor_id;
            $posts[$r]['autor_nombre'] = $autor_nombre;
            
            // config global la ponemos en cada post
            $posts[$r]['type_contenido'] = $type_contenido;
            $posts[$r]['estatus_entrada'] = $estatus_entrada;
            $posts[$r]['permitir_comentarios'] = $permitir_comentarios;
            
            // titulo
            $posts[$r]['titulo'] = "";
            for($i = 0 ; $i < sizeof($campos_titulo) ; $i++){
                $campo_titulo = $campos_titulo[$i];
                $valor_campo_titulo = $reg[$campo_titulo];
                
                // texto pre titulo
                if(trim($campos_titulo_pre[$campo_titulo]) != "")
                    $posts[$r]['titulo'] .= $campos_titulo_pre[$campo_titulo]." ";

               $posts[$r]['titulo'] .= $valor_campo_titulo;
            }
            
            // contenido
             $posts[$r]['contenido'] = "";
             $orden_salida = 1;
            foreach($campos_contenido as $i => $valor){
                
                $posts[$r]['contenido'] .= $campos_contenido[$i]['html_pre']; 
                
                $tipo_contenido = $this->get_posts_tipo_contenido($campos_contenido[$i]); // tipo de contenido (texto-imagen-more)
                $campo_contenido = $campos_contenido[$i][$tipo_contenido]; // nombre del campo
                $valor_campo_contenido = $reg[$campo_contenido]; // se coge el valor del array $regs
                
                if($tipo_contenido == "imagen"){
                    $url_imagen = $carpeta_uploads['baseurl']."/".$carpeta_imagenes."".$valor_campo_contenido;
                    $dir_imagen = $carpeta_uploads['basedir']."/".$carpeta_imagenes."".$valor_campo_contenido;                     
                    $posts[$r]['contenido'] .= $this->get_posts_imagen($url_imagen,$dir_imagen,$imagenes_ancho,$imagenes_alto,$imagenes_enlace);
                }
                else if($tipo_contenido == "more" &&  $orden_salida < count($campos_contenido) ){
                    $posts[$r]['contenido'] .= "<!--more-->";
                }
                else{
                    $posts[$r]['contenido'] .= $valor_campo_contenido;
                }
              
                $posts[$r]['contenido'] .= $campos_contenido[$i]['html_post'];
            $orden_salida++;
            }
            
            // imagen destacada
            $posts[$r]['imagen_destacada'] = "";
            if($campo_imagen_destacada != "" && $reg[$campo_imagen_destacada] != ""){
                $imagen = $reg[$campo_imagen_destacada];
                $posts[$r]['imagen_destacada'] = $carpeta_uploads['baseurl']."/".$carpeta_imagenes.$imagen;;
            }
            
            
            // campos personalizados
            $posts[$r]['campos_personalizados'] = array();
            for($i = 0 ; $i < sizeof($campos_personalizados) ; $i++){
                if(substr($campos_personalizados[$i],0,1) == "_"){
                    $key_para_el_valor = substr($campos_personalizados[$i],1);
                }else{
                    $key_para_el_valor = $campos_personalizados[$i];
                }
                $nombre_campo_per = $campos_personalizados[$i].$campos_personalizados_sufijo;
                $posts[$r]['campos_personalizados'][$nombre_campo_per] = $reg[$key_para_el_valor];
            }
            
            // asignar categoria
            $categorias_para_este_post = $this->get_posts_categorias($reg,$campos_asignar_categoria,$para_el_valor,$categoria_asignada); 
            
            $posts[$r]['categorias'] = $categorias_para_este_post['ids'];
            
        }
        return $posts;
    }
    
    /**
     *  Función auxiliar para el proceso crear_entradas -> crear array $posts
     *  
     *  Recibe el array $campos_contenido[$i] - siendo $i el orden procedente del paso 2 (configurar)
     * 
     *  @return el tipo de contenido (texto - imagen - more) que se almacena en la clave del siguiente nivel del array
     *  
     */
    function get_posts_tipo_contenido($campo_contenido){
        $tipo_contenido = "texto";
        if(array_key_exists("imagen",$campo_contenido)){
            $tipo_contenido = "imagen";
        }
        if(array_key_exists("more",$campo_contenido)){
            $tipo_contenido = "more";
        }
        return $tipo_contenido;
    }
    
    /**
     * Esta función se usa para general el html de una imagen incluida en el contenido del post.
     * Dentro del array $posts. 
     * 
     * @return Retorna el html de un tag imagen
     */
    function get_posts_imagen($url_imagen,$dir_imagen,$imagenes_ancho,$imagenes_alto,$con_enlace=true){
        
        if($imagenes_ancho == "")
           $imagenes_ancho = get_option('thumbnail_size_w');
        if($imagenes_alto == "")
           $imagenes_alto =get_option('thumbnail_size_h');
        
        $imagen = "";       
        if(file_exists($dir_imagen) && !is_dir($dir_imagen )){
            $info_archivo = av_archivo_info($dir_imagen);
            //print_r($info_archivo);
            $nombre_imagen = $info_archivo['archivo'];
            if($con_enlace){
                $imagen .= "<a href='".$url_imagen."' title='".$nombre_imagen."'/>";
            }
            // las medidas de la imagen original
            $img_alto = $info_archivo['alto'];
            $img_ancho = $info_archivo['ancho'];
            
            // redimensionar la imagen
            
            $redimensionar = av_redimensionar($img_ancho,$img_alto,$imagenes_ancho,$imagenes_alto);
           
            // nuevos valores ancho y alto
            $img_alto = $redimensionar['alto'];
            $img_ancho = $redimensionar['ancho'];

            $imagen .= "<img src='".$url_imagen."' width='".$img_ancho."' height='".$img_alto."' />";
            if($con_enlace){
                $imagen .= "</a>";
            }    
        }
        
        return $imagen;
    }
    
    /**
     * Genera un array con las categorias a asignar al post activo (en funcion de los campos a convertir en categorias)
     * Un registro puede tener seleccionados mas de un campo para asignar categorias.
     * 
     * @param $registro_activo (un array con los datos del registro activo)
     * @param $campos_asignar_categorias (un array con los nombres de los campos que se deben asociar)
     * @param $para_el_valor (array con todos los valores)
     * @param $categoria_asignada (array con las categorias asignada a los valores)
     * 
     * (nota - el factor de union de los tres parametros ($campos_asignar_categorias, $para_el_valor, $categoria_asignada)
     *          es el primer nivel del array [$i] - como numero de orden comun.
     * )
     * @example Ejemplo:
     *      $campos_asignar_categoria[$i]   -> (nombre del campo) - producto_cat
     *      $para_el_valor[$i]              -> (array con los valores distintos de producto_cat) - (1,2,3,4,5,6)
     *      $cagegoria_asignada             -> (array con los valores -id- de la categoria para cada valor distinto) - (45,33,4,5)
     * 
     * @return Retornar un array multidimensional con las categorias asignadas a una entrada determinada. Con la siguiente estructura
     *      $cats['ids'] = array(ids de las categorias)
     *      $cats['nombres'] = array(nombres de las categoarias)
     * 
     */
    function get_posts_categorias($registro_activo,$campos_asignar_categoria,$para_el_valor,$categoria_asignada){
        
        $cats = array();
        
        // la variable $i és el factor común
        for($i = 0 ; $i < sizeof($campos_asignar_categoria) ; $i++){
            $campo_asignar = $campos_asignar_categoria[$i]; // cogemos el primer nombre de campo
           
            // cogemos los valores distintos del campo_asignar
            for($d = 0 ; $d < sizeof($para_el_valor[$i]) ; $d++){
                $para = $para_el_valor[$i][$d]; // cada valor distinto
                $cat = $categoria_asignada[$i][$d]; // categoria asignada al valor distinto
                
                // se comprueba que la seleccion de la categoria no se haya dejado vacía
                if($cat != -1){
                    // se comprueba si el valor del campo del registro activo coincide con el valor distinto
                    if($registro_activo[$campo_asignar] == $para){
                        $cats['ids'][] = $cat; // generamos el subarray con los ids de las categorias
                        $cats['nombres'][] = get_cat_name($cat); // el subarray con los nombres de la categoria
                    }
                }
            }
        }
        return $cats;
    }
    
    /**
     * @return el html - de un post (para la previsualización)
     * 
     * en el tercer paso de crear entradas.
     */
    function get_post_previ($post_array){
         
        $retornar = "";
        $retornar .="<div class='ventana'>";
            $retornar .="<h3>Entrada de ejemplo.</h3>";
            $retornar .="<div class='avit_info post'>";
                $retornar .="<h1 class='post-title'>".$post_array['titulo']."</h2>";
                
                $nom_cat = get_cat_name($post_array['categorias'][0]);
                $retornar .="<p>Publicado en ".$nom_cat." Por ". $post_array['autor_nombre'] ."</p>";
                $retornar .=str_replace("<!--more-->","<p style='color:blue'> Seguir leyendo... </p>",$post_array['contenido']);
             $retornar .="</div>";
       $retornar .="</div>";
       
       return $retornar; 
    }
    
    
    function get_config_global(){
        // Datos config entradas
        $type_contenido = $_POST['type_contenido'];
        $permitir_comentarios = $_POST['permitir_comentarios'];
        $estatus_entrada = $_POST['estatus_entrada'];
        $autor_id = $_POST['autor'];
        $autor_nombre = get_author_name($autor_id);
        $retornar = "";        
        
        $retornar .="<h3>Datos de configuraci&oacute;n</h3>";
        $retornar .="<table class='widefat'>";
        $retornar .="<thead>
                <th>Entradas</th>
             </thead><tbody><tr>";
             $retornar .="<td>
                    <p><strong>Post Type:</strong> ".$type_contenido."</p>
                    <p><strong>Autor:</strong> ".$autor_nombre."</p>
                    <p><strong>Estado entradas:</strong> ".$estatus_entrada."</p>
                    <p><strong>Permitir Comentarios:</strong> ".$permitir_comentarios."</p>
                    
                 </td>";
        $retornar .="</tr></tbody></table>";
        
        
        return $retornar;                
    }
    /**
     *  Función auxiliar
     *  
     *  Recibe el id de la tabla en avit
     *  
     *  El array de retorno seria   cit['campos'] = array() con los nombres de los campos
     *                              cit['tipos']  = array() con los nombres de los tipos de cada campo (input_text - textarea)
     * 
     *  @return un array con dos claves (campos y tipos) con los campos y los tipos de la tabla solicitada.
     *  
     */
    function get_campos_y_tipos($avit_id){
        global $avit_db;
        $campos = $avit_db->dato("campos","avit","id=$avit_id");
        $campos = explode(",",$campos);
        
        $tipos_lista = $avit_db->dato("tipos","avit","id=$avit_id");
        $tipos_arr = explode(",",$tipos_lista);
        
        
        for($i = 0 ; $i < sizeof($campos)  ; $i++){
            $tipos[$campos[$i]] = $tipos_arr[$i];
        }
        
        $cit['campos'] = $campos;
        $cit['tipos']  = $tipos;
        
        return $cit;
    }
    /**
     *  Función auxiliar
     *  
     *  Recibe el id de la tabla en avit
     * 
     *  @return un array con los registros de la tabla solicitada:
     *          $regs['numero_de_registro']['nombre_del_campo'] = valor_del_campo
     *  
     */
    function get_datos_array($avit_id){
        global $avit_db;
        $regs = $avit_db->regs("avit_datos","avit_id=$avit_id");
   
        $datos = array();
        for($i = 0 ; $i < sizeof($regs) ; $i++){
            $reg = $regs[$i];
            $num_reg = $reg['reg_num'];
            $campo   = $reg['campo'];
            $valor   = $reg['valor'];
            
            $datos[$num_reg][$campo] = $valor;
        }
        
        return $datos;
        
    }
    /**
     *  Función auxiliar
     *  
     *  Recibe los siguientes parametros:
     * @param $avit_id - el id de la tabla en avit
     * @param $orden   - si se mostrará el boton para ordenar los items de la tabla
     * @param $incluir - si se mostrará la casilla de seleccion de cada item
     * @param $inputs  - si los datos mostrados iran en un input.
     * @param $colapsado - si la tabla de registros se mostrará abierta o colapsada.
     * 
     * @return html de la tabla construida en función de los parámetros.
     *  
     */
    function get_tabla_grid($avit_id,$orden="",$incluir="",$inputs="",$colapsado=false){
       
         
        $regs = $this->get_datos_array($avit_id); //print_r($regs);
        $n_registros = count($regs);
        $campos_y_tipos = $this->get_campos_y_tipos($avit_id);
        $campos = $campos_y_tipos['campos'];
        $tipos = $campos_y_tipos['tipos'];
        
        $classes_div = "colapsable ventana";
        $mas_classes = "";
        if($colapsado)
            $mas_classes = " closed";
       
        $retornar = "";
        $retornar .="<div class='colapsable ventana ".$mas_classes."' style='max-height:500px;overflow:auto;'>";
            $retornar .="<h3 style='padding:10px;'>Registros (".$n_registros.") </h3>";
            $retornar .="<div class='inside'>";
                $retornar .="<table class='widefat'>";
                $retornar .="<thead>";
                
                if($orden)
                $retornar .= "<th style='text-align:center !important;'>Orden</th>";
                
                if($incluir)
                $retornar .= "<th style='text-align:center !important;'>Incluir</th>";
                
                for($i = 0 ; $i < sizeof($campos) ; $i++){
                    $nombre_campo = $campos[$i];
                    $retornar .= "<th style='font-size:10px;'>";
                    
                        if( strlen($nombre_campo > 15 ))
                            $retornar .= substr($nombre_campo,0,15)."...";
                        else
                            $retornar .= $nombre_campo;
                    
                        
                    $retornar .= "</th>";
                }
                $retornar .= "</thead>";
                $retornar .= "<tbody class='sortable'>";
                
                
                $odd = true;
                
                foreach($regs as $num_reg => $campo_valor){
                
                    
                    if($odd){
                        $style_odd = "style='background:#ffffff'";
                        $odd = false;
                    }else{
                        $style_odd = "style='background:#f9f9f9'";
                        $odd =true;
                    }
                     
                    $retornar .="<tr ".$style_odd.">";
                    
                    if($orden)
                    $retornar .= "<td class='mover' style='text-align:center !important;'><img src='".AVIT_PLUGIN_URL."/imagenes/mover.png' width='18' height='18' /></td>";
                    
                    if($incluir)
                    $retornar .="<td style='text-align:center !important;'><input type='checkbox' name='incluir_reg[".$num_reg."]' value='1' checked='true' /> </td>";
                    
                    //foreach($campo_valor as $nombre_campo => $valor){
                    for($c = 0 ; $c < sizeof($campos) ; $c++){
                        
                        $nombre_campo = $campos[$c];
                        $valor  = $campo_valor[$nombre_campo];
                        $retornar .="<td  style='min-width:60px !important;max-width:230px !important'>";
                        
                        if($inputs){
                            $tipo = $tipos[$nombre_campo];
                            
                            if($tipo=="textarea"){
                               $retornar .="<textarea name='valor_campo[".$num_reg."][".$nombre_campo."]'>".$valor."</textarea>"; 
                            }else{
                               $retornar .="<input type='text' name='valor_campo[".$num_reg."][".$nombre_campo."]' value='".$valor."' />";
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
    
    
}
?>