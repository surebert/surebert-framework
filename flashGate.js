/**
@Name: sb.flashGate
@Description: Used to include swf for surebert communicate with flash player for sound, multi-file/progress upload and storage.  If you want to use one of these functions on page load, (e.g. play music) make sure to wrap it in the function sb_onFlashGateLoaded so that it fires once sb.flashGate has loaded.
@Author: Paul Visco
*/
sb.include('swf');
sb.flashGate = new sb.swf({
	src : sb.base+"FlashGate.swf",
	//src : '${swf}.swf?d='+Math.random(),
	width : 1,
	height : 1,
	bgColor : '#FF0000',
	id : 'Flashgate',
	"swLiveConnect" : "true",
	flashvars : {
		debug : true
	}
});

sb.flashGate.debug = 0;

sb.flashGateContainer = new sb.element({
	tag : 'x',
	styles : {
		display : 'block',
		position : 'absolute',
		left : '-50px',
		top : '-50px'
	}
});

var sb_onFlashGateLoaded = [];

sb.dom.onReady({
	id : 'body',
	onReady : function(){
		sb.flashGateContainer.appendToTop('body');
		sb.flashGate.embed(sb.flashGateContainer);
		sb_onFlashGateLoaded.forEach(function(v){
			if(typeof v == 'function'){
				v();
			}
		})
	},
	tries : 600,
	ontimeout : function(){
		if(sb.flashGate.debug){
			throw('Cannot append flashGate to browser');
		}
	}
});