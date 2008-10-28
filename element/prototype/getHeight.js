sb.include('element.prototype.getDimensions');

/**
@Name: sb.element.prototype.getHeight
@Description: Gets the height of an element
@Return: returns the elements height as a number in pixels with no unit
@Example:
	var height = myElement.getHeight();
	
*/
sb.element.prototype.getHeight = function(){
	return this.getDimensions(this).height;
};

Element.prototype.getHeight = sb.element.prototype.getHeight;