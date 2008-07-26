/**
@Name: sb.browser.removeSelection
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Removes any user based text selection, great for dragging scripts
@Example:
sb.browser.removeSelection();
*/
sb.browser.removeSelection = function(){
	window.setTimeout(function(){
		try{
			if(window.getSelection){
				window.getSelection().removeAllRanges();
			} else if(document.selection){
				document.selection.empty();
			}
		}catch(e){}
	}, 50);
	
};