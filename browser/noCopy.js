/**
 * @Name: sb.browser.noCopy
 * @Description: Causes the browser to reject copying by denying contextmenu and dragstart for elements with sb_nocopy attribute
 */
sb.noCopy = {};
(function(){
	var noCopy = function(e){
		var target = e.target;
		
		if(target.getAttribute('sb_nocopy') ||( target.nodeName== 'A' && target.$("img[sb_nocopy=true]").length())){
			e.stopPropagation();
			e.preventDefault();
		}
	}
	sb.events.add(document, 'contextmenu', noCopy);
	sb.events.add(window, 'dragstart', noCopy);
})();