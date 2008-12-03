/**
@Name: sb.nodeList.prototype.stylesTrail
@Description: Cahnegs the styles of a nodelist in order that they appear in the nodelist on a timeout, and then changes the styles back to how their were
@Example:
$('li').stylesTrail({
	styles : {
		backgroundColor : 'orange',
		borderColor : 'red',
		borderWidth : '2px'
	},
	offset : 6,
	offsetOff : 80,
	onEnd : function(){
		
	}
});
 */

sb.nodeList.prototype.stylesTrail = function(params){
	styles = params.styles || {backgroundColor : 'orange'};
	offset = params.offset || 300;
	offsetOff = params.offsetOff || 80;
	
	var i = 0; 
	var count = this.length;
	var j = 0;
	var self = this;
	
	this.forEach(function(node){
		node._origStyles = {};
		for(prop in styles){
			if(styles.hasOwnProperty(prop)){
				node._origStyles[prop] = node.getStyle(prop);
			}
		}
		
		window.setTimeout(function(){
			for(prop in styles){
				if(styles.hasOwnProperty(prop)){
					node.setStyle(prop, styles[prop]);
				}
			}
			window.setTimeout(function(){
				for(prop in node._origStyles){
					if(styles.hasOwnProperty(prop)){
						node.setStyle(prop, node._origStyles[prop]);
					}
				}
				
				j++;
				
				if(j == count && typeof params.onEnd == 'function'){
					params.onEnd(self);
				}
			}, i+offset);
		}, i);
		
		i += offsetOff;
		
	});

	return this;
};