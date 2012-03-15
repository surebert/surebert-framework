sb.include('Element.prototype.getDimensions');

/**
@Name: Element.prototype.getWidth
@Description: Gets the width of an element
@Return: returns the elements width as a number in pixels with no unit
@Example:
	var height = myElement.getWidth();
	
*/
Element.prototype.getWidth = function(){
	return this.getDimensions(this).width;
};