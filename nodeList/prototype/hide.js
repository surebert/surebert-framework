/**
@Name: sb.nodeList.prototype.hide
@Description: hides all the elements in the nodeList
var nodes = $('img');
nodes.hide()
*/
sb.nodeList.prototype.hide = function(){
	this.nodes.forEach(function(v){
		return v.style.display = 'none';
	});

	return this;
};