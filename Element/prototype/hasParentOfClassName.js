/**
@Name: Element.prototype.hasParentOfClassName
@Author: Paul Visco
@Version: 0.1 05-10-09 06-03-09
@Description: Searches the node itself, followed by parentnodes incrementally till it find a node of the specified className
@Param: string className The className to look for
@Return: sb.elementChecks to see if the that has the className or false if not found

@Example:
//would check if mySbElement has the className highlighted and if not search parentNodes till it found one that did or returned false
mySbElement.isOrHasParentOfClassName('highlighted');

*/
Element.prototype.hasParentOfClassName = function(className){

    var ret = false;
    var parent = this;
    while(parent = sb.$(parent.parentNode)){
        if(parent.hasClassName && parent.hasClassName(className)){
            return parent;
        }
    }
    return ret;

};