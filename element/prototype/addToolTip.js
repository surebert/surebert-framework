/**
@Name:  sb.element.prototype.addToolTip
@Author: Paul Visco
@Version: 1.0 11/16/07
@Description: Adds a tooltip capability to any DOM element
@Example: 
//simple
sbElement.addToolTip({
	tip : 'Here is a tooltip'
});

//more complex
sbElement.addToolTip({
	tip : '<a href="#" title="close">close</a><br />Use this form to lookup users by name.  It requires you input at least three characters.<br />You can even use regular expressions<ol><li><i>pa.l</i> would find paul and paulnotpaul<li><li><i>pa.l$</i> would find paul only<li><li><i>p..</i> would find everyone whose name started with p<li></ol>',
	additionalClassName : 'jump_menu',
	persistent : 1,
	yOffset : -160,
	xOffset : -210
});

*/
sb.element.prototype.addToolTip = function(o){
	o = o || {};
	if(!this.sb_tooltip){
		
		this.sb_tooltip = new sb.element({
			tag : 'div',
			className : o.className || 'sb_tooltip',
			innerHTML : o.tip,
			aboutToShow : 0,
			showing : 0,
			delay : o.delay || 125,
			yOffset : o.yOffset || 20,
			xOffset : o.xOffset || 20,
			persistent : o.persistent || 0,
			additionalClassName : o.additionalClassName || '',
			close : function(){
				this.hide();
				this.showing =0;
				this.aboutToShow =0;
				if(this.timeout){
					window.clearTimeout(this.timeout);
				}
			}
		});
		
		if(this.sb_tooltip.additionalClassName !==''){
			this.sb_tooltip.addClassName(this.sb_tooltip.additionalClassName);
		}
		
		if(this.sb_tooltip.persistent == 1){
			
			this.sb_tooltip.events({
				mousedown : function(e){
					var target = sb.events.target(e);
					
					if(target.nodeName == 'A' && target.title=='close'){
						this.close();
					}
				}
			});
		}
		
		this.sb_tooltip.appendTo('body');
		
		this.sb_tooltip.hide();
	} 
	var self = this;
	
	this.events({
		mouseover : function(e){
			var x = self.getX();
			var y = self.getY();
			
			if(self.sb_tooltip.aboutToShow === 0 && self.sb_tooltip.showing === 0){
				
				self.sb_tooltip.aboutToShow = 1;
				self.sb_tooltip.timeout = window.setTimeout(function(){
					
					var height = self.sb_tooltip.getHeight();
					var tipY = y-(height+self.sb_tooltip.yOffset);
					var tipX = x+self.sb_tooltip.xOffset;
					if(tipY < 0){ tipY += tipY*-1;}
					if(tipX < 0){ tipX += tipX*-1;}
					
					self.sb_tooltip.mv(tipX, tipY, 999);
					self.sb_tooltip.show();
					
					self.sb_tooltip.showing = 1;
				}, self.sb_tooltip.delay);
			}
			
			
			
		},
		mouseout : function(){
			if(this.sb_tooltip.persistent === 0){
				this.sb_tooltip.close();
			}
			
		}
	});
	
	return this.sb_tooltip;
	
};