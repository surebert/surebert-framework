/**
@Name: Element.prototype.redraw
@Description: Used to refresh an element display by hiding it and reshowing to fix reflow problem in IE
@Example:

//return the elemenet
myElement.redraw();
*/
Element.prototype.redraw = function(){
	var d = this.style.display;
	this.style.display = 'none';
	this.style.display = d;
	return this;
};