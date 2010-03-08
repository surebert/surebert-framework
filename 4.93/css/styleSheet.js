
/**
@Name: sb.css.styleSheet
@Description: A constructor used to load additional CSS styleSheets into the page.  Each styleSheet has a hide and show method.  The stylesheet is hidden by default.
@Param: String url The address of the stylesheet to load can be on or off site
@Param: String stackPosition Determines if the stylesheet loads before or after all other styles sheets.  If set to 'before' it loads on as the first stylesheet so that all other styles override the values set within it, if not specified, the styleSheet loads at the end so that styles defined in it override all previous styles
@Example:
var mySheet= new sb.css.styleSheet('http://www.example.com/format.css');
*/
sb.css.styleSheet = function(url, stackPosition){
	this.sheet = document.createElement('link');
	this.sheet.rel = 'stylesheet';
	this.sheet.href = url;
	this.sheet.type = 'text/css';

	var head = sb.$('head');
	if(stackPosition == 'before'){
		head.insertBefore(this.sheet, head.firstChild);
		
	} else {
		head.appendChild(this.sheet);
		
	}
	
	sb.css.styleSheets.added.push(this);
};

/**
@Name: sb.css.styleSheet.prototype
@Description: All sb.css.styleSheet.prototype examples below assume a sb.css.styleSheet instance created liek this
@Example:
var mySheet= new sb.css.styleSheet('http://www.example.com/format.css');
*/
sb.css.styleSheet.prototype = {
	
	/**
	@Name: sb.css.styleSheet.prototype.show
	@Description: Enables the stylesheet
	@Example:
	mySheet.show();
	*/
	show : function(){
		this.sheet.disabled = 0;
	},
	
	/**
	@Name: sb.css.styleSheet.prototype.show
	@Description: Disables the stylesheet
	@Example:
	mySheet.hide();
	*/
	hide : function(){
		this.sheet.disabled = 1;
	},
	
	/**
	@Name: sb.css.styleSheet.prototype.toggle
	@Description: Toggles the display stylesheet
	@Example:
	mySheet.toggle();
	*/
	toggle : function(){
		if(this.sheet.disabled ==1){
			this.sheet.disabled = 0;
		} else {
			this.sheet.disabled = 1;
		}
	}
};

/**
@Name: sb.css.styleSheets
@Description: A collection of all stylesheets added as sb.css.styleSheet instances.  Can show or hide all.
*/
sb.css.styleSheets = {
	/**
	@Name: sb.css.styleSheets.added
	@Description: USed Internally to keep track of sb.css.styleSheet instances
	*/
	added : [],
	/**
	@Name: sb.css.styleSheets.show
	@Description: Shows all stylesheets added as sb.css.styleSheet instances
	@Example: sb.css.styleSheets.show();
	*/
	show : function(){
		sb.css.styleSheets.added.forEach(function(v){
			v.show();
		});
	},
	
	/**
	@Name: sb.css.styleSheets.hide
	@Description: Hides all stylesheets added as sb.css.styleSheet instances
	@Example: sb.css.styleSheets.hide();
	*/
	hide : function(){
		sb.css.styleSheets.added.forEach(function(v){
			v.hide();
		});
	}
};