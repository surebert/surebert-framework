/**
@Name: String.prototype.toElement
@Description: Converts a string of HTML code to a sb.element for dom manipulation
@Param: String parentNodeType The nodetype of the parent element returned if there is not already a single parent element for all elements contained in the html string - see example two - defaults to span if it is not given
@Example:
//would return div as the element with all its children
sb.dom.HTMLToElement('<div id="joe"><p class="test">hey there</p></div>');
//would return all elements grouped under a span because they have no comment parent
sb.dom.HTMLToElement('<p class="test">hey there</p><p class="test2">hey there2</p>');
*/
String.prototype.toElement = function(parentNodeType){
	parentNodeType = parentNodeType || 'span';
	
	var temp = new sb.element({
		nodeName : parentNodeType,
		innerHTML : this
	});
	
	if(temp.childNodes.length > 1){
		return $(temp);
	} else {
		return $(temp.firstChild);	
	}
};

