
/**
@Package: surebert.effects
@Description: If you are going to be using color effects make sure to include sb.colors.js by adding a script tag or using sb.include or adding it to your surebert cache
@Version: v3.11
*/

/**
@Name: sb.effect
@Type: constructor
@Description: Used to create time based style effects
@Params: object params
	.el = The element to work with
	.begin = the beginning value
	.change = the change from the beginning value.  E.g. if the begin value is 10 and you want it to interate through to 20, change would be 10.  If you wanted it to interate through to 0, change would be -10
	.duration = the duration to use, 2 is the default, higher is slower, lower is faster.
	.onChange = the function that run for each iteration.  It has reference to all of the "this" properties of the effect in addition to this.value which is the current value based on begin and change
	.onStop = a function which fires when the effect is complete
	.onEnd = a function which fires when the effect is stopped
	
@Example: 
//set the DOM el we want to work with
var myDiv = $('#accord');
//create an custom effect
var myEffect = new sb.effect({
	el : myDiv,
	begin : 10,
	change : 12,
	duration : 120,
	onChange : function(c){
		this.el.style.fontSize = this.value+'px';
	},
	onEnd : function(){
		//do something
	},
	onStop : function(){
		//do soemthing
	}
});
myEffect.start();
*/
sb.effect = function(params){
	
	this.setParams(params ||{});
	
	sb.effects.register(this);
	
};

/**
@Name: sb.effect.prototype
@Type: prototype
@Description:  prototype methods and properties for a sb.effect instance
*/
sb.effect.prototype = {
	
	/**
	@Name: sb.effect.time
	@Type: integer
	@Description: used internally
	*/
	time : 0,
	
	/**
	@Name: sb.effect.duration
	@Type: integer
	@Description: The duration of teh effect from begin to end.  Default is 24.  Lower numbers are faster, higher numbers are slower.
	*/
	duration : 24, 
	
	/**
	@Name: sb.effect.count
	@Type: integer
	@Description: The number of times the effect has iterated so far between begin and end.  You have reference to this in your effect onChange as this.count
	*/
	count : 0,
	
	/**
	@Name: sb.effect.tween
	@Type: string
	@Description: The type of tweening used, see sb.tween
	*/
	tween : 'inQuart',
	
	/**
	@Name: sb.effect.setParams()
	@Type: method
	@Description: applies properties from an additional params object with the same structure as the one used in the constructor
	@Example:
	myEffect.setParams();
	*/
	setParams : function(params){
		sb.objects.infuse(params, this);
		if(typeof params.el !== 'undefined'){
			this.el = sb.$(this.el);
		} 
		
	},
	
	/**
	@Name: sb.effect.start()
	@Type: method
	@Description: starts the effect to run the effect
	@Example:
	myEffect.start();
	*/
	start : function(){
		
		var t=this;
	
		this.repeater = window.setInterval(
		function(){
			
			if(t.time < t.duration){
				
				t.time++;
				t.value = sb.effects.tween[t.tween](t.time,t.begin,t.change,t.duration);
				t.valueRounded = Math.round(t.value);
			
				t.onChange();
				
			} else {
				
				t.count++;
				t.stop();
				if(typeof t.onEnd === 'function'){t.onEnd();}
				
			}
		}, 12);
		
	},

	/**
	@Name: sb.effect.stop()
	@Type: method
	@Description: stops the effect from running. Also fires the effects onStop method if you have specified one
	@Example:
	myEffect.stop();
	*/
	stop : function(){
		if(typeof this.onStop === 'function'){this.onStop();}
		window.clearInterval(this.repeater);
	},
	
	/**
	@Name: sb.effect.reset()
	@Type: method
	@Description: reset the effect to count and time to 0 and resets value and valueRounded to this.begin an. Also fires the effects onReset method if you have pecified one
	@Example:
	myEffect.reset();
	*/
	reset : function(){
		if(typeof this.onReset === 'function'){this.onReset();}
		this.count=0;
		this.value = this.begin;
		this.valueRounded = this.begin;
		this.time=0;
		this.stop();
	},
	
	/**
	@Name: sb.effect.restart()
	@Type: method
	@Description: restarts the effect.  run this.reset() and this.start()
	@Example:
	myEffect.restart();
	*/
	restart : function(){
		if(typeof this.onRestart === 'function'){this.onRestart();}
		this.reset();
		this.start();
	}
};


