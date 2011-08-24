sb.include('browser.removeSelection');
sb.include('effect');
sb.include('Element.prototype.cssTransition');
/**
@Name : sb.widget.sortList
@Version: 1.21 11/12/07 12/15/08
@Description: used to make sortable lists
@Example:
<style type="text/css">
		#my_list li{
			list-style-type:none;
			border:1px solid #ACACAC;
			background-color:white;
		}
		
		sb_sortlist_handle{
			background-image:url('http://webservicesdev.roswellpark.org/webui/icons/ns_arrow.png');
			background-repeat:no-repeat;
			margin-right:15px;
			width:20px;
			display:inline-block;
		}
	</style>
<ul id="my_list">
	<li id="a">Element 1</li>
	<li id="b">Element 2</li>
	<li id="c">Element 3</li>
	<li id="d">Element 4</li>
	<li id="e">Element 5</li>
</ul>
<script type="text/javascript">
var list = new sb.widget.sortList({
	list : '#my_list',
	
	onSwitch : function(){
	},
	onItemSelect : function(){
		this.style.backgroundColor = '#DFDFDF';
		//this.style.fontSize = (parseInt(this.getStyle('fontSize'), 10)+10)+'px';
	},
	onItemDrop :function(){
		this.style.backgroundColor = '';
		//this.spring();
	}
});
</script>
*/
sb.widget.sortList = function(params){
	sb.objects.infuse(params, this);
	this.list = sb.$(params.list);
	this.listItems = this.list.$('li');
	this.addButtons(params.handleHTML || '&nbsp;');
	this.addEvents();
};

sb.widget.sortList.sorting = 0;
sb.widget.sortList.mousemove = 0;
sb.widget.sortList.draggedItem = 0;
sb.widget.sortList.cursor = 'move';
sb.widget.sortList.prototype = {
	addButtons : function(handleHTML){
		
		this.listItems.forEach(function(li){
		
			var btn = new sb.element({
				tag : 'sb_sortlist_handle',
				className : 'sb_sortlist_handle',
				innerHTML : handleHTML,
				title : 'drag to change list order',
				styles : {
					cursor: sb.widget.sortList.cursor
				}
				
			}).appendToTop(li);
		
		});
		
		this.addOrder();
	},
	
	addOrder : function(){
		var order =0;
		sb.$(this.id+' li').forEach(function(li){
			li.order =order;
			//li.firstChild.innerHTML = order;
			order++;
		});
	},
	
	dataOut : function(){
		var order =[];
		this.list.$('li').forEach(function(li, k){
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
				
				var handle = target.isOrHasParentOfClassName('sb_sortlist_handle');
				if(handle){
					var li = handle.getContaining('li');
					var currentItems = self.list.$('li');
					sb.widget.sortList.draggedItem = li;
					
					sb.widget.sortList.sorting=1;
				
					if(typeof self.onItemSelect == 'function'){
						self.onItemSelect.call(li);
					}
					
					var y=e.clientY;
					
					sb.widget.sortList.mousemove = sb.events.add(self.list, 'mouseover', function(e){
						self.dir=1;
						if(y < e.clientY){
							self.dir = 1;
						} else {
							self.dir = -1;
						}
						y = e.clientY;
						sb.browser.removeSelection();
						if(sb.widget.sortList.draggedItem){
							var target = e.target;
							
							if(target.isWithin(self.list)){
								
								var li = target.nodeName == 'LI' ? target : target.getContaining('li');
								
								if(li && li != this){
									
									if(self.dir == 1){
										sb.widget.sortList.draggedItem.appendAfter(li);
									} else {
										sb.widget.sortList.draggedItem.appendBefore(li);
									}

									if(typeof self.onSwitch =='function'){
										self.onSwitch.call(li);
									}
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