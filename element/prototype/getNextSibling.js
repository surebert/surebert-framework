/**
@Name: sb.element.prototype.getNextSibling
@Description: Finds the next sibling element of the element on which this is called
@Return: Element A DOM element reference to the next sibling

@Example:
myElement.getNextSibling();
*/
sb.element.prototype.getNextSibling = function(){
	var node = this;
	while((node = node.nextSibling) && node.nodeType != 1){}
	return sb.s$(node);
};