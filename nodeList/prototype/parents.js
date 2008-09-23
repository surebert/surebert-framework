/**
@Name: sb.nodeList.prototype.parents
@Description: changes the nodelist to be the parents of the nodes instead of themselves
var nodes = s$('ol li');
//adds element with id 'wrapper' to the node list
nodes.add('#wrapper');
//add all the links to the nodeList
nodes.add('a');
*/
sb.nodeList.prototype.parents = function(){
	this.nodes = this.nodes.map(function(v){
		return v.parentNode;
	});
	
	return this;
};