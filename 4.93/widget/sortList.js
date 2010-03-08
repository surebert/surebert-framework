sb.include('browser.removeSelection');
sb.include('effect');
sb.include('Element.prototype.cssTransition');
/**
@Name : sb.widget.sortList
@Version: 1.21 11/12/07 12/15/08
@Description: used to make sortable lists
@Example:
myList = new sb.widget.sortList('#myList', '');

myList.onSwitch = function(){
	this.turnColor();
};

//this this of this is the list itself
myList.onMouseUp = function(){
	//document.title= this.dataOut();
};

//same time as onMouseUp but the this is the item itself
myList.onItemDrop = function(){
	this.turnColor2();
	//document.title = this.order;
};

//the this of this is the item selected
myList.onItemSelect = function(){
	//document.title = this.order;
};
*/
//returns an element to its default position
Element.prototype.getParent = function(){
	if(this.parentNode){
		return sb.$(this.parentNode);
	}
};

Element.prototype.spring = function(){
	var self = this;
	self.cssTransition([{
		prop : 'fontSize',
		unit : 'px',
		begin : 10,
		change : +20,
		type : 'outQuart',
		onEnd : function(){
			self.cssTransition([{
				prop : 'fontSize',
				unit : 'px',
				type : 'inQuart',
				begin : 30,
				change : -10
			}]).start();
		}
	}]).start();
};

Element.prototype.turnColor = function(){
	var self = this;
	if(this.style.backgroundColor !='#ffffff'){
		this.style.backgroundColor='#ffffff';
		window.setTimeout(function(){
			self.style.backgroundColor='';
		}, 300);
	}
};

Element.prototype.turnColor2 = function(){
	var self = this;
	if(self.trans){
		self.trans.stop();
	}
	self.trans = self.cssTransition([{
		prop : 'backgroundColor',
		type : 'outQuart',
		begin : '#DFDFDF',
		end : '#f59e38',
		onEnd : function(){
			self.cssTransition([{
			prop : 'backgroundColor',
			type : 'inQuart',
			begin : '#f59e38',
			end : '#DFDFDF'
			}, 35]).start();
		}
	}, 35]).start();
};

sb.widget.sortList = function(id, innerHTML){
	this.id = id;
	this.list = sb.$(id);
	this.listItems = sb.$(id+' li');
	this.addButtons(innerHTML);
	this.addEvents();
};

sb.widget.sortList.sorting = 0;
sb.widget.sortList.mousemove = 0;
sb.widget.sortList.draggedItem = 0;

sb.widget.sortList.prototype = {
	addButtons : function(innerHTML){
		
		this.listItems.forEach(function(li){
		
			var btn = new sb.element({
				tag : 'button',
				innerHTML : innerHTML || ' ',
				title : 'drag to change list order'
				
			}).appendToTop(li);
		
		});
		
		this.addOrder();
	},
	
	addOrder : function(){
		var order =0;
		sb.$(this.id+' li').forEach(function(li){
			li.order =order;
			li.firstChild.innerHTML = order;
			order++;
		});
	},
	
	dataOut : function(){
		var order =[];
		sb.$(this.id+' li').forEach(function(li, k){
			order[k] = li.id;
		});
		return order;
	
	},
	
	addEvents : function(){
		
		var self = this;
		
		this.list.events({
			
			mousedown : function(e){
				if(sb.widget.sortList.sorting ===1){return;}
				
				var target = e.target;
				
				if(target.nodeName =='BUTTON' && target.parentNode && target.parentNode.nodeName =='LI'){
					var currentItems = sb.$(self.id+ ' li');
				
					
					sb.widget.sortList.draggedItem = target.getParent();
					
					sb.widget.sortList.sorting=1;
				
					if(typeof self.onItemSelect == 'function'){
						self.onItemSelect.call(sb.widget.sortList.draggedItem);
					}
					
					var y=e.clientY;
					
					sb.widget.sortList.mousemove = sb.events.add(self.list, 'mouseover', function(e){
						var dir=1;
						if(y < e.clientY){
							dir = 1;
						} else {
							dir = -1;
						}
						y = e.clientY;
						sb.browser.removeSelection();
						if(sb.widget.sortList.draggedItem){
							var target = e.target;
							
							if(target.nodeName =='BUTTON' && target.parentNode && target.parentNode.nodeName =='LI' && target.parentNode !=this){
								if(dir==1){
									sb.widget.sortList.draggedItem.appendAfter(target.parentNode);
								} else {
									sb.widget.sortList.draggedItem.appendBefore(target.parentNode);
								}
								
								if(typeof self.onSwitch =='function'){
									self.onSwitch.call(target.parentNode);
								}
							}
							
						} else {
							sb.events.remove(sb.widget.sortList.mousemove);
						}
					});
				}
			},
			
			mouseup : function(e){
			
				if(sb.widget.sortList.draggedItem){
					
					self.addOrder();
					
					if(typeof self.onItemDrop == 'function'){
						self.onItemDrop.call(sb.widget.sortList.draggedItem);
					}
					
					if(typeof self.onMouseUp == 'function'){
						self.onMouseUp();
					}
					
					sb.widget.sortList.draggedItem=0;
				
					sb.widget.sortList.sorting =0;
					sb.events.remove(sb.widget.sortList.mousemove);
					
				}
			}
		});
	}
};