/**
@Name: sb.nodeList.prototype.show
@Description: shows all the elements in the nodeList
var nodes = $('img');
nodes.show()
*/
sb.nodeList.prototype.show = function(){
	this.nodes.forEach(function(v){
		return v.style.display = '';
	});

	return this;
};