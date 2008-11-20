sb.include('drag');
sb.include('browser.getScrollPosition');

/**
@Name: Element.prototype.makeDraggable
@Author: Paul Visco 
@Version: 1.2 04/13/06
@Description: Makes a DOM element draggable. All super elements, those normal elements passed through $ or elements created with sb.element, have the method makeDraggable when surebert.drag.js is included in the source.
@Example: 
var draggableThing = $('#dragme');
draggableThing.makeDraggable();
*/
Element.prototype.makeDraggable = function(){

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
@Name: Element.prototype.makeUnDraggable
@Author: Paul Visco 
@Version: 1.2 04/13/06
@Description:  Makes a DOM element no longer draggable.  All super elements, those normal elements passed through $ or elements created with sb.element, have the method makeUnDraggable when surebert.drag.js is included in the source.
@Example: 
var draggableThing = $('#dragme');
draggableThing.makeUnDraggable();
*/
Element.prototype.makeUnDraggable = function(){
	
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