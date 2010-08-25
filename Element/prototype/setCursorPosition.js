/**
@Name: Element.prototype.setCursorPosition
@Description: Sets the cursor position within a textarea
@Example:
myElement.setCursorPosition

*/
Element.prototype.setCursorPosition = function(start,end){
	end = end || start;
	if(this.setSelectionRange){
		this.setSelectionRange(start, end);
	} else if(this.createTextRange){
		var range = this.createTextRange();
		range.collapse(true);
		range.moveEnd('character', start);
		range.moveStart('character', end);
		range.select();
	}
};