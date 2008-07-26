/**
@Name: sb.functions.fireRepeatedly
@Description: Fires a function repeatedly on a timeout expressed in milliseconds or seconds
@Param: Object o Timeout can be in either seconds or milliseconds
milliseconds - the time between firing in milliseconds
seconds - The time between firing in seconds
@Return: Object Returns an object that has a stop method to end the repeated firing
@Example:

sb.functions.fireRepeatedly.call(hello,1);
*/
sb.functions.fireRepeatedly = function(o){
	var milliseconds =1000;
	
	if(typeof o == 'object'){
		milliseconds = o.milliseconds || milliseconds;
		if(o.seconds){
			milliseconds = o.seconds*1000;
		}
	} else if(typeof o == 'number'){
		milliseconds = o;
	}
	
	var evt = window.setInterval(this, milliseconds);
 	return {
 		abort : function(){window.clearInterval(evt);}
 	};
};