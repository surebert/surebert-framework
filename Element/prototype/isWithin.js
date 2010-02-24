sb.include('Element.prototype.containsElement');
/**
@Name: Element.prototype.containsNode
@Author: Paul Visco
@Version: 0.1 5-21-09 06-03-09
@Description: Determines if this element is within another
@Param: b The other element to determine if this element is within
@Return: returns boolean If element b is within this element
@Example:
$('#my_element').isWithin('#another_element');
*/
Element.prototype.isWithin = function(b){
    var b = sb.$(b);
    if(b && b.containsElement){
        return b.containsElement(this);
    }

    return false;
};