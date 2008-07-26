/**
@Name: sb.element.prototype.markText
@Author: Paul Visco
@Version: 2.0 11/17/07
@Description: The method allows you to mark text string within a DOM element.  Great for use with search utility.  It automically puts the foudn text in a <u></u> tag and adds either the className you specify or sb_hiliteText by default
@Param Object o
o.find String The string to match and mark
o.className String The className to use for marking
o.matchExact Boolean 1=matches exactly, 0=matches like etc will mark chicken within chickens, if chicken is the find word
o.caseSensitive Will only match case sensitive
*/
sb.element.prototype.markText = function(o){
	var allTags = $(this, '*');
	var textNodes =[];
	var className = o.className || 'sb_markText';
	var find = o.find || '';
	var matchExact = o.matchExact || 0;
	var caseSensitive = (o.caseSensitive) ? "" : "i";
	allTags.forEach(function(node){
	
		for(x=0;x<node.childNodes.length;x++){
				
			switch(node.nodeName){
				case "IMG":
				case "SCRIPT":
				case "STYLE":
				break;
				default:
				
					if(node.childNodes[x].nodeType ==3 ){
						if(!node.childNodes[x].data.match(/^[\n|\s]$/,''))
						{
							textNodes.push(node.childNodes[x]);
						}
					}	
				break;
			}
		}
	});
	
	textNodes.forEach(function(node){
		
		var sp=new sb.element({
			nodeName : 'span',
			innerHTML : node.data
		});

		var terms = find.split(' ');
		terms.forEach(function(term){
			var re = '';
			var term_match;
			if(term){
				if(matchExact){
					term_match = new RegExp( "\\b("+term+")\\b", caseSensitive+"g");
				} else {
					term_match = new RegExp( "("+term+")", caseSensitive+"g");
				}
				
				sp.innerHTML = sp.innerHTML.replace(term_match, '<u class="'+className+'">'+"$1"+'</u>');
			}
		});
		
		sp.replace(node);
		sp=null;
	});

};