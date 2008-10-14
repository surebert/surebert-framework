/**
@Name: sb.flashGate
@Description: Used to include swf for surebert communicate with flash player for sound, multi-file/progress upload and storage.  If you want to use one of these functions on page load, (e.g. play music) make sure to wrap it in the function sb_onFlashGateLoaded so that it fires once sb.flashGate has loaded.
@Author: Paul Visco
@Version: 4.2 02/12/06 09/03/08
#############################
*/
sb.include('swf');
sb.flashGate = new sb.swf({
	src : sb.base+"/FlashGate.swf",
	//src : '${swf}.swf?d='+Math.random(),
	width : 1,
	height : 1,
	bgColor : '#FF0000',
	id : 'Flashgate',
	wmode: 'transparent',
	flashvars : {
		debug : true
	}
});

sb.flashGateContainer = new sb.element({
	tag : 'x',
	styles : {
		display : 'block',
		position : 'absolute',
		left : '-200px',
		top : '-200px'
	}
});

sb.onbodyload.push(function(){
	sb.flashGateContainer.appendToTop('body');
	sb.flashGate.embed(sb.flashGateContainer);
});

/**
 * @Name sb_onFlashGateLoad
 * @Description: Used Internally
 */
sb_onFlashGateLoad = function(){

	if(sb_onFlashGateLoaded && sb_onFlashGateLoaded.forEach ){
		sb_onFlashGateLoaded.forEach(function(v){
			if(typeof v == 'function'){
				v();
			}
		});
	}
	
};