sb.include('drag');

/**
@Name: sb.element.prototype.makeDraggable
@Author: Paul Visco 
@Version: 1.2 04/13/06
@Description: Makes a DOM element draggable. All super elements, those normal elements passed through s$ or elements created with sb.element, have the method makeDraggable when surebert.drag.js is included in the source.
@Example: 
var draggableThing = s$('#dragme');
draggableThing.makeDraggable();
*/
sb.element.prototype.makeDraggable = function(){

	sb.$(this.id+' *').forEach(function(v){
		if(v.className.match(/dragHandle/i)){
			v.style.cursor = 'move';
		}
	});
	if(this.className.match(/dragHandle/)){
		this.style.cursor = 'move';
	}
	this.sbDraggable = this.event('mousedown', sb.drag.start);
};

/**
@Name: sb.element.prototype.makeUnDraggable
@Author: Paul Visco 
@Version: 1.2 04/13/06
@Description:  Makes a DOM element no longer draggable.  All super elements, those normal elements passed through s$ or elements created with sb.element, have the method makeUnDraggable when surebert.drag.js is included in the source.
@Example: 
var draggableThing = s$('#dragme');
draggableThing.makeUnDraggable();
*/
sb.element.prototype.makeUnDraggable = function(){
	
	sb.$(this.id+' *').forEach(function(v){
		if(v.className.match(/dragHandle/i)){
			v.style.cursor = '';
		}
	});
	if(this.className.match(/dragHandle/)){
		this.style.cursor = '';
	}
	this.eventRemove(this.sbDraggable);
};

Element.prototype.makeDraggable = sb.element.prototype.makeDraggable;
Element.prototype.makeUnDraggable = sb.element.prototype.makeUnDraggable;