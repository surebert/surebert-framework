sb.include('effect');
sb.include('Element.prototype.cssTransition');
sb.include('colors.getTweenColor');
sb.include('String.prototype.rgb2hex');

/**
@Name:  Element.prototype.highlight
@Author: James Lindley
@Version: 1.0
@Description: This effect is used to briefly highlight an element, returning to the original background color afterwards.
@Param: string    startColor  The highlight color. Defaults to light yellow.
@Param: string    endColor    The color the background will be at the end of the effect (defaults to existing background color)
@Param: integer   duration    The time milliseconds to highlight the element. Defaults to 1/3 of a second.
@Param: function  onEnd       Callback for after the highlight is over.
Example: 
// Highlight an element via a red background, and fade back to normal over 0.5 seconds.
var highlight = $('#new_element').highlight({startColor : '#FF9999', duration : 500, onEnd : function(){
	alert('done');
}});
*/
Element.prototype.highlight = function(o){
	if(this.highlighting){
		this.highlighting.stop();
	}

    options = o || {}
	
	var highlightDuration   = options.duration   || 333;
	var highlightStartColor = options.startColor || '#FFFF66';
	var highlightEndColor   = options.endColor   || 0;
	var highlightOnEnd      = options.onEnd      || 0;

    // Retrieves hex value of css color, from hex, rgb, or word color values
    var colorInterpreter = function(colorString) {
        if (colorString == 'transparent') {
            return 'transparent';

        } else if (colorString.rgb2hex()) {
            return colorString.rgb2hex(); // Handle Firefox's rgb notation

        } else if (colorString.substring(0,1) == '#') {
            return colorString; // Hex color already

        } else if (colorString.match(/\w/)){
            // HTML color words
            sb.include('colors.html');
            if (sb.colors.html[colorString]) {
                return (sb.colors.html[colorString] || '#FFFFFF');
            }            

        } else {
            return 'transparent'; // Default to a transparent background.
        }            
    }
	
    // Set final color of effect
    if(! this.sb_highlightEndColor) {
        if (highlightEndColor) {
            this.sb_highlightEndColor = highlightEndColor;
        } else {
            var effectEndColor = colorInterpreter(this.style.backgroundColor)

            if (effectEndColor == 'transparent') {
                // Find a parent node with a background color
                var currentNode = this.parentNode;
                while((!currentNode.style.backgroundColor) && currentNode.tagName != 'BODY') {
                    currentNode = this.parentNode;
                }
                effectEndColor = colorInterpreter(currentNode.style.backgroundColor);

                // if still transparent, revert to white (math for transitions 
                // require an actual color value, otherwise it blows up)
                if (effectEndColor == 'transparent') {
                    effectEndColor = '#FFFFFF';
                }
            }      
            // Save the element background/final color in case the effect is called overlapping times.
            this.sb_highlightEndColor = effectEndColor;
        }
    }

	this.highlighting = this.cssTransition([
		{
			prop   : 'backgroundColor',
			begin  : highlightStartColor,
			end    : this.sb_highlightEndColor,
            onEnd  : highlightOnEnd
		}
	], highlightDuration);
	
	this.highlighting.start();
	return this.highlighting;
};