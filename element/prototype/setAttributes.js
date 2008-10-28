/**
@Name: sb.element.prototype.addClassName
@Description: Adds a className to the sb.element, using this methods sb.element instances can have multiple classNames
@Param: String c The classname to add
@Return: returns itself
@Example:
myElement.setAttributes({friend : 'tim', name : 'joe'});
<myElement friend="tim" name="joe">
*/
sb.element.prototype.setAttributes = function(o){
	var t=this;
	sb.objects.forEach.call(o, function(val,prop,o){
		t.setAttribute(prop, val);
	});
	return this;
};
Element.prototype.setAttributes = sb.element.prototype.setAttributes;