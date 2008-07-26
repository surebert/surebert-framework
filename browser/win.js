sb.browser.win = function(href, width, height, name){
	name = name || 'docwin';
	var w = window.open(href, name,"width="+width+",height="+height+",toolbar=0,scrollbar=1,resizable=1");
	w.focus();
	return w;
};