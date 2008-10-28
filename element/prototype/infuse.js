/**
@Name: sb.element.prototype.infuse
@Description: Used to infuse an sb.element instance
@Example:

//adds a property called name which is set to tim, and add a  property called type with a value of text
myElement.infuse({name : 'tim', type : 'text'});
*/
sb.element.prototype.infuse =  function(o){
	sb.objects.infuse(o, this);
	
	return this;
};

Element.prototype.infuse = sb.element.prototype.infuse;