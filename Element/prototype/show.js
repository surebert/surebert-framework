/**
@Name: Element.prototype.show
@Description: Switches an elements display back to whatever its default was.  Tis is the reciprocal method for myElement.hide();
@Return: returns itself
@Example:
myElement.show();
*/
Element.prototype.show = function(){
	try{
		var s = this.getStyle('display');
		this.style.display = s =='none' ? 'block' : s;
	} catch(e){
		this.style.display='block';
	}
	return this;
};