sb.include('drag');
sb.include('browser.getScrollPosition');

/**
@Name: Element.prototype.makeDraggable
@Author: Paul Visco 
@Version: 1.21 04-13-06 07-29-09
@Description: Makes a DOM element draggable. All super elements, those normal elements passed through $ or elements created with sb.element, have the method makeDraggable when surebert.drag.js is included in the source.
@Example: 
var draggableThing = $('#dragme');
draggableThing.makeDraggable();
*/
Element.prototype.makeDraggable = function(){
	this.$('.dragHandle').forEach(function(v){
		if(v.className.match(/dragHandle/i)){
			v.style.cursor = 'move';
		}
	});
	this.sbDraggable = this.evt('mousedown', sb.drag.start);
};

/**
@Name: Element.prototype.makeUnDraggable
@Author: Paul Visco 
@Version: 1.21 04-13-06 07-29-09
@Description:  Makes a DOM element no longer draggable.  All super elements, those normal elements passed through $ or elements created with sb.element, have the method makeUnDraggable when surebert.drag.js is included in the source.
@Example: 
var draggableThing = $('#dragme');
draggableThing.makeUnDraggable();
*/
Element.prototype.makeUnDraggable = function(){
	
	this.$('.dragHandle').forEach(function(v){
		if(v.className.match(/dragHandle/i)){
			v.style.cursor = '';
		}
	});
	this.eventRemove(this.sbDraggable);
};