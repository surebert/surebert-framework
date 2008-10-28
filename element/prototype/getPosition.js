sb.include('element.prototype.getDimensions');
sb.include('sb.browser.getScrollPosition');

/**
@Name: sb.element.prototype.getPosition
@Description: Used to calculate the bounds top, right, bottom, left, h, and w of a DOM node.  h and w are height and width.  Attaches those properties to the element itself
@Return: Object An object with top, right, bottom, left, h, and w properties
@Return: returns itself
@Example:
var pos = myElement.getPosition();
alert(pos.left);
myElement.getPosition({pos : 'rel'});
myElement.getPosition({accountForScroll : 1});
*/

sb.element.prototype.getPosition = function(params){
	params = params || {};
	var orig = this;

	var el=this;
	
	orig.top =0;
	orig.left =0;
	
	do{
		orig.top += el.offsetTop;
		orig.left += el.offsetLeft;
		
		if(params.pos =='rel'){
			el = false;
		} else{
			try{el = el.offsetParent;}catch(e){el = false;}
			
		}
	} while(el);
	
	if(params.accountForScrollBar){
		sb.browser.getScrollPosition();
		if(sb.browser.scrollY){
			orig.top -=sb.browser.scrollY;
		}
		
		if(sb.browser.scrollX){
			orig.left -=sb.browser.scrollX;
		}
	}
	
	orig.getDimensions();
	
	//alias for w and h
	orig.w = orig.width;
	orig.h = orig.height;
	orig.bottom = orig.top+orig.width;
	orig.right = orig.left+orig.height;
	
	return orig;	
};

Element.prototype.getPosition = sb.element.prototype.getPosition;