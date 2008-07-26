sb.include('arrays.iteration');
sb.include('effect');
sb.include('element.prototype.cssTransition');
/**
@Name: sb.flipBook
@Type: constructor
@Description: Loops through a series of images on an interval, each one which open a new site, such as with advertisments
@Param: object params
.interval integer The interval between flips in milliseconds
.images array An array of image objects with src and href properties, see example
.onFlip function A custom function which fires each time during the flip
.onClick function A custom function which fires when the user clicks on the image before the new window loads.  If it returns false, the window doesn't open
.onMouseOver
.onMouseOut 
@Return object An sb.flipbook instance
@Example: 
var flipper = new sb.widget.flipBook({
	images : [
	{ src : 'estrip.jpg', url : 'http://www.estrip.org', alt : 'blog your life'},
	{ src : 'yahoo.jpg', url : 'http://www.yahoo.com', alt : 'yahoo'},
	{ src : 'cnn.jpg', url : 'http://www.cnn.com', alt : 'watch the new'},
	{ src : 'roswellpark.jpg', url : 'http://www.roswellpark.org', alt : 'cure cancer'}]
});

flipper.image.appendTo('#advertisement');

*/
sb.widget.flipBook = function(params){

	this.images = params.images || new sb.nodeList();
	this.interval = params.interval || 3000;
	this.createImage();
	var t= this;
	this.flipper = window.setInterval(function(){
		t.flip();
	}, this.interval);
	
	
};
sb.widget.flipBook.prototype = {
	
	add : function(img){
		this.images.push(img);	
	},
	
	flip : function(){
		this.image.style.cursor='';
		
		if(typeof this.onFlip == 'function'){
			this.onFlip();
		}
		var t=this;
		this.image.cssTransition([{
			prop : 'opacity',
			type: 'outQuart',
			begin : 100,
			change : -100,
			onEnd : function(){
				t.image.cssTransition([{
					prop : 'opacity',
					type: 'inQuart',
					begin : 0,
					change : 100
				}], 48).start();
				
				
				var img = t.images.cycle();
				if(typeof img.alt !='undefined'){
					t.image.alt = img.alt;
					t.image.title = img.alt;
				}
				t.image.src = img.src;
				if(typeof img.url != 'undefined' && img.url !==''){
					
					t.image.style.cursor='pointer';
					t.image.url = img.url;
				}
			}
		}], 48).start();
		
	},
	
	createImage : function(){
		
		this.image = new sb.element({
			tag :'img',
			src : this.images[0].src || '',
			alt : this.images[0].alt || '',
		
			title : this.images[0].title || '',
			flipper : this,
			
			events : {
				click : function(){
					var flip = 1;
					if(typeof this.onFlip == 'function'){
						flip = this.onFlip();
					}
					if(flip){
						window.location = this.url;
					}
				},
				mouseover : function(){
					if(typeof this.flipper.onMouseOver == 'function'){
						this.flipper.onMouseOver();
					}
				},
				mouseout : function(){
					if(typeof this.flipper.onMouseOut == 'function'){
						this.flipper.onMouseOut();
					}
				}
			}
		});
		
		if(typeof this.images[0].url !='undefined' && this.images[0].url !==''){
			
			this.image.style.cursor='pointer';
			this.image.url = this.images[0].url;
		}
		
		
	}
	
};