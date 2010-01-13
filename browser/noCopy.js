/**
 * @Name: sb.browser.noCopy
 * @Description: Causes the browser to reject copying by denying contextmenu and dragstart for elements with sb_nocopy attribute
 */
sb.browser.noCopy = {};
(function(){
	var noCopy = function(e){
		var target = e.target;
		var a = 'sb_nocopy';
		if(target.getAttribute(a) ||( target.nodeName== 'A' && target.$('img['+a+'="true"]').length())){
			e.stopPropagation();
			e.preventDefault();
		}
	}
	sb.events.add(document, 'contextmenu', noCopy);
	sb.events.add(window, 'dragstart', noCopy);
})();