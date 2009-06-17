/**
@Name: Element.prototype.onDelayedKeyup
@Author: Paul Visco
@Version: 0.1 06-17-09 06-17-09
@Description: Fires after a delay on keyup.  New keypresses restart the count.
Great for when you have a realtime ajax search but want to wait until after
the user finishes typing.
@Param: o Object
o.onAfterDelay function The function to fire after the delay.  The this is the element.  The e arg is the event
o.onKeyUp function The function to fire on keyup.  The this is the element.  The e arg is the event
o.delay integer The delay in milliseconds to wait before firing onAfterDelay
o.debug boolean If timer is sent to console in browsers that support it
@Return: object r With the following properties:
r.onAfterDelay() function which can be changed at any point
r.onKeyUp() function which can be changed at any point
r.observe(); function start or restart observing keypresses
r.unobserve(); function stop observing keypresses
@Example:
var onDelayedKeyUp = $('#search_main').onDelayedKeyup({
    delay : 500,
    onAfterDelay : function(e){
        //if the value is more than one character
        if(this.value.length > 3){

            //do something app specific
            search(this.value);
        }

    },
    onKeyUp : function(e){
         if(e.keyCode == 27 || this.value == ''){
            //do something app specific
            clearSearch();


            return false;
        }
    }
});
*/
Element.prototype.onDelayedKeyup = function(o){

    if(!o.onAfterDe)
    var el = this;
    var ret = {
        onAfterDelay : o.onAfterDelay,
        onKeyUp : o.onKeyUp,
        delay : Math.round(o.delay/10) || 50,
        timer : false,
        debug : o.debug,
        count : 0,
        unobserve : function(){
            window.clearTimeout(this.timer);
            sb.events.remove(this.keyup);
        },

        observe : function(){

            if(this.keyup){return;}

            this.keyup = el.event('keyup', function(e){

                if(typeof ret.onKeyUp == 'function'){
                    ret.onKeyUp.call(el, e);
                }

                ret.count = 0;

                if(!ret.timer){

                     ret.timer = window.setInterval(function(){
                        ret.count++;

                        if(ret.debug && console){
                            console.log(ret.count);
                        }

                        if(ret.count > 10){

                             window.clearTimeout(ret.timer);

                             if(typeof ret.onAfterDelay == 'function'){

                                 ret.onAfterDelay.call(el, e);
                             }

                             ret.timer = null;
                             ret.count = 0;
                        }
                    }, ret.delay);
                }
            });
        }
    };

    ret.observe();

    return ret;

};