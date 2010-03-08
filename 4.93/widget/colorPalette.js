
/**
@Name: sb.widgets.colorPalette
@Description: Used to create a websafe color palette table
@Author: Paul Visco v1.01 11/05/07 12/15/08
@Example:

sb.include('widget.colorPalette');

var table = new sb.widget.colorPalette('jimmy');
table.evt('click', function(e){
	
	var td = e.target;
	if(td.nodeName =='TD'){
			if(table.selectedColor){
				table.selectedColor.style.border='';
			}
		$('body').style.backgroundColor=td.style.backgroundColor;
		td.style.border='1px solid white';
		table.selectedColor = td;
	}
	
});
table.appendToTop('body');
*/
sb.widget.colorPalette = function(id){
	var color,td;
	var colors = ['00','33','66','99','CC','FF'];
	//var colors = ['00','11','22','33','44','55', '66', '77', '88', 'AA', 'BB', 'CC', 'DD', 'EE', 'FF'];
	var table = new sb.element({
		tag : 'table',
		id : id || 'sb_web_safe_palette'
	});
	
	var tbody = document.createElement('tbody');
	table.appendChild(tbody);
		
	for (var i = colors.length-1; i >= 0; i--) {
		var tr = document.createElement('tr');
		tbody.appendChild(tr);
		
		for (var j = colors.length-1; j >= 0; j--) {
			
			for (var k= colors.length-1; k >= 0; k--) {
				color = colors[i]+colors[j]+colors[k];
				td = document.createElement('td');
				tr.appendChild(td);
				td.style.backgroundColor = '#'+color;
			}
		}
		
		color = colors[i]+colors[i]+colors[i];
		td = document.createElement('td');
		tr.appendChild(td);
		
		td.style.backgroundColor = '#'+color;
				
	}
	return table;
};
