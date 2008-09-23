/**
@Name: sb.element.prototype.disableSelection
@Description: disables text selection for this element
@Example:
myElement.disableSelection();
*/
sb.element.prototype.disableSelection = function(){
	
    this.onselectstart = function() {
        return false;
    };
    this.unselectable = "on";
    this.style.MozUserSelect = "none";
	
	return this;
};