/**
@Name: Element.prototype.wh
@Description: Sets the width and height of the element
@Param: String/Number w The element width desired, can be specified as a number e.g. 100 or as a percent '100%'
@Param: String/Number h The element height desired, can be specified as a number e.g. 100 or as a percent '20%'
@Example: 
myElement.wh(100, 200);
*/
Element.prototype.wh = function(w,h){
	this.style.width = w+'px';
	this.style.height = h+'px';
	return this;
};