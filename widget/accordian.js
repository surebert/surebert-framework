sb.include('effect');
sb.include('arrays.iteration');
sb.include('element.prototype.cssTransition');

sb.widget.accordian = function(o){
	this.className = o.className || 'sb_accord';
	
	this.loadAccordians();
	this.accordians.forEach(function(accordian){
		if(o.height){
			accordian.style.height = o.height+'px';
		}
		accordian.fireOnMouseOver = o.fireOnMouseOver ||0
		accordian.onClose = o.onClose || 0;
		accordian.onClosed = o.onClosed || 0;
		accordian.onOpen = o.onOpen || 0;
		accordian.onOpened = o.onOpened || 0;
		accordian.evt = o.evt || 'click'
		accordian.firing = 0;
		if(o.events){
			accordian.events(o.events);
		}
	});
	this.accordians.forEach(this.getAndCloseSections);
	this.accordians.forEach(this.addClickEvent);
	return this.accordians;
	//alert(this.accordians[0].sections[3].innerHTML);
};

//this sets the minimum height to 1px for ie as it freaks out and sets height to auto if it is 0
sb.widget.accordian.minHeight = (sb.browser.agent =='ie' && sb.browser.version==6) ?1: 0;


sb.widget.accordian.prototype = {
	accordians : [],
	loadAccordians : function(){
		this.accordians = sb.s$('dl.'+this.className).nodes;
	},
	
	getAndCloseSections : function(accordian){
		accordian.style.overflow='hidden';
		accordian.sections = s$(accordian, 'dt').nodes;
		accordian.titlesHeight = 0;
		accordian.sections.forEach(function(section){
			
			section.accordian = accordian;
			section.contents = section.getNextSibling();
		
			accordian.titlesHeight +=section.getHeight();
			section.contents.style.overflow='hidden';
			section.selected =0;
			
			
			section.open = function(){
				
				var t =this;
				t.selected =1;
				accordian.sections.forEach(function(section){
					
					var border = parseInt(accordian.getStyle('border'), 10);
					accordian.border = (isNaN(border)) ? 0 : border;
					
					accordian.beginHeight = accordian.getHeight()-(accordian.border*2);
					
			
					if(section !=t){
						
						if(section.contents){
							
							if(section.contents.getHeight() !== sb.widget.accordian.minHeight){
								section.close();
								
							} 
							
							
						}
					}
					
				});
				
				var selectedSection = this.getNextSibling();
			
				if(typeof accordian.onOpen =='function'){
					accordian.onOpen.call(t);
				}
				
				if(typeof section.onOpen == 'function'){
					section.onOpen.call(t);
				}
			
				var sectionHeight = section.getNextSibling().firstChild.offsetHeight;
				selectedSection.fold = selectedSection.cssTransition([{
				
					begin : 0,
					prop : 'height',
					change : sectionHeight,
					unit : 'px',
					onEnd : function(){
						
						if(typeof accordian.onOpened =='function'){
							accordian.onOpened.call(section);
						}
						if(typeof section.onOpened == 'function'){
							section.onOpened.call(section);
						}
						accordian.firing =0;
					},
					
					duration : 10
				}]);
				
				selectedSection.fold.start();
					
					
			};
			
			section.close = function(){
				var t=this;
				t.selected =0;
				var h = this.contents.getHeight();
				this.contents.style.overflow='hidden';				
				if(typeof accordian.onClose =='function'){
					accordian.onClose.call(t);
				}
				
				if(typeof section.onClose == 'function'){
					section.onClose.call(t);
				}
						
				section.contents.cssTransition([{
					begin : h,
					prop : 'height',
					change : -h,
					unit : 'px',
					onEnd : function(){
						if(typeof section.onClosed =='function'){
							section.onClosed.call(t);
						}
						
						if(typeof accordian.onClosed =='function'){
							accordian.onClosed.call(t);
						}
						accordian.firing =0;
						
					},
					
					duration : 10
				}]).start();
			};
			
			if(section != accordian.sections.last()){
				section.contents.setStyle('height', sb.widget.accordian.minHeight+'px');
				
			} else {
				section.open();
				
			}
		
			
		});
		
		
	
	},
	
	addClickEvent: function(accordian){
	
		function show(e){
			var target = sb.events.target(e);
			var t = sb.s$(target);
			if(target.nodeName == 'DT' && accordian.firing==0 && target.selected !=1){
				accordian.firing =1;
				
					target.open();
				
			}
			
		}
	
		if(accordian.fireOnMouseOver ==1){
			accordian.event('mouseover', show);
		}
		
		accordian.event('mousedown', show);
	}
	
};