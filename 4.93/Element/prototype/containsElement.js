/**
@Name: Element.prototype.containsElement
@Author: Paul Visco
@Version: 0.1 5-21-09 06-03-09
@Description: Determines if this this element contains another.  Inspired by John Resig blog
@Param: b The other element to check if for
@Return: returns boolean If this contains b
@Example:
$('#one_element').containsElement('#another_element');
*/
Element.prototype.containsElement = function(b){
    b = sb.$(b);

    return this.contains ?
    this != b && this.contains(b) :
    !!(this.compareDocumentPosition(b) & 16);
};