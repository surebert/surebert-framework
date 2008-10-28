/**
@Name: sb.element.prototype.unsetAttributes
@Description: Unsets the attributes of the element that are in the argument array
@Param: Array a A list of strings which represent the values to unset
@Example:
myElement.unsetAttributes(['friend', 'nextKin']);
*/
sb.element.prototype.unsetAttributes = function(a){
	var t=this;
	a.forEach(function(v){
		t.setAttribute(v, '');
	});
	return this;
};

Element.prototype.unsetAttributes = sb.element.prototype.unsetAttributes;