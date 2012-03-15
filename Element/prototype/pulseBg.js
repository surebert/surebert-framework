Element.prototype.pulseBg = function(beginColor, endColor, timesToPulse, duration){
	duration = duration || 80;
	var t = this;
	var f = 0;
	function pulse(){

		t.cssTransition([
			{
				prop : 'backgroundColor',
				begin : beginColor,
				end : endColor,
				onEnd : function(){
					if(f < timesToPulse){
						pulse();
						f++;
					} else {
						pulse = null;
					}
				}

			}
		], duration).start();

	}
	pulse();
};