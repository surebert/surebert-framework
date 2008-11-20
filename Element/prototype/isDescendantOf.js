/**
@Name: Element.prototype.isDescendantOf
@Description: Checks to see if the element is a child of whatever element it is passed. DOES NOT RETURN ITSELF LIKE OTHER Element.prototypes
@Param: Object/String of You can specify the parent element as an id string e.g. #parent or as an element object reference
@Return: Boolean True is the element is a child of the parent specified and false if it is not
@Example:
myElement.isDescendantOf('#parent');
*/

Element.prototype.isDescendantOf = function(el){

	return $(el, '*').nodes.inArray(this);
};