/*
@Name: sb.element.loading
@Author: Paul Visco
@Description: creates a blinking node "loading.." that can be appended anywhere and blink during load or other action
myLoading = new sb.loading();
myLoading.appendTo('body');
myLoading.start();
myLoading.stop();
*/

sb.element.loading = function(){
	
	var el = new sb.element({
		tag : 'span',
		className : 'loading',
		innerHTML : 'loading...',
		
		blink : function(){
			this.style.visibility = this.style.visibility === "" ? "hidden" : "";
		},
		
		start : function(){
			var t=this;
			
			this.blinker = window.setInterval(function(){
				t.blink();
			}, 300);
		},
		
		stop : function(){
			
			if(typeof this.blinker !='undefined'){
				window.clearInterval(this.blinker);
				this.remove();
			}
		}
	});
	
	return el;
	
};