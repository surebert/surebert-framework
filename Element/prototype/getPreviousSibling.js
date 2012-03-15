/**
@Name: Element.prototype.getPreviousSibling
@Description: Finds the previous sibling element of the element on which this is called
@Return: Element A DOM element reference to the previous sibling

@Example:
myElement.getPreviousSibling();
*/
Element.prototype.getPreviousSibling = function(){
	var node = this;
	while((node = node.previousSibling) && node.nodeType != 1){}
	return sb.$(node);
};