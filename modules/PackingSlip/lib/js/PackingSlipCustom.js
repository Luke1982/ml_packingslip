$(function(){
	$("#proBody").sortable({
		placeholder: "ui-state-highlight",
		helper: fixHelper,
	    start: function(e, ui){
	        ui.placeholder.height(ui.item.height());
	    }
	});
});

var fixHelper = function(e, ui) {  
  ui.children().each(function() {  
    $(this).width($(this).width());  
  });  
  return ui;  
};