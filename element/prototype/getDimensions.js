/**
@Name: sb.element.prototype.getDimensions
@Description: calculates and assigns width and height properties to an to an element
@Example:
myElement.getDimensions();
alert(myElement.width);
*/
sb.element.prototype.getDimensions = function() {
	
    var display = this.getStyle('display');
    // Safari bug
    if (display != 'none' && display !== null) {
    	this.width = this.offsetWidth;
    	this.height = this.offsetHeight;
     
    } else {
    	
	    // All *Width and *Height properties give 0 on els with display none,so enable the el temporarily
	
	    var origStyles = {
	    	visibility : this.style.visibility,
	    	position : this.style.position,
	    	display : this.style.display
	    };
	    
	    this.styles({
	    	visibility : 'hidden',
	    	position : 'absolute',
	    	display : 'block'
	    });
		
	  	this.width = this.clientWidth;
	  	this.height = this.clientHeight;
	  	this.styles(origStyles);
    }
    return this;
};

Element.prototype.getDimensions = sb.element.prototype.getDimensions;