/**
@Name: Element.prototype.hide
@Description: Sets the display of the element to none, removing its from being displayed on the page
@Return: returns itself
@Example:
myElement.hide();
*/
Element.prototype.hide = function(){
	this.style.display = 'none';
	return this;
};