/**
@Name: Element.prototype.clearPosition
@Description: Clears any position data set with javascript
@Example:
myElement.style.position = 'absolute';
myElement.style.left = '10px';
myElement.clearPosition();
//both properties would be ''

*/
Element.prototype.clearPosition = function(){
	
	this.styles({
		position : '',
		zIndex: '',
		left : '',
		top: ''
	});	
};