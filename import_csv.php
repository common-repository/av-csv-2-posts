
<?php

    // incluimos la clase av_import_csv y la instanciamos.
    include("classes/av_import_csv.class.php");
    $av_import = new av_import_csv();

	$paso = 1;
    
    if(isset($_GET['paso']))
        $paso = $_GET['paso'];
                
   if($paso == 1){
      echo "<img src='".AVIT_PLUGIN_URL."imagenes/csv_pasos1.jpg' />";
      echo $av_import->avit_form_subir_csv("import_csv&paso=2");
   }
   else if($paso == 2){
      echo "<img src='".AVIT_PLUGIN_URL."imagenes/csv_pasos2.jpg' />";
      echo $av_import->avit_form_csv();
   }
   else if($paso == 3){
      echo "<img src='".AVIT_PLUGIN_URL."imagenes/csv_pasos3.jpg' />";
      echo $av_import->avit_guardar_en_tabla();
      echo "<a href='".AVIT_PAGE."' class='button-primary'>Ir a Tablas</a>";
   }
         
	       
        ?>
       
    