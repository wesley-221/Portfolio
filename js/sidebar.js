$(document).ready(function(){
	var expanded = 0;

	$("#showmenu").on('click', function(){
		if(expanded === 0) {
			$("#sidebar").animate({ width: "40%" }, { duration: 200, start: function(){ $("#sidebar").toggleClass('sidebarshow'); }, complete: function(){ expanded = 1; }});
		}
		else {
			$("#sidebar").animate({ width: "0%" }, { duration: 200, complete: function(){ expanded = 0; $("#sidebar").toggleClass('sidebarshow'); }});
		}
	});
});
