/**
@Name: sb.element.prototype.hide
@Description: Sets the display of the element to none, removing its from being displayed on the page
@Return: returns itself
@Example:
myElement.hide();
*/
sb.element.prototype.hide = function(){
	this.style.display = 'none';
	return this;
};

Element.prototype.hide = sb.element.prototype.hide;