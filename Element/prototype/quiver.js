sb.include('math.rand');

/**
@Name: Element.prototype.quiver
@Author: Paul Visco 
@Version: 1.0 11/16/07
@Description: Make it so that an element quiver and shakes as though it is scared.
@Example:

sbElement.quiver();
sbElement.quiver().stop();
*/

Element.prototype.quiver = function(params){

	params = params || {};
	
	var distance = params.distance || 5;
	var self = this;
	
	if(!this.isQuivering){
		this.isQuivering =1;
		
		var x = this.getX();
		var y = this.getY();
		
		var z = this.style.zIndex || 999;
		
		this.interval = window.setInterval(function(){
			
				var left = sb.math.rand(0,distance);
				var top = sb.math.rand(0,distance);
				var position = (self.style.position =='absolute') ? 'absolute' : 'relative';
				left += (position =='absolute') ? x : 0;
				top += (position =='absolute') ? y : 0;
				
				self.styles({
					left : left+'px',
					top : top+'px',
					z : z,
					position : position
				});
		}, 10);
	}
	
	return {
		stop : function(){
	
			if(self.isQuivering){
				window.clearTimeout(self.interval);
				self.isQuivering=0;
			}
		}
	};
};