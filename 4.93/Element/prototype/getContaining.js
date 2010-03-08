/**
@Name: Element.prototype.getContaining
@Description: Searches parentNodes incrementally till it find a node of a particular nodeName
@Param: string nodeName The containing nodeName to look for
@Return: sb.element The parent node of the nodeName type specified or false

@Example:
//get the containing div
mySbElement.getContaining('div');

*/
Element.prototype.getContaining = function(nodeName){

	var ret = false;
	var parent = this;

	while(parent = sb.$(parent.parentNode)){
		if(parent.nodeName && parent.nodeName == nodeName.toUpperCase()){

			return parent;
		}
	}
	return ret;
};