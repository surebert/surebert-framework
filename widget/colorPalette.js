
/**
@Name: sb.widgets.colorPalette
@Description: Used to create a websafe color palette table
@Author: Paul Visco v1.01 11/05/07 12/15/08
@Example:

sb.include('widget.colorPalette');

var table = new sb.widget.colorPalette({
	id : 'jimmy',
	events : {
		click : function(e){
			if(td.nodeName =='TD'){
					if(table.selectedColor){
						table.selectedColor.style.border='';
					}
				$('body').style.backgroundColor=td.style.backgroundColor;
				td.style.border='1px solid white';
				table.selectedColor = td;
			}
		}
	}
});

table.appendToTop('body');
*/
sb.widget.colorPalette = function(o){
	this.colors = o.colors || ['00','22','44', '66', '88', 'BB',  'DD', 'FF'];
	
	var table = new sb.element({
		tag : 'table',
		id : o.id || 'sb_web_safe_palette',
		events : o.events || {}
	});
	
	var td,tbody = document.createElement('tbody');
	table.appendChild(tbody);
		
	for (var i = this.colors.length-1; i >= 0; i--) {
		var tr = document.createElement('tr');
		tbody.appendChild(tr);
		
		for (var j = this.colors.length-1; j >= 0; j--) {
			
			for (var k= this.colors.length-1; k >= 0; k--) {
				color = this.colors[i]+this.colors[j]+this.colors[k];
				td = document.createElement('td');
				tr.appendChild(td);
				td.style.backgroundColor = '#'+color;
				td.style.width = '2px';
				td.style.height = '2px';
				td.style.fontSize = '0px';
				td.style.cursor = 'pointer';
			}
		}
		
		color = this.colors[i]+this.colors[i]+this.colors[i];
		td = document.createElement('td');
		tr.appendChild(td);
		
		td.style.backgroundColor = '#'+color;
				
	}
	return table;
};
