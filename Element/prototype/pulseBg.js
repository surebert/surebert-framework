Element.prototype.pulseBg = function(beginColor, endColor, timesToPulse){
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
			], 150).start();

		}
		pulse();
};