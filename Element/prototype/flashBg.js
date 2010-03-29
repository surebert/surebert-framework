/**
@Name: Element.prototype.flashBg
@Description: Flashes the background color with another color for a timeout to show activity e.g. save, delete
@Example:
myElement.flashBg('red');
*/
Element.prototype.flashBg = function(color, timeout){
	timeout = timeout || 1000;

	this.style.backgroundColor = color;
	var t = this;
	window.setTimeout(function(){
		t.style.backgroundColor = '';

		t = null;
	}, 1000);
};