/**
@Name: Element.prototype.swap
@Description: Swaps one element with another
@Return: returns self and other element in an array
@Example:
myElement.swap(otherElement);
*/
Element.prototype.swap = function(b){
    b = sb.$(b);
    if(b && b.cloneNode){
        a2 = this.cloneNode(true);
        b2 = b.cloneNode(true);

        this.parentNode.replaceChild(b2, this);
        b.parentNode.replaceChild(a2, b);

        return [a2,b2];
    } else {

        throw('cannot swap node');
    }


    //actuals switch instead of clones
    a2.parentNode.replaceChild(this, a2);
    b2.parentNode.replaceChild(b, b2);
    return [this,b];
};