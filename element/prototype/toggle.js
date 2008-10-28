/**
@Name: sb.element.prototype.toggle
@Description: Switches an object's display between hidden and default
@Return: returns itself
@Example:
myElement.toggle();
*/
sb.element.prototype.toggle = function(){
	if(this.style){
		this.style.display = (this.getStyle('display') ==='none') ? '' : 'none';
	}
	return this;
};

Element.prototype.toggle = sb.element.prototype.toggle;