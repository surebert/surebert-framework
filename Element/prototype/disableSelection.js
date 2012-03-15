/**
@Name: Element.prototype.disableSelection
@Description: disables text selection for this element
@Example:
myElement.disableSelection();
*/
Element.prototype.disableSelection = function(){
	
    this.onselectstart = function() {
        return false;
    };
    this.unselectable = "on";
    this.style.MozUserSelect = "none";
	
	return this;
};