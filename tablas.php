<?php

/**
 * @author ohyeah
 * @copyright 2012
 */
    
     
           
  $tablas_av = $avit_db->regs("avit","id>0");
            
            
  if(!isset($_GET['proceso'])){
    
  ?>   
    <div class="avit_info" style="display: block;margin-top:40px;">
        
        <table class="widefat">
        <thead><th>Tablas</th><th>Registros</th></thead>
        <tbody>
            <?php
            if($tablas_av != 0)
    	       foreach($tablas_av as $orden => $campo_valor){
                $tabla_av = $campo_valor['avit'];
                $avit_id = $campo_valor['id'];
	
            ?>
            <tr>
                <td>
                    <a href="<?php echo AVIT_PAGE ?>&action=tablas&proceso=editar&avit_id=<?php echo $avit_id ?>"" class="row-title"><?php echo $tabla_av ?></a>
                    <div class="row-actions">
                        <a href="<?php echo AVIT_PAGE ?>&action=tablas&proceso=editar&avit_id=<?php echo $avit_id ?>" >Editar</a> |  
                        <a href="<?php echo AVIT_PAGE ?>&action=tablas&proceso=crear_entradas&avit_id=<?php echo $avit_id ?>" >Crear Entradas</a> |  
                        <a href="<?php echo AVIT_PAGE ?>&action=tablas&proceso=crear_categorias&avit_id=<?php echo $avit_id ?>" >Crear Categor&iacute;as</a> | 
                        <a href="<?php echo AVIT_PAGE ?>&action=tablas&proceso=crear_etiquetas&avit_id=<?php echo $avit_id ?>" >Crear Etiquetas</a> | 
                        <a style="color: red;" href="<?php echo AVIT_PAGE ?>&action=tablas&proceso=eliminar_tabla&avit_id=<?php echo $avit_id ?>" >Eliminar</a>   
                    </div>
                </td>
                <td>
                    <p><?php echo $campo_valor['n_regs']; ?></p>
                </td>
            </tr>
            <?php
                
            
            }else{
               ?>
            <tr>
                <td>
                    <p>No hay tablas</p>    
                    </div>
                </td>
            </tr>
            <?php
             
            }
            ?>
        </tbody>
        </table>
         
       
    </div>

<?php
	}else{
	   
        
       
        
	   $proceso = $_GET['proceso'];
       $avit_id = ($_POST['avit_id']) ? $_POST['avit_id'] : $_GET['avit_id'];
         
       $nombre_tabla = $avit_db->dato("avit","avit","id=$avit_id");
       if($proceso == "editar"){
            if(!isset($_GET['paso'])){
                echo "<h3>Editar Tabla</h3>";
                echo   $avit_inst->form_editar_tabla($avit_id);
            }else{
                echo   $avit_inst->editar_tabla(); 
                header("Location: ".AVIT_PAGE);
            }
       }
       
       else if($proceso == "crear_entradas"){
            echo "<h3>Crear Entradas. TABLA: ".$nombre_tabla.".</h3>";
            if(!isset($_GET['paso'])){
                echo "<img src='".AVIT_PLUGIN_URL."imagenes/tablas_pasos1.jpg' />";
               echo   $avit_inst->form_crear_entradas($avit_id);
            }
            else if($_GET['paso']==2){
                echo "<img src='".AVIT_PLUGIN_URL."imagenes/tablas_pasos2.jpg' />";
               echo   $avit_inst->crear_entradas_configurar(); 
            }
            else if($_GET['paso'] == 3){
                echo "<img src='".AVIT_PLUGIN_URL."imagenes/tablas_pasos3.jpg' />";
                echo   $avit_inst->crear_entradas();
            }
       }
       else if($proceso == "crear_categorias"){
            if(!isset($_GET['paso'])){
               echo "<h3>Crear Categor&iacute;as</h3>";
               echo   $avit_inst->form_crear_categorias($avit_id);
            }else{
               echo   $avit_inst->crear_categorias(); 
            }
       }
       else if($proceso == "crear_etiquetas"){
            if(!isset($_GET['paso'])){
               echo "<h3>Crear Etiquetas<p><small>Se desestiman los duplicados.</small></p></h3>"; 
               echo   $avit_inst->form_crear_categorias($avit_id,false,"tablas&proceso=crear_etiquetas&paso=2");
            }else{
               echo   $avit_inst->crear_etiquetas(); 
            }
       }
       else if($proceso == "eliminar_tabla"){
            if(!isset($_GET['paso'])){
              
               echo   $avit_inst->form_eliminar_tabla($avit_id);
            }else{
               echo   $avit_inst->eliminar_tabla();
               header("Location: ".AVIT_PAGE); 
            }
       }
       
       
       
	}
?>

