/**
@Name: Element.prototype.isOrHasParentOfClassName
@Description: Searches the node itself, followed by parentnodes incrementally till it find a node of the specified className
@Param: string className The className to look for
@Return: sb.element The element itself or the parentNode that has the className or false if not found

@Example:
//would check if mySbElement has the className highlighted and if not search parentNodes till it found one that did or returned false
mySbElement.isOrHasParentOfClassName('highlighted');

*/
Element.prototype.isOrHasParentOfClassName = function(className){

    if(this.hasClassName(className)){
	return this;
    }

    var ret = false;
    var parent = this;
    while(parent = $(parent.parentNode)){
	if(parent.hasClassName && parent.hasClassName(className)){
	    return parent;
	}
    }
    return ret;

};