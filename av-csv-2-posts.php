<?php
/*
Plugin Name: AV csv 2 posts
Version: 1.0
Author: Xiaobai

Description: AV csv to posts importa archivos CSV e inserta los datos en una tabla de la BBDD (avit y avit_datos). Con esos datos se pueden crear nuevas entradas, pudiendo personalizar el HTML de salida. Incluye: campos personalizados, imagen destacada, asignaciones a categor&iacute;as y separador --more--. As&iacute; como asignar el tipo de contenido (post_type) donde crearlos. Tambi&eacute;n crea Categor&iacute;as y etiquetas de forma masiva. 


*/

define("AVIT_PLUGIN_DIR",plugin_dir_path( __FILE__ ));
define("AVIT_PLUGIN_URL",plugin_dir_url( __FILE__ ));
define("AVIT_CSS_URL",plugin_dir_url( __FILE__ )."/css/");
define("AVIT_JS_URL",plugin_dir_url( __FILE__ )."/js/");
define("AVIT_PAGE","tools.php?page=av_csv2posts");



// incluimos las funciones av
// incluimos la clase db i la instanciamos en $avit_db
include("funcions.php");
include("classes/avit.class.php");
     $avit_inst = new avit();
 include("classes/db.php");
     $avit_db = new db();       
add_action('admin_init', 'avit_admin_init');
add_action('admin_menu', 'avit_admin_menu');
    
function avit_admin_init()
{
    
    //wp_register_script('jquery', AVIT_PLUGIN_URL . '/js/jquery-1.5.1.min');
	wp_register_script('avit.js', AVIT_JS_URL . 'avit.js');
	wp_register_style('avit.css', AVIT_CSS_URL . 'avit.css');
	
    
}

function avit_crear_tablas(){
    global $avit_db;
    $table_avit_camps = array("id","avit","campos","tipos","n_regs","n_campos");
    $table_avit_tipus = array("int(10)","varchar(200)","text","text","int(5)","int(5)",);
    
    $table_avit_datos_camps = array("id","reg_num","campo","valor","avit_id");
    $table_avit_datos_tipus = array("int(10)","int(5)","varchar(200)","text","int(5)");
    
    $avit_db->av_create_table_db("avit",$table_avit_camps,$table_avit_tipus,"id");
    $avit_db->av_create_table_db("avit_datos",$table_avit_datos_camps,$table_avit_datos_tipus,"id");

}
function avit_admin_menu()
{
	$page = add_management_page("", "Av CSV 2 Posts", 1, "av_csv2posts", "avit_manage_menu");
	add_action('admin_print_scripts-' . $page, 'avit_admin_styles');
}

function avit_admin_styles()
{
	//wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('avit.js');
    wp_enqueue_style('avit.css');

    
   
}


function avit_manage_menu()
{
    global $wpdb,$avit_inst,$avit_db;
    avit_crear_tablas();
    
    echo "<div id='av_wrap' class='wrap'>";
    echo "<h2>AV CSV 2 POSTS.</h2>";
        echo "<ul class='subsubsub avit_menu' >";
        echo "<li><a href='".AVIT_PAGE."&action=tablas'>".__('Tablas')."</a> | </li>";
        echo "<li><a href='".AVIT_PAGE."&action=import_csv'>".__('Importar CSV')."</a> | </li>";
        
        
        echo "</ul>";
        $accion = "tablas";
        
        if(isset($_GET['action'])){
            $accion = $_GET['action'];
        }
        
        
        include($accion.".php");
    echo "</div>";
    
    
//	include("pruebas.php");
}

?>