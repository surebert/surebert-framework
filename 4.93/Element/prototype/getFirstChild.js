/**
@Name: Element.prototype.getFirstChild
@Description: returns the first element type node (nodeType ==1) of a parentNode
@Return: element sb.element

@Example:
//get the nodes firstChild
myParentSbElement.getFirstChild();

*/
Element.getFirstChild = function(){
	var node = this.firstChild;
	while (node && node.nodeType && node.nodeType == 3) {
		node = node.nextSibling;
	}
	return sb.$(node);
};