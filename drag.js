sb.include('browser.removeSelection');

/**
@Name: sb.drag
@Author: Paul Visco v1.02 09/02/06 12/15/08
@Description: Used by Element.prototype.makeDraggable.
An event handler which makes DOM nodes draggable.  Any super element (from sb.element or $) can become draggable using this.  Draggable elements have three additional methods and two setting.  The additional methods are ondragstop, ondragstart, and ondrag.  The properties are lockX and lockY.  Any element within the element being set as draggable that has a className which contains "dragHandle" is set as the handle to drag the object with.  Items can have multiple handles.
@Example:
<div id="dragme" class="dragPaper" ><p class="dragHandle" style="background-color:red;border:1px solid black;">Drag From Here</p><p>Here is an drag box with a handle and some text</p></div>
var draggableThing = $('#dragme');
draggableThing.makeDraggable();

//to stop this element from being draggable
//draggableThing.makeUnDraggable();

//the this refers to the element being dragged
draggableThing.ondrag = function(e, pos){
	document.title = e.clientX;
};

//the this referes to the element being dragged
draggableThing.ondragstop = function(e){
	document.title = e.clientX;
};

//the this referes to the element being dragged
draggableThing.ondragstart = function(e){
	document.title = e.clientX;
};

//locks the x axis from being dragged e.g. only drags up and down
draggableThing.lockX=1;

//locks the y axis from being dragged e.g. only drags left to right
draggableThing.lockY=1;
*/

sb.drag = {
	
	debug : 0,
	zIndex : 0,
	el : {},
	
	move : function(e){
		
		e.preventDefault();
		var scroll = sb.browser.getScrollPosition();
		var x = e.clientX, y = e.clientY, el = sb.drag.el;
		
		if(typeof sb.drag.el.lockX =='undefined'){
			x=el.x.estart + x + scroll[0] - el.x.cstart;
			el.style.left = x+'px';
		}
		if(typeof sb.drag.el.lockY =='undefined'){
			y = el.y.estart + y + scroll[1] - el.y.cstart;
			el.style.top = y+'px';
		}
		
		sb.browser.removeSelection();
		
		if(typeof sb.drag.ondrag == 'function'){
			
			sb.drag.ondrag.call(sb.drag.el, e, {x:x,y:y});	
		}
	},
	
	stop : function(e){
		
		e.preventDefault();
		if(sb.drag.mmove){
			sb.events.remove(sb.drag.mmove);
		}
		if(sb.drag.mup){
			sb.events.remove(sb.drag.mup);
		}
		//sb.drag.el.setOpacity(sb.drag.el.origOpacity||1);
		
		if(typeof sb.drag.ondragstop == 'function'){
			sb.drag.ondragstop.call(sb.drag.el, e);	
		}
		 
	},
	
	start : function(e, id){
		
		var x=e.clientX, y=e.clientY, el;
		var scroll = sb.browser.getScrollPosition();
		
		el = $(this);
		
		//set handlers
		if(typeof this.ondrag == 'function'){
			sb.drag.ondrag = this.ondrag;	
		}
		
		if(typeof this.ondragstart == 'function'){
			sb.drag.ondragstart = this.ondragstart;	
			sb.drag.ondragstart.apply(sb.drag.el);
		}
		
		if(typeof this.ondragstop == 'function'){
			sb.drag.ondragstop = this.ondragstop;	
		}
		
		
		if(el.getStyle('position') == 'static' && sb.drag.debug ==1){
			alert('You need to set position style on elemement');
		}
		
		while(el.nodeType == 3 || el.getStyle('position') == 'static'){
			el = $(el.parentNode);
		}
		
		el.x = {
			cstart : x+scroll[0],
			estart : parseInt(el.getStyle('left'), 10)
		};
		
		el.y = {
			cstart : y+scroll[1],
			estart : parseInt(el.getStyle('top'), 10)
		};
		
		if (isNaN(el.x.estart)) {el.x.estart = 0;}
	  	if (isNaN(el.y.estart)) {el.y.estart = 0;}
	 

		el.style.zIndex = ++sb.drag.zIndex;
		
		//set as global dragable element
		sb.drag.el = el;
		var target = e.target;
		
		if(target.hasClassName('dragHandle')){
			sb.drag.mmove = sb.events.add(document, 'mousemove', sb.drag.move);
			//el.opacity(0.3);
		} 
		sb.drag.mup = sb.events.add(document, 'mouseup', sb.drag.stop);
	
  }
	
};