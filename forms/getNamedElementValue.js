/**
@Name: sb.forms.getNamedElementValue
@Version: 1.0 06-03-09 06-03-09
@Description: returns the value of a radio button based on the name of the inpiut
@Param: String name The name of the radio element

@Return: String The value of the radio button
@Example:

var val = sb.forms.getNamedElementValue("search_form");
*/
sb.forms.getNamedElementValue = function(name){

    var inputs = document.getElementsByName(name);
    var x=0;
    var len = inputs.length;
    for(x;x<len;x++){
        if(inputs[x].checked){
            return inputs[x].value;
        }
    }

    return false;
};