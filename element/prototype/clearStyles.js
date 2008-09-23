/**
@Name: sb.element.prototype.clearStyles
@Description: Clears any styles set for the DOM element by javascript
@Example:
myElement.style.height = '10px';
myElement.clearStyles();
//height would be '';

*/
sb.element.prototype.clearStyles = function(){
	for(var style in this.style){
		if(!sb.objects[style]){
			try{
				this.style[style] = '';
			}catch(e){}
		}
	}
	return this;
};