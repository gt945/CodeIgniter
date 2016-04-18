function switch_to_tab(name, caption, target) {
	var tabs = $("#tabs_div").tabs();
	id = "tabs-" + name;
	if ($("#" + id).length == 0) {
		if (target != '') {
			tabTemplate = "<li><a href='#{href}'>#{label}</a> <span class='ui-icon ui-icon-close' role='presentation'>Remove Tab</span></li>";
			li = $(tabTemplate.replace(/#\{href\}/g, "#" + id).replace(/#\{label\}/g, caption));

			tabs.find(".ui-tabs-nav").append(li);
			tabs.append("<div id='" + id + "'></div>");
			$.get(baseurl + "index.php/" + target, function(data) {
				$("#" + id).html(data);
				tabs.tabs("refresh");

			});
			tabs.tabs("refresh");
		}
	}
	var index = $('#tabs_div a[href="#'+id +'"]').parent().index();
	tabs.tabs("option", "active", index);
}
function adjust_size() {
	$(".ui-jqgrid-btable").each(
			function() {
				$(this).jqGrid('setGridWidth', $('#tab_main').width());
				$(this).jqGrid(
						'setGridHeight',
						$('#tabs_div').height() - 135);
			});
	$("#accordion1").accordion("refresh");
}
function select2_change() {
	$("select.FormElement").trigger("change.select2");
}

function select2_init(elm) {
//	$(elm).width(156).select2( {dropdownCssClass: "ui-widget ui-jqdialog" });
	$(elm).width(156).select2();
	$("select.input-elm").trigger("change.select2");
}

$(document).ready( function() {

	myLayout = $('body').layout({
		west__size:			300,
		onresize_end: adjust_size
	});

	$("#accordion1").accordion({
		heightStyle:	"fill"
	});
	
	tabs = $("#tabs_div").tabs();
    tabs.delegate( "span.ui-icon-close", "click", function() {
        var panelId = $( this ).closest( "li" ).remove().attr( "aria-controls" );
        $( "#" + panelId ).remove();
        tabs.tabs( "refresh" );
      });
});
