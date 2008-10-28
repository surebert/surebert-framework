/**
@Name: sb.element.prototype.clearPosition
@Description: Clears any position data set with javascript
@Example:
myElement.style.position = 'absolute';
myElement.style.left = '10px';
myElement.clearPosition();
//both properties would be ''

*/
sb.element.prototype.clearPosition = function(){
	
	this.styles({
		position : '',
		zIndex: '',
		left : '',
		top: ''
	});	
};

Element.prototype.clearPosition = sb.element.prototype.clearPosition;