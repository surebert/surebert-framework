/**
@Name:  Element.prototype.rollUp
@Author: Paul Visco
@Version: 1.0 05/27/09
@Description: This effect rolls up an element while fading and then removes it
Example: div.rollUp({onEnd:function(){alert('d');}});
*/
Element.prototype.rollUp = function(o){

	o = o || {duration : 24};

	this.style.overflow='hidden';
	var el = this;
	var transitions = [];

	var height = this.offsetHeight;
	if(el.nodeName == 'TR'){
		var chs = el.$('td');
		chs.forEach(function(v){
			v.innerHTML = '';
		});
	}
	
	transitions.push({
		prop : 'height',
		begin : height,
		change : -height,

		tween : 'inQuad'
	});

	transitions.push({
		prop : 'opacity',
		begin : 1,
		change : -1,
		onEnd : function(){

			el.style.padding='0';
			el.style.border='0';
			el.remove();
			if(typeof o.onEnd == 'function'){
				o.onEnd(el);
			}
		},
		tween : 'inCubic'
	});

	if(this.resizing){
		this.resizing.stop();
	}
	this.resizing = this.cssTransition(transitions, o.duration || 48);


	this.resizing.start();
	return this.resizing;

};