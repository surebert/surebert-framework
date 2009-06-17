/**
@Name: Element.prototype.onHover
@Author: Paul Visco
@Version: 0.1 5-21-09 06-03-09
@Description: Fires an event when the user hovers over and element
@Param: Function func The function to fire onhover, the this is the element itself
@Param: integer interval The number of millseconds between firing
@Return: returns A hovering object with properties interval, timer, events, and methods unobserve, observe, and hover
@Example:
var hovering = myElement.onHover(function(){this.innerHTML = new Date();}, 200);
//OR
var hovering = $('#my_node').onHover(app.colorPicker.updatePalette, 100);

//force hover - takes optional interval argument
hovering.hover();

//force hover stop
hovering.mouseout();

//stop observing hover
hovering.unobserve();

//start observing hover again  - takes optional interval argument
hovering.overserve();
*/

Element.prototype.onHover = function(func, interval){


    var el = this;
    var hovering = function(){
        this.interval = interval || 500;
        this.events = [];
        this.observe();

    };

    hovering.prototype = {
        unobserve : function(){
            this.mouseout();
            for(var x=0;x<this.events.length;x++){
                sb.events.remove(this.events[x]);
            }
        },
        observe : function(interval){
            this.interval = interval || this.interval;
            var self = this;
            this.unobserve();
            this.events.push(el.event('mouseover', function(){self.hover();}));
            this.events.push(el.event('mouseout', function(){self.mouseout();}));
        },

        hover : function(interval){
            this.interval = interval || this.interval;
            this.mouseout();
            this.timer = window.setInterval(function(){
                if(typeof func == 'function'){
                    func.call(el);
                }
            }, this.interval);
        },

        mouseout : function(){
            if(this.timer){
                window.clearInterval(this.timer);
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