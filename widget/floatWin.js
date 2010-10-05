sb.include('Element.prototype.makeDraggable');
sb.include('Element.prototype.getPosition');
sb.include('Element.prototype.mv');
sb.include('Element.prototype.wh');
sb.include('Element.prototype.hide');
sb.include('Element.prototype.show');
sb.include('Element.prototype.isWithin');

sb.widget.floatWin = function(params){
	sb.objects.infuse(params, this);
	this.createBox();
};

sb.widget.floatWin.prototype = {
	createBox : function(){

		var self = this;
		this.win = new sb.element({
			tag : 'div',
			className : 'sb_floatWin '+this.className || ''
		});

		this.titleBar = new sb.element({
			tag : 'div',
			className : 'sb_floatWinTitleBar dragHandle',
			onselectstart : function() {
				return false;
			},
			unselectable : "on",
			styles : {
				MozUserSelect : 'none'
			}
		});

		this.titleIcons = new sb.element({
			tag : 'div',
			className : 'sb_floatWinTitleIcons',
			styles : {
				cssFloat : 'right',
				styleFloat : 'right',
				textAlign : 'right'
			}
		});

		this.titleText = new sb.element({
			tag : 'div',
			className : 'sb_floatWinTitleText dragHandle',
			innerHTML : 'title',
			shaded : 0
		});

		this.content = new sb.element({
			tag : 'div',
			className : 'sb_floatWinContent'
		});

		this.win.events({
			click : function(e){
				var target = e.target;

				self.onClick(e);
				if(target.isWithin(self.content)){
					self.onContentClick(e);
				} else {

					if(target.isWithin(self.titleBar)){
						self.onTitleBarClick(e);
					}
					if(target.nodeName == 'IMG' && target.isWithin(self.titleIcons)){
						self.onIconClick(e);
					}
					
				}

			},
			dblclick : function(e){
				
				if(e.target == self.titleText || e.target.isWithin(self.titleText)){
					if(this.shaded){

						this.style.height = '';
						this.style.overflow = 'auto';
						this.shaded=0;

					} else {

						this.oldHeight = this.style.height;
						this.style.height = self.titleBar.offsetHeight+'px';
						this.style.overflow ='hidden';
						this.shaded=1;
					}
				}
				
			}
		});

		this.win.mv(sb.browser.w/2,sb.browser.h/2,999);

		this.titleBar.appendTo(this.win);
		this.titleIcons.appendTo(this.titleBar);
		this.titleText.appendTo(this.titleBar);
		this.content.appendTo(this.win);
		this.win.makeDraggable();

		if(this.closeButton){
			this.closeButton = this.addIcon(new sb.element({
				tag : 'img',
				src : sb.base+'_media/close.png',
				title : 'Click to close',
				events : {
					click : function(e){
						self.close(e);
					}
				}
			}));
		}

		if(typeof this.title =='string'){
			this.setTitle(this.title);
		}

	},

	closeButton : true,

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
		} else if(sb.typeOf(el) == 'string'){
			return new sb.element({tag : 'img', src : el}).appendToTop(this.titleIcons);
		}
	},

	clearContent : function(){
		this.content.innerHTML ='';
	},

	close : function(e){
		this.onClose(e);
		this.win.hide(e);
	},

	show : function(){
		this.win.show();
		this.titleText.style.backgroundColor = this.titleBar.getStyle('backgroundColor');
		this.onDisplay();

	},

	maximize : function(){
		this.win.origW = this.win.offsetWidth;
		this.win.origH = this.win.offsetHeight;
		this.win.origX = this.win.offsetLeft;
		this.win.origY = this.win.offsetTop;

		this.win.mv(0,0,999);
		this.win.wh(sb.browser.w, sb.browser.h);
	},

	minimize : function(){
		this.win.origW = this.win.offsetWidth;
		this.win.origH = this.win.offsetHeight;
		this.win.origX = this.win.offsetLeft;
		this.win.origY = this.win.offsetTop;

		var titleHeight=this.titleBar.offsetHeight;
		titleHeight += parseInt(this.titleBar.getStyle('margin'), 10);
		titleHeight += parseInt(this.titleBar.getStyle('padding'), 10);

		this.win.mv(0,sb.browser.h-(titleHeight+18),999);
		this.win.wh(this.offsetWidth, titleHeight);
		this.win.style.overflow='hidden';
	},

	mv : function(x,y,z){
		this.win.mv(x,y,z);
	},

	wh : function(w,h){
		this.win.wh(w,h);
	},

	restore : function(){

		this.win.wh(this.win.origW, this.win.origH);
		this.win.mv(this.win.origX, this.win.origY, 999);
		this.win.style.overflow='';
	},

	setTitle : function(txt){

		this.titleText.innerHTML=txt;
	},
	onTitleBarClick : function(e){},
	onContentClick : function(e){},
	onClick : function(e){},
	onDisplay : function(){},
	onIconClick : function(e){},
	onClose : function(e){}
};

