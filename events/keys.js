/**
@Name: sb.events.keys
@Description: gets key press data for an event making it easy to know which keys were pressed
@Param: Object event An event reference as passed to a handler function as e
@Return: Object A key press object witht the properties listed in the sb.events.keys method
@Example:
var myEvent = sb.events.add('#textArea', 'keydown', function(e){
	var keys = sb.events.keys(e);
	alert(keys.alt); //would be true if alt was pressed during the keypress - other keys listed below
});

*/
sb.events.keys = function(e){
	
	var k, key, pressed, prop;
	key = e.keyCode;
	
	k = {
		pressed : '',
		esc : (key == 27) ? 1 :0,
		ret : (key == 13) ? 1 :0,
		tab : (key ==9) ? 1 : 0,
		shift : (e.shiftKey) ? 1 :0,
		ctrl : (e.ctrlKey) ? 1 :0,
		alt : (e.altKey) ? 1 :0,
		home : (e.keyCode == 36) ? 1 : 0,
		up : (e.keyCode == 38) ? 1 : 0,
		down : (e.keyCode == 40) ? 1 : 0,
		left : (e.keyCode == 37) ? 1 : 0,
		right : (e.keyCode == 39) ? 1 : 0,
		pageUp : (e.keyCode == 33) ? 1 : 0,
		pageDown : (e.keyCode == 34) ? 1 : 0,
		space : (e.keyCode == 32) ? 1 : 0,
		letter : String.fromCharCode(key).toLowerCase()
	};
	
	if(!k.letter.match(new RegExp("\\w"))){
		k.letter ='';
	}
	
	sb.objects.forEach(function(val,prop,o){
		if(val === 1){
			k.pressed = prop;
		}
	});
	
	k.pressed += ' '+k.letter;
	
	return k;
};