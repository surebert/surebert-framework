sb.include('Element.prototype.makeDraggable');

sb.widget.floatWin = function(o){
	
	o = o || {};
	
	var floatWin = new sb.element({
		tag : 'div',
		className : 'sb_floatWin '+o.className || '',
		
		addContent : function(content){
			var typ = sb.typeOf(content);
			if(typ == 'string'){
				this.content.innerHTML = content;
			} else if(typ =='sb.element'){
				content.appendTo(this.content);
			}
		},
		
		addIcon : function(el){
			
			if(sb.typeOf(el)=='sb.element'){
				el.win=this;
				el.style.cursor = 'pointer';
				return el.appendToTop(this.titleIcons);
			}
		},
	
		clearContent : function(){
			this.content.innerHTML ='';
		},
		
		close : function(){
			this.hide();
			
		},
		
		content : new sb.element({
			tag : 'div',
			className : 'sb_floatWinContent'
		}),
	
		maximize : function(){
			this.origW = this.offsetWidth;
			this.origH = this.offsetHeight;
			this.origX = this.offsetLeft;
			this.origY = this.offsetTop;
			
		//	sb.consol.log(this.origW+' '+this.origH);
			this.mv(0,0,999);
			this.wh(sb.browser.w, sb.browser.h);
		},
		
		minimize : function(){
			this.origW = this.offsetWidth;
			this.origH = this.offsetHeight;
			this.origX = this.offsetLeft;
			this.origY = this.offsetTop;
			
			var titleHeight=this.titleBar.offsetHeight;
			titleHeight += parseInt(this.titleBar.getStyle('margin'), 10);
			titleHeight += parseInt(this.titleBar.getStyle('padding'), 10);
			
			this.mv(0,sb.browser.h-(titleHeight+18),999);
			this.wh(this.offsetWidth, titleHeight);
			this.style.overflow='hidden';
		},
		
		restore : function(){
			
			this.wh(this.origW, this.origH);
			this.mv(this.origX, this.origY, 999);
			this.style.overflow='';
		},
		
		setTitle : function(txt){
		
			this.titleText.innerHTML=txt;	
		},
		
		titleBar : new sb.element({
			tag : 'div',
			className : 'sb_floatWinTitleBar dragHandle'
		}),
		
		titleIcons : new sb.element({
			tag : 'div',
			className : 'sb_floatWinTitleIcons',
			styles : {
				cssFloat : 'right',
				styleFloat : 'right',
				textAlign : 'right'
			}
		}),
		
		titleText : new sb.element({
			tag : 'div',
			className : 'sb_floatWinTitleText dragHandle',
			innerHTML : 'title',
			shaded : 0,
			events : {
				dblclick : function(){
					if(this.shaded === 0){
						this.oldHeight = floatWin.style.height;
						floatWin.style.height = this.titleBar.offsetHeight+'px';
						floatWin.style.overflow ='hidden';
						this.shaded=1;
					} else {
						floatWin.style.height =this.oldHeight;
						floatWin.style.overflow ='auto';
						this.shaded=0;
					}
				}
			}
		}),
		
		styles :{
			
		}
	});
	
	floatWin.mv(sb.browser.w/2,sb.browser.h/2,999);
	
	floatWin.titleBar.appendTo(floatWin);
	floatWin.titleIcons.appendTo(floatWin.titleBar);
	floatWin.titleText.appendTo(floatWin.titleBar);
	
	
	floatWin.content.appendTo(floatWin);
	floatWin.makeDraggable();

	if(!o.closeButton){
		floatWin.addIcon(new sb.element({
			tag : 'img',
			src : sb.base+'_media/close.png',
			onclick : function(){
				floatWin.close();
			}
		}));
	}
	
	if(typeof o.title =='string'){
		floatWin.setTitle(o.title);
	}
	return floatWin;
	
	
};


/*
var x = new sb.floatWin({});

x.id='xx';
x.setTitle('xx');
x.appendTo('body');
x.wh(400,200);
x.mv(200,0, 999);
x.addContent('xxx');

*/