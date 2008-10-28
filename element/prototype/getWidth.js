sb.include('element.prototype.getDimensions');

/**
@Name: sb.element.prototype.getWidth
@Description: Gets the width of an element
@Return: returns the elements width as a number in pixels with no unit
@Example:
	var height = myElement.getWidth();
	
*/
sb.element.prototype.getWidth = function(){
	return this.getDimensions(this).width;
};

Element.prototype.getWidth = sb.element.prototype.getWidth;