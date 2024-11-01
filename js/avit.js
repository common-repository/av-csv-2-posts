
jQuery(document).ready(function(){
    
    jQuery('table tbody.sortable').sortable({handle: 'td.mover'});
  
    jQuery('.colapsable h3').css("cursor","pointer").prepend('<a class="togbox">+</a> ');
    jQuery('.colapsable h3').click( function() {
        jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
    });
    
    jQuery('.ventana table tr:even').addClass('tr_odd');

})
