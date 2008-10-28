/**
@Name: sb.element.prototype.css
@Description: used to get the CSS/styles of an object, You can pass it either css style dash syntax of javascript camelBack e.g. background-color or backgroundColor.
@Param: String prop The property to set or get.
@Param: String val The value to set the property to.  If not set the function returns the value currently set
@Example:
//would set the background color
myElement.css('background-color', 'red');

myElement.css('background-color');
//would return red
*/

sb.element.prototype.css = function(prop, val){
	
	if(val){
		return this.setStyle(prop, val);
	} else {
		return this.getStyle(prop);
	}
	
};

Element.prototype.css = sb.element.prototype.css;