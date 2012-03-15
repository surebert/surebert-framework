/**
@Name: Element.prototype.getAncestors
@Description: Looks up all ancestors of the sb.element and places them in an array after turning them into sb.elements themselves
@Param: sb.element within A reference to another node, where the recurive ancestor collection should stop.  If not set, ancestor list will go up to body tag.  Otherwise, it will stop at whatever you use as the within argument.
@Return: array of sb.elements

@Example:
//get all ancestors
mySbElement.getAncestors();
//get all ancestors within (up to) myOtherDiv
mySbElement.getAncestors(myOtherSbElement);

*/
Element.prototype.getAncestors = function(within){
	
	if(within){
		within = sb.$(within);
	}
	
	var ancestors =[], el=this;
	
	do{
		if(typeof el =='object' && el.parentNode && el.parentNode.nodeName !='HTML' && el.parentNode !=document && el.parentNode != within){
			el = el.parentNode;
			ancestors.push(sb.$(el));
		} else {
			el = false;
		}
		
	} while(el);
	
	return ancestors;
};