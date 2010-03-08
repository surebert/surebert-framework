/**
@Name: sb.performance
@Description: Determines the time it takes to run a function in milliseconds
@Param: Function func The function to time
@Return: Number The number of milliseconds required to run the function.
@Example:
function getImages(){
	var images = sb.$('img');
}
var timeItTakes = sb.performace(getImages);

*/
sb.performance = function(func){
    var t0 = new Date().getTime();
    func();
    return new Date().getTime() - t0;
};