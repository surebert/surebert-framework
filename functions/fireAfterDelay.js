/**
@Name: sb.functions.fireAfterDelay
@Description: Fires a function after waiting the specified number of seconds or milliseconds.  You can run this on any function if globals are not disabled.  If they are you would need to use the call method demostrated below.
@Return: Object A sb.fireAfterDelay instance which has an abort() method 
@Example:
//with globals on
function hello(){
	alert('hello');
}

//fires the function after 10 seconds
var myEvent = hello.fireAfterDelay({seconds : 10});

//this would abort the event before it ran if executed before the ten seconds were up
myEvent.abort();

//with globals off
sb.functions.fireAfterDelay.call(hello,1);
*/
sb.functions.fireAfterDelay = function(o){
	
	o.func = this;
	return new sb.fireAfterDelay(o);
	
};

/**
@Name: sb.fireAfterDelay
@Description: This constructor creates an object that fires a function on a timeout.  The function can be swicthed out at any point before the delay reaches the fires by resetting the .func property of your instance
@Param: Object params An object with the following properties, you must set either the seconds of milliseconds property
seconds - the number of seconds to wait before firing
milliseconds - the number of milliseconds to wait before firing
func - the function to fire after the delay
args - an array of arguments that get passed to the function when it fires
@Example:
var myWaiting= new sb.fireAfterDelay({
	func : function(){
		alert('hello');
	},
	seconds : 2
});
*/
sb.fireAfterDelay = function(o){
	sb.objects.infuse(o, this);
	this.milliseconds = this.milliseconds || 1000;
	if(typeof this.seconds!='undefined'){
		this.milliseconds = this.seconds*1000;
	}
	var t=this,a;
	if(typeof o.args !='undefined'){
		a = (o.args.length) ? o.args : []; 
	}
	this.evt = window.setTimeout(function(){t.func.apply(t.func, a);}, this.milliseconds);
};

sb.fireAfterDelay.prototype = {
		
	/**
	@Name: sb.fireAfterDelay.prototype.abort
	@Description: Stops a sb.delayedFiring object instance from firing if exexuted before the delay has expired.
	@Example:
	var myWaiting= new sb.fireAfterDelay({
		func : function(){
			alert('hello');
		},
		seconds : 2
	});
	//if this is executed before the 2 seconds are up, then the dealyed firing is aborted
	myWaiting.abort();
	*/
	abort : function(){
		window.clearTimeout(this.evt);
	}
};