var currentLocation = window.location.href;

function checkLocation() {

	if(window.location.href != currentLocation) 
	{
		reload();
	}

}

function reload() {
	currentLocation = window.location.href;
	$('div.ui-layout-center').load(currentLocation.replace(/#/, '/'), {}, init);
	return false;
}

function init() {
	
	$('table.list tbody tr:even').addClass('even');
	$('table.list tbody tr:odd').addClass('odd');
	
	$('div.ui-layout-center form').ajaxForm({
		success: function(responseText, statusText) {
			if(responseText.match(/redirect:(.*)/))
			{
				window.location.href = RegExp.$1;
			}
			else
			{
				$('div.ui-layout-center').html(responseText);
				init();
			}
		}
	});

	if(currentLocation.match(/database\/(\w+)#tables\/(\w+)\//))
	{
		schema = RegExp.$1.toString();
		table = RegExp.$2.toString();
		
		$('#bc_table a span').text(table);
		$('#bc_table a').attr('href', baseUrl + '/database/' + schema + '#tables/' + table + '/structure');
		$('#bc_table').show();
	}
	else 
	{
		$('bc_table').hide();
	}
	
	// Add checkboxes to respective tables
	try 
	{
		$('table.addCheckboxes').each(function() {
			$(this).addCheckboxes(this.id).removeClass('addCheckboxes');
		});
	}
	catch(exception) {}
}

$(document).ready(function()
{

	$('body').layout({
		
		// General
		applyDefaultStyles: true,

		// North
		north__size: 40,
		north__resizable: false,
		north__closable: false,
		north__spacing_open: 1,

		// West
		west__size: userSettings.sidebarWidth,
		west__initClosed: userSettings.sidebarState == 'closed',
		west__onresize_end: function () {
			myAccordion.accordion('resize');
			// Save
			$.post(baseUrl + '/ajaxSettings/set', {
					name: 'sidebarWidth',
					value: $('.ui-layout-west').width()
				}
			);
			return;
		},
		west__onclose_end: function () {
			myAccordion.accordion('resize');
			// Save
			$.post(baseUrl + '/ajaxSettings/set', {
					name: 'sidebarState',
					value: 'closed'
				}
			);
			return;
		},
		west__onopen_end: function () {
			myAccordion.accordion('resize');
			// Save
			$.post(baseUrl + '/ajaxSettings/set', {
					name: 'sidebarState',
					value: 'open'
				}
			);
			return;
		}
	});

	// ACCORDION - inside the West pane
	var myAccordion = $("#MainMenu").accordion({
		selectedClass: "active",
		fillSpace: true,
		autoHeight: true,
		collapsible: false,
		animated: "slide"
	});
	
	// Ajax loader 
	$(document).ajaxStart(function() {
		$('#loading').css({'background': '#FF0000'}).fadeIn();
	});
	
	$(document).ajaxStop(function() {
		$('#loading').css({'background': '#009900'}).fadeOut();
	});

	setInterval(checkLocation, 100);
	
	if(currentLocation.indexOf('#') > -1)
	{
		$('div.ui-layout-center').load(currentLocation.replace(/#/, '/'), {}, init);
	}
	else
	{
		init();
	}

});

