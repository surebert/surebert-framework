sb.include('Element.prototype.getDimensions');

/**
@Name: Element.prototype.getHeight
@Description: Gets the height of an element
@Return: returns the elements height as a number in pixels with no unit
@Example:
	var height = myElement.getHeight();
	
*/
Element.prototype.getHeight = function(){
	return this.getDimensions(this).height;
};