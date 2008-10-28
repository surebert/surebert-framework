sb.include('effect');
/**
@Name: sb.element.prototype.cssTransition()
@Type: constructor
@Description: Easily handles a multitude of css transitions for S$ elements and sb.elements.  This is what most people will probably use for all effects they create.
@Example: 
sb.include('colors.getTweenColor');
var body = s$('body');
var transitions = body.cssTransition([
	
	{
		prop : 'backgroundColor',
		begin : 'FF0000',
		end : '00FF00',
		onEnd : function(){
			//alert('done');
		}
		
	},
	
	{
		prop : 'fontSize',
		begin : 10,
		change : 40,
		
		duration : 48 //this is optional it defualts to the global duration (constructor's second argument or 24 if not neither is defined)
	}
	
], 120);

transitions.start();
*/
sb.element.prototype.cssTransition = function(changes, duration){
	var el = this;
	
	var transitions = {
		/**
		@Name: sb.element.prototype.cssTransition.effects()
		@Type: array
		@Description: an array of all effects in the cssTransition
		*/
		effects : [],
		
		/**
		@Name: sb.element.prototype.cssTransition.start()
		@Type: method
		@Description: starts the transition
		@Example:
		myTransition.start();
		*/
		start : function(){
			this.effects.forEach(function(effect){
				effect.start();
			});
		},
		
		/**
		@Name: sb.element.prototype.cssTransition.stop()
		@Type: method
		@Description: stops the transition
		@Example:
		myTransition.stop();
		*/
		stop : function(){
			this.effects.forEach(function(effect){
				effect.stop();
			});
		},
		
		/**
		@Name: sb.element.prototype.cssTransition.reset()
		@Type: method
		@Description: reset the transition
		@Example:
		myTransition.reset();
		*/
		reset : function(){
			this.effects.forEach(function(effect){
				effect.reset();
			});
		},
		
		/**
		@Name: sb.element.prototype.cssTransition.restart()
		@Type: method
		@Description: reset the transition
		@Example:
		myTransition.restart();
		*/
		restart : function(){
			this.effects.forEach(function(effect){
				effect.restart();
			});
		}
		
	};
	
	changes.forEach(function(change){
		var effect =  new sb.effect({
			el : el
		});
		
		effect.prop = change.prop;
		effect.unit = change.unit ||'';
		
		effect.duration = change.duration || duration || 24;
		effect.type = change.type || 'outQuad';
		if(change.prop =='backgroundColor' || change.prop =='color'){
			effect.beginColor = change.begin;
			effect.endColor = change.end;
			
			effect.begin =  0;
			effect.change = 100;
			effect.end = change.end;
		} else {
			effect.begin =  change.begin;
			effect.change = change.change;
		}
		effect.onTween = change.onTween || 0;
		effect.onEnd = change.onEnd || 0;
		if(typeof change.handler =='function'){
			
			effect.handler = function(){
				change.handler.call(effect);
			};
		} else {
			effect.handler = function(){
			
				//fix stupid IE height 0px prob
				if (this.prop =='height' && sb.browser.ie6 && this.value<1){
					
					this.value=1;
				}
				
				if(this.prop =='backgroundColor' || this.prop =='color'){
					sb.include('colors.getTweenColor');
					this.el.style[change.prop] = sb.colors.getTweenColor(this.beginColor, this.endColor, this.value);
					
				
				} else  {
					try{
					this.el.setStyle(change.prop, String(this.value.toFixed(2))+this.unit);
					} catch(e){
					//	alert(this.prop);
					}
				} 
			};
			
		}
		
		transitions.effects.push(effect);
	});
	
	
	return transitions;
};

Element.prototype.cssTransition = sb.element.prototype.cssTransition;