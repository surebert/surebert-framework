/**
@Name: Element.prototype.onHover
@Author: Paul Visco
@Version: 0.11 5-21-09 09-08-09
@Description: Fires an event when the user hovers over and element
@Param: o Object
o.start Function The function to fire when you start hovering
o.stop Function The function to fire when you stop hovering
@Param: integer interval The number of millseconds between firing
@Return: returns A hovering object with properties interval, timer, events, and methods unobserve, observe, and hover
@Example:
var hovering = myElement.onHover(function(){this.innerHTML = new Date();}, 200);
//OR
var hovering = $('#my_node').onHover({
    onStart : app.colorPicker.updatePalette,
    onStop : function(){
        document.title = new Date();
    },
    interval : 100
});

//force hover - takes optional interval argument
hovering.hover();

//force hover stop
hovering.hoverstop();

//stop observing hover
hovering.unobserve();

//start observing hover again  - takes optional interval argument
hovering.overserve();
*/

Element.prototype.onHover = function(o){

    var el = this;
    var hovering = function(){
        this.interval = o.interval || 500;
        this.events = [];
        this.observe();
        this.onStart = o.onStart;
        this.onStop = o.onStop;
    };

    hovering.prototype = {
        unobserve : function(){
            this.hoverstop();
            for(var x=0;x<this.events.length;x++){
                sb.events.remove(this.events[x]);
            }
        },
        observe : function(interval){
            this.interval = interval || this.interval;
            var self = this;
            this.unobserve();
            this.events.push(el.evt('mouseover', function(){self.hover();}));
            this.events.push(el.evt('mouseout', function(){self.hoverstop();}));
        },

        hover : function(interval){
            var t = this;
            t.interval = interval || t.interval;
            t.hoverstop();
            
            t.timer = window.setInterval(function(){
                if(typeof t.onStart == 'function'){
                    t.onStart.call(el);
                }
            }, t.interval);
        },

        hoverstop : function(){
            var t=this;
            if(t.timer){
                window.clearInterval(t.timer);
                if(typeof t.onStop == 'function'){
                    t.onStop.call(el);
                }
                
            }
        }
    };

    //make sure to stop if hovering when dom element is removed
    if(el.remove == sb.element.prototype.remove){
        var remove = el.remove;

        el.remove = function(){
            hovering.unobserve();
            remove.call(el);
        }

    }


    return new hovering();

};