/**
@Name: sb.effects
@Type: object
@Description: A reference to all effects registered ont he page
*/
sb.effects = {
	
	/**
	@Name: sb.registered
	@Type: array
	@Description: Used Internally. A array reference to all effects registered on the page
	*/
	registered : [],
	
	/**
	@Name: sb.register()
	@Type: method
	@Description: Used internally. Registers an effect on the page
	*/
	register : function(effect){
		this.registered.push(effect);
	},
	
	/**
	@Name: sb.stopAll()
	@Type: method
	@Description: Stops all the effects on the page
	*/
	stopAll : function(){
		sb.effects.registered.forEach(function(v){v.stop();});
	},
	
	/**
	@Name: sb.startAll()
	@Type: method
	@Description: Starts all the effects on the page
	*/
	startAll : function(){
		sb.effects.registered.forEach(function(v){v.start();});
	}
};

/**
@Name: surebert.effects.tween
@Type: object
@Description: If you are going to be using color effects make sure to include sb.colors.js by adding a script tag or using sb.include('colors.getTweenColor'); or adding it to your surebert cache .Tweens Math adapted from http://www.synthesisters.com/hypermail/max-msp/Nov05/34305.html
*/
sb.effects.tween = {

	/**
	@Name: surebert.effects.tween.linear
	@Type: method
	@Description: A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.linear(100, 0, 50, 5);
	*/
	linear : function(t,b,c,d) {
	    return c*t/d + b;
	},
	
	/**
	@Name: surebert.effects.tween.outQuint
	@Type: method
	@Description: A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.outQuint(100, 0, 50, 5);
	*/
	outQuint : function(t,b,c,d) {
	    return c*(Math.pow(t/d-1,5)+1)+b;
	},
	
	/**
	@Name: surebert.effects.tween.inOutQuint
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inOutQuint(100, 0, 50, 5);
	*/
	inOutQuint : function(t,b,c,d) {
	    if ((t/=d/2)<1){
	        return c/2*Math.pow(t,5)+b;
	    }
	    return c/2*(Math.pow(t-2,5)+2)+b;
	},
	
	/**
	@Name: surebert.effects.tween.inQuad
	@Type: method
	@Description: A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inQuad(100, 0, 50, 5);
	*/
	inQuad : function(t,b,c,d) {
	    return c*(t/=d)*t + b;
	},
	
	/**
	@Name: surebert.effects.tween.outQuad
	@Type: method
	@Description: A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.outQuad(100, 0, 50, 5);
	*/
	outQuad : function(t,b,c,d) {
	    return -c*(t/=d)*(t-2) + b;
	},
	
	/**
	@Name: surebert.effects.tween.inOutQuad
	@Type: method
	@Description: A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inOutQuad(100, 0, 50, 5);
	*/
	inOutQuad : function(t,b,c,d) {
	    if ((t/=d/2) < 1) {return c/2*t*t+b;}
		return -c/2 * ((--t)*(t-2)-1)+b;
	},
	
	/**
	@Name: surebert.effects.tween.inCubic
	@Type: method
	@Description: A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inCubic(100, 0, 50, 5);
	*/
	inCubic : function(t,b,c,d) {
	    return c*Math.pow(t/d,3)+b;
	},
	
	/**
	@Name: surebert.effects.tween.outCubic
	@Type: method
	@Description: A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.outCubic(100, 0, 50, 5);
	*/
	outCubic : function(t,b,c,d) {
	    return c*(Math.pow(t/d-1,3)+1)+b;
	},
	
	/**
	@Name: surebert.effects.tween.inOutCubic
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inOutCubic(100, 0, 50, 5);
	*/
	inOutCubic : function(t,b,c,d) {
	    if ((t/=d/2)<1){
	        return c/2*Math.pow(t,3)+b;
	    }
	    return c/2*(Math.pow(t-2,3)+2)+b;
	},
	
	/**
	@Name: surebert.effects.tween.inQuart
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inQuart(100, 0, 50, 5);
	*/
	inQuart : function(t,b,c,d) {
	    return c* Math.pow(t/d,4) + b;
	},
	
	/**
	@Name: surebert.effects.tween.outQuart
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.outQuart(100, 0, 50, 5);
	*/
	outQuart : function(t,b,c,d) {
	    return -c*(Math.pow(t/d-1,4)-1)+b;
	},
	
	/**
	@Name: surebert.effects.tween.inOutQuart
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inOutQuart(100, 0, 50, 5);
	*/
	inOutQuart : function(t,b,c,d) {
		if ((t/=d/2)<1){
		    return c/2*Math.pow(t,4)+b;
		}
		return -c/2*(Math.pow(t-2,4)-2)+b;
	},
	
	/**
	@Name: surebert.effects.tween.inSine
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inSine(100, 0, 50, 5);
	*/
	inSine : function(t,b,c,d) {
	    return c* (1 - Math.cos(t/d*(Math.PI/2))) + b;
	},
	
	/**
	@Name: surebert.effects.tween.outSine
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.outSine(100, 0, 50, 5);
	*/
	outSine : function(t,b,c,d) {
	    return c* Math.sin(t/d*(Math.PI/2)) + b;
	},
	
	/**
	@Name: surebert.effects.tween.inOutSine
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inOutSine(100, 0, 50, 5);
	*/
	inOutSine : function(t,b,c,d) {
	    return c/2* (1-Math.cos(Math.PI*t/d)) +b;
	},
	
	/**
	@Name: surebert.effects.tween.inExpo
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inExpo(100, 0, 50, 5);
	*/
	inExpo : function(t,b,c,d) {
	    return c* Math.pow(2,10*(t/d - 1)) +b;
	},
	
	/**
	@Name: surebert.effects.tween.outExpo
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.outExpo(100, 0, 50, 5);
	*/
	outExpo : function(t,b,c,d) {
	    return c* (-Math.pow(2,-10*t/d) + 1) + b;
	},
	
	/**
	@Name: surebert.effects.tween.inOutExpo
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inOutExpo(100, 0, 50, 5);
	*/
	inOutExpo : function(t,b,c,d) {
	    if ((t/=d/2) < 1){
	        return c/2 * Math.pow(2, 10*(t-1))+b;
	    }
	    return c/2 * (-Math.pow(2, -10 * --t) + 2) +b;
	},
	
	/**
	@Name: surebert.effects.tween.inCirc
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inCirc(100, 0, 50, 5);
	*/
	inCirc : function(t,b,c,d) {
	    return c* (1-Math.sqrt(1- (t/=d)*t)) + b;
	},
	
	/**
	@Name: surebert.effects.tween.outCirc
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.outCirc(100, 0, 50, 5);
	*/
	outCirc : function(t,b,c,d) {
	    return c* Math.sqrt(1-(t=t/d-1)*t) + b;
	},
	
	/**
	@Name: surebert.effects.tween.inOutCirc
	@Type: method
	@Description:  A method for interpolating between two values over time
	@Param float t time (elapsed since tween began)
	@Param float b beginning (value)
	@Param float b change (in value)
	@Param float d duration
	@Example:
	var myValue = sb.effects.tween.inOutCirc(100, 0, 50, 5);
	*/
	inOutCirc : function(t,b,c,d) {
		if ((t/=d/2) < 1){
			return c/2 * (1-Math.sqrt(1-t*t))+b;
		}
	    return c/2 * (Math.sqrt(1- (t-=2)*t)+1) +b;
	},
	
	/**
	@Name: surebert.effects.infuse
	@Type: method
	
	@Example:
	var myValue = sb.effects.tween.infuse({
		inOutCirc : function(t,b,c,d) {
			if ((t/=d/2) < 1){
				return c/2 * (1-Math.sqrt(1-t*t))+b;
			}
		    return c/2 * (Math.sqrt(1- (t-=2)*t)+1) +b;
		}
	});
	*/
	infuse : sb.objects.infuse
};