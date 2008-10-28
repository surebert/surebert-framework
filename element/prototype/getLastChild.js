/**
@Name: sb.element.prototype.getLastChild
@Description: returns the last element type node (nodeType ==1) of a parentNode
@Return: element sb.element

@Example:
////get the nodes lastChild
myParentSbElement.getLastChild();

*/
sb.element.prototype.getLastChild = function(){
	
	var node = this.lastChild;
	while (node && node.nodeType && node.nodeType == 3) {
		node = node.previousSibling;
	}
	return s$(node);
};