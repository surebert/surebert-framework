/**
@Name: sb.ie6
@Description: methods for dealing with ie6 inconsistences in displaying transparent pngs
@Version: 2.0 11/13/07
*/

sb.ie6 = {
	
	/**
	@Name: sb.ie6.pngFix
	@Description: Forces transparent PNGs to display properly in IE 6
	@Param: The element which has a png that needs to be fixed or a conatiner element containing other elements that need to be fixed
	@Example: 
	//fix a single image
	sb.ie6.pngFix('#myImg');
	*/
	pngFix : function(obj){
		var i,im,images,src,st;
		
		obj = sb.$(obj);
		if(typeof obj.src !='undefined'){
			images = [obj];
		} else {
			images = obj.getElementsByTagName('img');
		}
		var len = images.length;
		for(i=0;i<len;i++){	
			
			im = images[i];
			src = im.src;
			st = im.style;
			if( src.substr((src.length -3),3)== "png"){
				if(im.width === 0 || im.height === 0){
					continue;
				}
				st.width = im.width + 'px';
				st.height = im.height + 'px';
			
				im.src = sb.base+'/_media/spacer.gif';
				st.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "', sizingMethod='image')";
	
			}
		}
		
	},
	
	/**
	@Name: sb.ie6.pngFixBg
	@Description: allows for background png image fix in IE 6
	@Param: The element which has a background png that needs to be fixed for ie6
	@Example: sb.ie6.pngFix('#myElement');
	*/
	
	pngFixBg : function(el){
			var png='';

			el = sb.$(el);
		
			if(el.currentStyle.backgroundImage.match(/\.png/)){
			png = el.currentStyle.backgroundImage;
				png = png.replace("url(", "");
				png = png.replace(")", "");
				
				el.style.backgroundImage = 'none';
				el.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale src="+png+")";
			
			}
		
	}

};

sb.dom.createNamedElement = function(t, n, c) {
	var el;
	
	try {
		el = document.createElement('<input type="'+t+'" name="'+n+'" checked="'+((c) ? 'checked' : '')+'">');
		
	} catch (e) { }
	
		if (!el || !el.name) { 
		el = document.createElement('input');
		el.type=t;
		el.name=n;
	}
	
	return el;
};
	
/**
@Name: sb.$.attrConvert
@Description: Used Internally
*/
sb.nodeList.attrConvert = function(attr){
	
		switch(attr){
			
			case 'cellindex':
			attr = 'cellIndex';
			break;
		case 'class':
			attr = 'className';
			break;
		case 'colspan':
			attr = 'colSpan';
			break;
		case 'for':
			attr = 'htmlFor';
			break;
		case 'rowspan':
			attr = 'rowSpan';
			break;
		case 'valign':
			attr = 'vAlign';
			break;
	}

	return attr;
};