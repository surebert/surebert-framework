/**
@Name: sb.css.styleTag
@Description: Used to create a css style tag on the fly.  You can pass it an existing style tag if you would like to manipulate that one. 
@Param: Object styleTag An already existing style tag node - optional
@Example:
var css = new sb.css.styleTag();
css.write('body{border:10px solid red;}');
css.clear();
css.write('body{border:10px solid yellow;}');
css.replace('body', '#main');
css.hide();
css.toggle();
*/
sb.css.styleTag = function(styleSheet){
	
	if(styleSheet && styleSheet.nodeName && styleSheet.nodeName == 'STYLE'){
		this.styleSheet = styleSheet;
	} else {
		this.styleSheet = new sb.element({
			tag : 'style'
		});
	}
	
	this.styleSheet.setAttribute('type', 'text/css');
	this.show();
	
};

sb.css.styleTag.prototype = {
	/**
	@Name: sb.css.styleTag.prototype.showing
	@Description: Used Internally. denotes if the style tag node is being reendered or not
	*/
	showing : 0,
	
	clear : function(){
		if (sb.browser.agent == 'ie') {
			 this.styleSheet.styleSheet.cssText = '';
		} else {
			for(var x=this.styleSheet.childNodes.length-1;x>=0;x--){
				this.styleSheet.childNodes[x].data = '';
			}
		}
		
	},
	
	hide : function(){
		
		this.styleSheet.remove();
		this.showing = 0;
	},
	
	show : function(){
		
		document.getElementsByTagName('head')[0].appendChild(this.styleSheet);
		this.showing = 1;
	},
	
	toggle : function(){
		if(this.showing){
			this.hide();
		} else {
			this.show();
		}
	},
	
	write: function(css){
		if (sb.browser.agent == 'ie') {
			 this.styleSheet.styleSheet.cssText = css;
		} else {
			var data = document.createTextNode(css);
			this.styleSheet.appendChild(data);
		}
	},
	
	replace : function(a, b){
		
		if (sb.browser.agent == 'ie') {
			  this.styleSheet.styleSheet.cssText = this.styleSheet.styleSheet.cssText.replace(a, b);
		} else {
			for(var x=this.styleSheet.childNodes.length-1;x>=0;x--){
				this.styleSheet.childNodes[x].data = this.styleSheet.childNodes[x].data.replace(a, b);
			}
		}
	}
	
};
