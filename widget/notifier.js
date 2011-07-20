sb.include('Element.prototype.cssTransition');
sb.include('String.prototype.md5');
/**
@Name: sb.widget.notifier
@Description: provides growl like messages for user
@Version: 1.0 08-06-2009
@Example:
//add this css
#sb_notifications{
    position:absolute;
    right:10px;
    top:10px;
    z-index:999;
}

.sb_notify{
    width:500px;
    border:1px solid #ACACAC;
    color:#333;
    background-color:#DFDFDF;
    padding:10px;
    margin:5px;
    opacity:0.9;
    font-size:1.5em;
}

.sb_notify .sb_close{
	background-image:url('/surebert/load/_media/x.png');
	background-repeat:no-repeat;
	font-size:0px;
	width:20px;
	height:20px;
	margin:-15px 0 0 -20px
}

//then
sb.widget.notifier.init();
*/
sb.widget.notifier = {

    box : null,

    moveCounter : 0,

	timeout : 15000,

    clearMethod : function(){

        var el = this;
        if(this.clearing){
            window.clearTimeout(this.clearing);
        }
        
        el.style.zIndex = 60000;
        var height = el.offsetHeight;
        height = height-(parseInt(el.getStyle('padding'), 10));
        height = height-(parseInt(el.getStyle('border'), 10));
        height = height-(parseInt(el.getStyle('margin'), 10));

        el.style.overflow='hidden';

        if(el.resizing){
            el.resizing.stop();
        }
        el.resizing = el.cssTransition([
            {
                prop : 'height',
                begin : height,
                change : -height,

                tween : el.effect.heightTween || 'inQuad'
            },
            {
                prop : 'opacity',
                begin : 1,
                change : -1,
                onEnd : function(){

                    el.style.padding='0';
                    el.style.border='0';
                    el.remove();
                    if(typeof el.effect.onEnd == 'function'){
                        el.effect.onEnd(el);
                        el.remove();
                        el = null;
                    }
                },
                tween : el.effect.opacityTween || 'inCubic'
            }
        ], el.effect.duration || 48).start();
    },

    createBox : function(){
        this.box = new sb.element({
           tag : 'div',
           id : 'sb_notifications'
        });
		
        this.box.appendToTop(document.body);
    },

    /**
	@Name: sb.widget.notifier.clearAll
	@Description: Used to clear any open notifcation messages
    @Example:
    sb.widget.notifier.clearAll();
	*/
    clearAll : function(){
        var t = this;
        this.box.$('.sb_notify').forEach(function(v){
            t.clearMethod.call(v);
		});
    },

    /**
	@Name: sb.widget.notifier.notify
	@Description: All user notifications feedback goes through this function.
    @Param: message string The message to be displayed, can be plain text or HTML
    @Param: className string Optional The CSS className to assign to the message
	@Param: stay boolean Optional Determines if box stays or fade by default
    @Param: effect object Optional Can override the properties of the effect used to move the boxes
        effect.heightTween string The height tween method
        effect.opacityTween string The opacity tween method
        effect.duration integer The duration of the fade out, roll up
        effect.onEnd function A function which fires when the message is done fading out
    @Example:
    sb.widget.notifier.notify('Hello World '+new Date());
	*/
    notify : function(message, className, stay, effect){

        className = ' '+className || '';
        var t = this,id;

		id = 'i'+((message+className).md5());
        if($('#'+id)){
			return;
		}
		
        var el = new sb.element({
            tag : 'div',
            className : 'sb_notify'+className,
            innerHTML : message,
			id : id,
            effect : effect || {}
        });


        el.clear = function(){
            t.clearMethod.call(el);
        };

        el.appendToTop(this.box);
		if(!stay){
			el.clearing = window.setTimeout(el.clear, sb.widget.notifier.timeout);
		}
        return el;

    },

    /**
	@Name: sb.widget.notifier.followScrollBars
	@Description: Used Internally. All user notifications feedback goes through this function.
	*/
    followScrollBars : function(e){

        var t = this;
        if(t.moving){
            window.clearTimeout(t.moving);
        }

        t.moving = window.setTimeout(function(){
            var pos = sb.browser.getScrollPosition();
			
            if(t.sliding){
                t.sliding.stop();
            }
            var y = t.box.getY();
            var x = parseInt(t.box.style.right, 10) || 0;
			
            t.box.style.zIndex = 60000;
            t.sliding = t.box.cssTransition([{
                prop : 'top',
                begin : y,
                change : pos[1]-y,
                onEnd : function(){
                    t.box.style.zIndex = 60000;
                },
                tween : 'inQuad'
            },{
                prop : 'right',
                begin : x,
                change : -pos[0]-x,
                onEnd : function(){
                    t.box.style.zIndex = 60000;
                },
                tween : 'inQuad'
            }], 24).start();

        }, 100);

    },

    /**
	@Name: sb.widget.notifier.init
	@Description: Looks for sb_notifications box, if not found, creates and appends to the top of body tag
	*/
    init : function(o){
		o = o || {};
        this.box = sb.$('#sb_notifications');
		
        this.events = o.events || {};
        if(!this.box){

            this.createBox();
        }

        this.box.style.zIndex = 60000;
        var t = this;
        this.box.events(this.events);

        var t = this;
        sb.events.add(window, 'scroll', function(e){
            t.followScrollBars(e);
        });

    }
};

sb.widget.notifier.init({
	events : {
		click : function(e){
			var parent = e.target.parentNode;
			if(parent && typeof parent.clear == 'function'){
				parent.clear();
			}
		}
	}
});

/**
@Name: rp.notify
@Description: All user notifications feedback goes through this function.
@Example:
sb.notify('hello world', 'success');
*/
sb.notify = function(message, type, stay, effect){
	if(typeof sb.widget.notifier.box == 'null'){sb.widget.notifier();}
	type = type || 'message';
	return sb.widget.notifier.notify('<div class="sb_close"></div> '+message, type, stay, effect);
};