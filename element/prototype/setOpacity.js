/**
@Name: sb.element.prototype.setOpacity
@Description: Sets the opacity of the element
@Param: Float o Optional arguemnt specifies the percentage ocapcity.  0.0 is 100% transparent and 1.0 is 100% opaque. 
@Return: returns itself
@Example:
myElement.setOpacity(0.3);
//sets the elements opacity to 0.3
*/
sb.element.prototype.setOpacity = function(o){
	this.setStyle('opacity', o);
	return this;
};

Element.prototype.setOpacity = sb.element.prototype.setOpacity;