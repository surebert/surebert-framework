sb.include('math.rand');
sb.include('colors.rand');
sb.include('Element.prototype.disableSelection');

/**
@Name: sb.widget.colorRandomizer
@Author: Paul Visco 07-06-2008
@Description: Used to create a palette of random color swatches. 
@Param: Object An optional params argument who properties are applied to the instance you are creating
@Example:
var y = new sb.widget.colorRandomizer({
	numSwatches : 10,
	mouseDown : function(e, self){
		//do something when the swatch is clicked
		if(e.shiftKey){
			self.palette.remove();
			return;
		}
		var target = e.target;
		
		document.body.style.backgroundColor = target.style.backgroundColor;
		
	}
});
y.palette.appendTo('#themes');
*/
sb.widget.colorRandomizer = function(params){
	sb.objects.infuse(params, this);
	this.createDom();
};

sb.widget.colorRandomizer.prototype = {
	numSwatches : 5,
	
	/**
	@Name: sb.widget.colorRandomizer.prototype.cycling
	@Description: Used to hold the reference to window.setInterval instance create by this.cycle();
	@Example:
	window.clearInterval(yourInstance.cycler);
	 */
	cycler : 0,
	
	swatches : [],
	/**
	@Name: sb.widget.colorRandomizer.prototype.palette
	@Description: The sb.element node used to hold the palette.  This is what you would append to to the DOM 
	@Example:
	yourInstance.palette.appendTo('#themes');
	 */
	palette : {},
	
	/**
	@Name: sb.widget.colorRandomizer.prototype.interval
	@Description: The amount of milliseconds between cycling the colors
	@Example:
	 */
	interval : 300,
	
	/**
	@Name: sb.widget.colorRandomizer.prototype.createDom
	@Description: Used Internally - creates the DOM
	 */
	createDom : function(el){
		this.palette = new sb.element({
			tag : 'div',
			id : 'sb_color_randomizer',
			events : this.events
		});
		
		for(var x=0;x<this.numSwatches;x++){
			this.swatches.push(new sb.element({
				tag : 'button',
				styles : {
					backgroundColor : sb.colors.rand()
				}
			}).appendTo(this.palette));
		}
		
	},
	
	/**
	@Name: sb.widget.colorRandomizer.prototype.start
	@Description: Starts the cycling through the colors
	 */
	start : function(){
		if (this.cycler) {
			return true;
		}
		
		var swatches = this.swatches;
		this.cycler = window.setInterval(function(){
		
			for(var x=swatches.length-1;x>0;x--){
				swatches[x].style.backgroundColor = swatches[x-1].style.backgroundColor;
			}
			
			swatches[0].style.backgroundColor = sb.colors.rand();
			
		}, this.interval);
		
	},
	
	stop : function(){
		window.clearInterval(this.cycler);
		this.cycler = 0;
	}
};