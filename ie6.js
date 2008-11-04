if(!Array.prototype.forEach){
	
	/**
	@Name: sb.arrays.js1_5
	@Description: All of these array prototypes are part of Javascript 1.5 and are included by defaut in sureert for browsers that do not have them (IE and Opera).  They are built in by defualt in Firefox(mozilla) and Safari (webkit)
	*/
	sb.arrays.js1_5 = {};
	
	/**
	@Name: Array.prototype.forEach
	@Description: Runs a function on every value in an array
	@Param: Function func An anonymous function or a reference to a function.  Array data is passed to the function for each vlaue in the array.  Values passed are v,k,a which stand for value, key and array.  v is the current value as it loops through the array, k is the current key as it loops through tthe array and a is the entire array.
	@Example:
	function addOne(val,key,arr){
		val = val+1;
		
	}
	var myArray=[1,2,3];
	myArray.forEach(addOne);
	
	//afterwards myArray = [2,3,4]
	*/
	Array.prototype.forEach = function(func){
		var k;
		if(typeof func == 'function'){
			var len = this.length;
			for(k=0;k<len;k++){
				func(this[k], k, this);
			}
		}
	};
	
	/**
	@Name: Array.prototype.filter
	@Description: Filters values out of an array that do not return true from the test function.
	@Param: Function func An anonymous function or a reference to a function.  Array data is passed to the function for each vlaue in the array.  Values passed are v,k,a which stand for value, key and array.  v is the current value as it loops through the array, k is the current key as it loops through tthe array and a is the entire array.
	@Return: Array The new array contains only the values which were true.
	@Example:
	function over10(val, key, arr) {
		if(val > 10){return true;}
	}
	
	var myArray = [5, 10, 15];
	var newArray = myArray.filter(over10);
	//returns the array 10,15 because those two values are >=10
	
	*/
	Array.prototype.filter = function(func){
		var n=[];
		if(typeof func == 'function'){
			this.forEach(function(v,k,arr){
				if(func(arr[k], k, arr) === true){
					n.push(v);
				}
			});
		}
		
		return n;
		
	};
	
	/**
	@Name: Array.prototype.every
	@Description: Checks to see if every value in an array returns true from the function provided
	@Param: Function func An anonymous function or a reference to a function.  Array data is passed to the function for each vlaue in the array.  Values passed are v,k,a which stand for value, key and array.  v is the current value as it loops through the array, k is the current key as it loops through tthe array and a is the entire array.
	@Return: Boolean True or False
	@Example:
	
	function over10(val, key, arr) {
		if(val > 10){return true;}
	}
	
	var myArray = [5, 10, 15];
	myArray.every(over10);
	returns false because not every number in the array is over 10
	
	*/
	Array.prototype.every = function(func){
		var k;
		if(typeof func == 'function'){
			var len = this.length;
			for(k=0;k<len;k++){
				
				if(func(this[k], k, this) !== true){
					
					return false;
				}
			}
			return true;
		}
	};
	
	
	/**
	@Name: Array.prototype.indexOf
	@Description: Finds the index of the value given within the array.  Return the position of the first matching value.  Rememeber that array start at 0.
	@Param: Object/String/Number val The value you want to search for in the array. 
	@Return: Integer
	@Example:
	
	var myArray = [1,2,3,'a','b'];
	var answer = myArray.indexOf('a');
	//answer is 3
	
	*/
	Array.prototype.indexOf = function(val){
		var len = this.length;
		for(var k=0;k<len;k++){
			if(this[k] == val){
				return k;
			}
		}
		return -1;
	};
	
	/**
	@Name: Array.prototype.lastIndexOf
	@Description: Finds the last index of the value given within the array.Rememeber that array start at 0.
	@Param: Object/String/Number val The value you want to search for in the array. 
	@Return: Integer
	@Example:
	
	var myArray = [1,2,3,2];
	var answer = myArray.lastIndexOf(2);
	//answer is 3
	*/
	Array.prototype.lastIndexOf = function(val){
		var p=-1,k;
		var len=this.length;
		for(k=0;k<len;k++){
			if(this[k] == val){
				p=k;
			}
		}
		return p;
	};
	
	/**
	@Name: Array.prototype.map
	@Description: Runs a function on every item in the array and returns the results in an array.
	@Param: Function func The function you want applied run on every value in the array.  It is automatically passed the current (value, key, and array) as arguments on eqach loop through the array.  The function can be either a reference to a global function or an inline anonymouse function.
	@Return: Array A new array with each value mapping to the result of the original arrays value after is is passed through the function specified.
	@Example:
	function addTen(val, key, array) {
		return val+10;
	}
	
	var myArray = [5, 10, 15];
	var answer = myArray.map(addTen);
	//answer = [15, 20, 25];
	
	*/
	Array.prototype.map = function(func){
		var n=[];
		if(typeof func == 'function'){
			this.forEach(function(v,k,a){n.push(func(v,k,a));});
		}
		return n;
	};
	
	Array.prototype.some = function(func){
		
		var k;
		if(typeof func == 'function'){
			var len = this.length;
			for(k=0;k<len;k++){
				if(func(this[k], k, this) === true){
					return true;
				}
			}
			return false;
		}
	};

}

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
			images = sb.$(obj, 'img');
		}
		
		for(i=0;i<images.length;i++){	
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

			el = sb.s$(el);
		
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