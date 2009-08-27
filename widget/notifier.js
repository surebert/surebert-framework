sb.include('Element.prototype.cssTransition');

/**
@Name: sb.widget.notifier
@Description: provides growl like messages for user
@Version: 1.0 08-06-2009
@Example:
//add this css
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

//then
sb.widget.notifier.init();
*/
sb.widget.notifier = {

    box : null,

    moveCounter : 0,

    clearMethod : function(){

        var el = this;

        el.style.zIndex = 999;
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
           id : 'sb_notifications',
           styles : {
               position : 'absolute',
               zIndex : 999,
               top : '0px',
               right : '0px',
               minWidth : '500px'
           }
        });

        this.box.appendToTop('body');
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
    @Param: effect object Optional Can override the properties of the effect used to move the boxes
        effect.heightTween string The height tween method
        effect.opacityTween string The opacity tween method
        effect.duration integer The duration of the fade out, roll up
        effect.onEnd function A function which fires when the message is done fading out
    @Example:
    sb.widget.notifier.notify('Hello World '+new Date());
	*/
    notify : function(message, className, effect){

        className = ' '+className || '';

        var el = new sb.element({
            tag : 'div',
            className : 'sb_notify'+className,
            innerHTML : message,
            effect : effect || {}
        });

        el.appendToTop(this.box);
        var t = this;
        el.clearing = window.setTimeout(function(){
            t.clearMethod.call(el);
        }, 4000);


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
            var x = parseInt(t.box.style.right, 10);
            t.box.style.zIndex = 999;
            t.sliding = t.box.cssTransition([{
                prop : 'top',
                begin : y,
                change : pos[1]-y,
                onEnd : function(){
                    t.box.style.zIndex = 999;
                },
                tween : 'inQuad'
            },{
                prop : 'right',
                begin : x,
                change : -pos[0]-x,
                onEnd : function(){
                    t.box.style.zIndex = 999;
                },
                tween : 'inQuad'
            }], 24).start();

        }, 100);

    },

    /**
	@Name: sb.widget.notifier.init
	@Description: Looks for sb_notifications box, if not found, creates and appends to the top of body tag
	*/
    init : function(){
        this.box = $('#sb_notifications');

        if(!this.box){
            this.createBox();
        }

        this.box.style.zIndex = 999;

        var t = this;
        sb.events.add(window, 'scroll', function(e){
            t.followScrollBars(e);
        });

    }
};