/**
@Name: sb.element.prototype.show
@Description: Switches an elements display back to whatever its default was.  Tis is the reciprocal method for myElement.hide();
@Return: returns itself
@Example:
myElement.show();
*/
sb.element.prototype.show = function(){
	try{
	this.style.display = (this.getStyle('display')=='none') ? 'block' : this.getStyle('display'); 
	} catch(e){
		this.style.display='block';
	}
	return this;
};

Element.prototype.show = sb.element.prototype.show;