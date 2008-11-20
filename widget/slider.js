sb.include('Element.prototype.setOpacity');
sb.include('Element.prototype.getPosition');

/**
@Name: sb.$
@Description: A widget for creating simple sliders
@Param: object Has the following properties
min integer The minimum value for the slider, defaults to 0
max integer The maximum value for the slider, defaults to 100
defaultValue integer The default value for the slider
allowFloats boolean Determines if float values are allowed or rounded
displayName boolean Is the name of the slider displayed on it
displayName boolean Is the value of the slider displayed on it
@Example:
var bri = new sb.slider({
	name : 'brightness',
	min : 0,
	max : 255,
	id : 'brightness',
	defaultValue : 100,
	allowFloats : 0,
	onchangevalue : function(){
		document.body.style.backgroundColor = 'rgb('+this.value+','+this.value+','+this.value+')';
		
	}
});

bri.appendTo('#brightnessDiv');

bri.setX(30);
*/
sb.widget.slider = function(params){
	
	sb.objects.infuse(params, this);
	this.min = params.min || 0;
	this.max = params.max || 100;
	this.defaultValue = (typeof params.defaultValue != 'undefined') ? params.defaultValue :  (this.max-this.min)/2;
	this.allowFloats = params.allowFloats || 0;
	this.displayName = params.displayName || 1;
	this.displayValue = params.displayValue || 1;
};

sb.widget.slider.prototype = {
	
	value :0,
	
	setX : function(x){
			var nonRoundX = x,str;
			x = Math.round(x);
			if(isNaN(x)){x=this.defaultValue;}
			
			if(x < 0){
				x=0;
				this.value=this.min;
				this.valueToPos(this.value, 1);
			} else if (x > this.width){
				this.value=this.max;
				x=this.width;
				this.valueToPos(this.value, 1);
			} else {
				this.value = this.posToValue(nonRoundX);
				this.getPercentage();
			}
			
			this.nob.title = this.value;
			
			this.nob.style.left=x+'px';
			if(this.display){
				
				if(this.displayName===1){str = this.name;}
				if(this.displayValue===1){str += ' '+this.value;}
				if(this.measurement !== undefined){str += this.measurement;}
				this.display.firstChild.data=str;
			}
			
			this.nob.value = this.value;
			if(typeof(this.onChangeValue)=='function'){
				this.onChangeValue();
			}
	},
	
	getX : function(){
		return this.nob.getPosition().left;
		
	},
	
	getPercentage : function(){
		
		this.percentage = (this.value ===0)? 0 :Math.round((this.value*100)/this.max);
		return this.percentage;
	},
	
	posToValue : function(val){
		val= this.min+(val*(this.max-this.min))/this.width;
		
		val= (this.allowFloats) ? sb.math.round(val, 3) : Math.round(val);
		if(isNaN(val)){val =this.min+1;}
		return val;
	},
	
	valueToPos : function(val, mv){
		var x;
		if(val===0 && this.min ===0){
			 x=0;
		} else {
			x= (this.width*(val-this.min))/(this.max-this.min);
			x=Math.round(x);
		}
	
		if(mv == 1){this.setX(x);}
		return x;
	},
	
	drag : function(e){
		var t=this,x;
		if(this.draggable === 1){
			x=this.valueToPos(this.defaultValue)+e.clientX-this.origX-this.nob.offsetWidth/2;
			
			this.setX(x);
		}
	},
	
	dragStop : function(){
		this.draggable = 0;
		document.onmousemove = null;
		document.onmouseup = null;
	},
	
	events : [],
	
	dragStart : function(){
		var t=this;
		this.origX=this.getX();
	
		sb.events.add(t.nob, 'mousedown',  
			function(e){
				
				t.draggable=1;
				t.events.mousemove = sb.events.add(document, 'mousemove', function(e){t.drag(e);return false;});
				
				t.events.onmouseup = sb.events.add(document, 'mouseup', function(e){
					if(typeof t.onmouseup =='function'){t.onmouseup();}
					t.dragStop();
					return false;
				});
		
				sb.events.preventDefault(e);
				return false;
			}
		);
		
	},
	
	calibrate : function(){
		
		var oldValue = this.value;
		this.valueToPos(this.defaultValue, 1);
		this.dragStart();
		this.valueToPos(oldValue, 1);
	},
	
	addDefaultStyles : function(){
		
		this.track.styles({
			cursor : 'pointer',
			position : 'relative',
			display : 'block'
			
		});
		
		this.nob.setOpacity(0.5);
		
		this.nob.styles({
				cursor : 'w-resize',
				position : 'absolute',
				zIndex : 1
		});

		if(this.track.getStyle('backgroundColor')=='transparent'){
			this.track.setStyle('backgroundColor', 'orange');
		}
		
		if(this.nob.getStyle('backgroundColor')=='transparent'){
			this.nob.setStyle('backgroundColor', 'green');
		}
		
		if(this.nob.getStyle('width')=='0px' || this.nob.getStyle('width')=='auto'){
			this.nob.setStyle('width', '1.0em');
		}
		
		if(this.nob.getStyle('height')=='0px' || this.nob.getStyle('height')=='auto' ){
			this.nob.setStyle('height', '100%');
		}
		
		if(this.track.getStyle('fontSize')=='0px'){
			this.nob.setStyle('fontSize', '1.5em');
		}
		
		this.track.style.width=this.track.offsetWidth-this.nob.offsetWidth+'px';
		
		this.width = parseInt(this.track.getStyle('width'), 10);
		
		this.track.style.width=this.width+this.nob.offsetWidth+'px';
		
	},
	
	appendTo : function(container){
		
		var t=this;
		t.container = sb.$(container);
	
		this.track = new sb.element({
			tag : 'slider',
			id : this.id
			
		});
		
		this.track.appendTo(this.container);
	
		//create the nob
		this.nob = new sb.element({
			tag : 'nob'
		});
		
		this.nob.appendTo(this.track);
		
		//create the 
		if(this.displayName ==1){
			this.display = new sb.element({
				tag : 'display'
			});
			this.display.append(document.createTextNode(' '));
			this.display.appendTo(this.track);
		}
		
		this.addDefaultStyles();
		
		this.track.event('mousedown', 
			function(e){
				if(typeof t.onmousedown =='function'){t.onmousedown();}
				t.setX(t.valueToPos(t.defaultValue)+e.clientX-t.origX-t.nob.offsetWidth/2);
			}
		);
		
		//move to the defaultValue
		this.valueToPos(this.defaultValue, 1);
	
		sb.events.add(window, 'resize', function(){t.calibrate();});
	
		this.calibrate();
	},
	
	reset : function(){
		this.valueToPos(this.defaultValue,1);
	}, 
	
	toString : function(){
		return '[sb slider]';
	}
		
};
