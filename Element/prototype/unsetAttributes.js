/**
@Name: Element.prototype.unsetAttributes
@Description: Unsets the attributes of the element that are in the argument array
@Param: Array a A list of strings which represent the values to unset
@Example:
myElement.unsetAttributes(['friend', 'nextKin']);
*/
Element.prototype.unsetAttributes = function(a){
	var t=this;
	a.forEach(function(v){
		t.setAttribute(v, '');
	});
	return this;
};