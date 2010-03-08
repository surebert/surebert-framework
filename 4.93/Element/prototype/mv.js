/**
@Name: Element.prototype.mv
@Description: Moves an element to a specific x, y and z position either absolutly or relatively is specified
@Param: Number x The x position to move the element to
@Param: Number y The y position to move the element to
@Param: Number z The zIndex to move the element to
@Return: returns itself
@Example:
myElement.mv(200,200,999);
//move sthe node to absolute position 200,200 and a zIndex of 999
*/
Element.prototype.mv = function(x,y,z){
	
	this.style.left = x+'px';
	this.style.top = y+'px';
	if(z){
		this.style.zIndex = z;
	}
	
	if(this.getStyle('position') == 'static'){this.style.position = 'absolute';}
	
	this.getPosition();
	return this;
};