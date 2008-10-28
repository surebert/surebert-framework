sb.include('effect');
sb.include('element.prototype.cssTransition');
sb.include('strings.toNumber');

/**
@Name:  sb.element.prototype.resizeTo
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: This effect resizes an element dynamically
@Param: integer width The width to resize to
@Param: integer height The height to resize to
@Param: integer duration The time milliseconds to fade over
Example: div.resizeTo({height:700, width:700});
*/
sb.element.prototype.resizeTo = function(o){
	
	
	var border = this.getStyle('border').toNumber();
	var padding = this.getStyle('padding').toNumber();
	this.style.overflow='hidden';
	
	var transitions = [];
	
	if(o.width !== undefined){
		var width = this.offsetWidth;
		transitions.push({
			prop : 'width',
			begin : width,
			change : o.width-width,
			onEnd : o.onWidthChanged || 0
		});
	}
	
	if(o.height !== undefined){
		
		var height = this.offsetHeight;
		
		transitions.push({
			prop : 'height',
			begin : height,
			change : o.height-height,
			onEnd : o.onHeightChanged || 0
		});
	}
	
	if(this.resizing){
		this.resizing.stop();
	}
	this.resizing = this.cssTransition(transitions, o.duration || 48);
	
	
	this.resizing.start();
	return this.resizing;
	
};

Element.prototype.resizeTo = sb.element.prototype.resizeTo;