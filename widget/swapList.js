sb.include('browser.removeSelection');
sb.include('element.prototype.clearPosition');

/**
@Name : sb.widget.swapList
@Version: 1.2 11/12/07
@Description: used to make swappable lists
@Example:
myList = new sb.widget.swapList('#myList', '');

*/
sb.element.prototype.getParent = function(){
	if(this.parentNode){
		return sb.s$(this.parentNode);
	}
	return false;
};

sb.widget.swapList = function(id, innerHTML){
	this.id = id;
	this.list = sb.s$(id);
	this.listItems = sb.s$(id+' li');
	this.addButtons(innerHTML);
	this.addEvents();
};

sb.widget.swapList.sorting = 0;
sb.widget.swapList.mousemove = 0;
sb.widget.swapList.draggedItem = 0;

sb.widget.swapList.prototype = {
	addButtons : function(innerHTML){
		
		this.listItems.forEach(function(li){
		
			var el = new sb.element({
				tag : 'button',
				innerHTML : innerHTML || ' '
				
			}).appendToTop(li);
		
		});
		
		this.addOrder();
	},
	
	addOrder : function(){
		var order =0;
		sb.s$(this.id+' li').forEach(function(li){
			li.order =order;
			//li.firstChild.innerHTML = order;
			order++;
		});
	},
	
	getOrder : function(){
		var order =[];
		sb.s$(this.id+' li').forEach(function(li, k){
			order[k] = li.id;
		});
		return order;
	
	},
	
	addEvents : function(){
		
		var self = this;
		
		this.list.events({
			
			mousedown : function(e){
				if(sb.widget.swapList.sorting ===1){return;}
				
				var target = sb.events.target(e);
				if(target.nodeName =='BUTTON' && target.parentNode && target.parentNode.nodeName =='LI'){
					
					sb.widget.swapList.draggedItem = target.getParent();
					
					sb.widget.swapList.draggedItem.origX = sb.widget.swapList.draggedItem.getX();
					sb.widget.swapList.draggedItem.origY = sb.widget.swapList.draggedItem.getY();
					
					sb.widget.swapList.draggedItem.styles({
						position : 'absolute',
						zIndex: 999
					});
					
					sb.widget.swapList.sorting=1;
					sb.widget.swapList.clone = sb.s$(sb.widget.swapList.draggedItem.cloneNode(1));
					sb.widget.swapList.clone.clearPosition();
					sb.widget.swapList.clone.appendBefore(sb.widget.swapList.draggedItem);
					
					sb.widget.swapList.clone.addClassName('sb_highlighted');
					if(typeof self.onSelect == 'function'){
						self.onSelect.call(sb.widget.swapList.draggedItem);
					}
					
					var x = sb.widget.swapList.draggedItem.getX()-40;
					var y = sb.widget.swapList.draggedItem.getY()-sb.widget.swapList.draggedItem.offsetTop;
				
					
					sb.widget.swapList.mousemove = sb.events.add(document, 'mousemove', function(e){
						sb.browser.removeSelection();
						if(sb.widget.swapList.draggedItem){
							var target = sb.events.target(e);
							
							sb.widget.swapList.draggedItem.mv(e.clientX-x+40, e.clientY-y);
							
						} else {
							sb.events.remove(sb.widget.swapList.mousemove);
						}
					});
				}
			},
			
			mouseup : function(e){
				
				var target = sb.events.target(e);
			
				if(sb.widget.swapList.draggedItem){
					
					if(target.nodeName =='BUTTON' && target.parentNode && target.parentNode.nodeName =='LI'){
						
						var old = sb.widget.swapList.draggedItem.replace(target.parentNode);
					//alert(sb.s$(self.id).getElementsByTagName('li').length);
						var newPos = target.parentNode.appendAfter(sb.$(self.id).getElementsByTagName('li')[sb.widget.swapList.draggedItem.order]);
						
						
						newPos.addClassName('sb_highlighted');
						sb.widget.swapList.draggedItem.addClassName('sb_highlighted');
						window.setTimeout(function(){
							newPos.removeClassName('sb_highlighted');
							sb.widget.swapList.draggedItem.removeClassName('sb_highlighted');
							
							if(typeof self.onDrop == 'function'){
								self.onDrop.call(sb.widget.swapList.draggedItem);
							}
							
							if(typeof self.onChangePosition == 'function'){
								self.onChangePosition();
							}
							
						}, 1000);
						
					} 
				
					sb.widget.swapList.draggedItem.clearPosition();
					sb.widget.swapList.clone.remove();
					self.addOrder();
					sb.widget.swapList.sorting =0;
					
					sb.events.remove(sb.widget.swapList.mousemove);
					
					
				}
			}
		});
	}
};