/**
@Name: Element.prototype.getContaining
@Description: Searches parentNodes incrementally till it find a node of a particular nodeName
@Param: string nodeName The containing nodeName to look for
@Return: sb.element The parent node of the nodeName type specified or false

@Example:
//get all ancestors
mySbElement.getAncestors();
//get all ancestors within (up to) myOtherDiv
mySbElement.getAncestors(myOtherSbElement);

*/
Element.prototype.getContaining = function(nodeName){

	var ret = false;
	var parent = this;

	while(parent = $(parent.parentNode)){
		if(parent.nodeName && parent.nodeName == nodeName.toUpperCase()){

			return parent;
		}
	}
	return ret;
};