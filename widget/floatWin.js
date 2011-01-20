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
sb.widget.floatWin.winCount = 0;
sb.widget.floatWin.createHub = function(){

	this.hub = new sb.element({
		tag : 'div',
		id : 'sb_floatwin_hub'
	});

	this.hub.html('');
	this.hub.styles({
		position : 'fixed',
		bottom : '0px',
		right : '0px',
		zIndex : 999
	});
	this.hub.appendTo('body');
};

sb.widget.floatWin.prototype = {
	createBox : function(e){
		if(!sb.widget.floatWin.hub){
			sb.widget.floatWin.createHub();
		}
		var self = this;
		this.win = new sb.element({
			tag : 'div',
			className : 'sb_floatWin '+this.className || ''
		});

		this.titleBar = new sb.element({
			tag : 'div',
			className : 'sb_floatWinTitleBar dragHandle',
			title : 'Drag me to move window',
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
				var target = e.target;

				self.onDblClick(e);
				
				if(target.isWithin(self.titleBar)){
					if(self.onTitleBarDblClick(e) !== false){
						if(self.minimized){
							self.restore();
						} else {
							self.minimize();
						}
					}
					
				}

			}
		});

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

			this.downButton = this.addIcon(new sb.element({
				tag : 'img',
				src : sb.base+'_media/down.png',
				title : 'Click minimize',
				events : {
					click : function(e){
						self.minimize(e);
					}
				}
			}));

			this.upButton = this.addIcon(new sb.element({
				tag : 'img',
				src : sb.base+'_media/up.png',
				title : 'Click restore',
				events : {
					click : function(e){
						self.restore(e);
					}
				}
			}));
		}

		if(typeof this.title =='string'){
			this.setTitle(this.title);
		}

		this.win.appendTo('body');

		this.win.mv(sb.browser.w/2-this.win.offsetWidth/2,20,900+sb.widget.floatWin.winCount);

		sb.widget.floatWin.winCount++;
		this.win.style.position = 'fixed';

	},

	closeButton : true,

	shade : function(){
		if(this.win.shaded){

			this.win.style.height = '';
			this.win.style.overflow = 'auto';
			this.win.shaded=0;

		} else {

			this.win.oldHeight = this.win.style.height;
			this.win.style.height = this.titleBar.offsetHeight+'px';
			this.win.style.overflow ='hidden';
			this.win.shaded=1;
		}
	},

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

	restore : function(){
		
		this.minimized = false;
		if(this.win.origWidth){
			this.win.style.width = this.win.origWidth+'px';
		}
		this.win.appendTo('body');
		this.win.style.position = 'fixed';
		this.shade();
	},
	
	minimize : function(){
		this.win.appendTo(sb.widget.floatWin.hub);
		this.minimized = true;
		this.win.origWidth = this.win.getWidth();
		this.win.style.width = '250px';
		this.win.style.position = '';

		this.shade();
	},

	mv : function(x,y,z){
		this.win.mv(x,y,z);
	},

	wh : function(w,h){
		this.win.wh(w,h);
	},

	setTitle : function(txt){
		this.titleText.innerHTML=txt;
	},

	getTitle : function(){
		return this.titleText.innerHTML;
	},
	onTitleBarClick : function(e){},
	onContentClick : function(e){},
	onClick : function(e){},
	onDisplay : function(){},
	onIconClick : function(e){},
	onClose : function(e){},
	onTitleBarDblClick : function(e){},
	onDblClick : function(e){}
};

