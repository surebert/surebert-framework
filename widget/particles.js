/**
@Name: sb.widget.particles
@Description: makes floating particles that float around the screen
Math for floating pattern from code by Altan d.o.o. (snow@altan.hr, http://www.altan.hr/snow/index.html)

XHTML compliant will work in when using DOCTYPE XHTML

@Author: Paul Visco 11-05-04, 11-15-07
@Example: 
sb.include('widget.particles');
sb.widget.particles.init(5, 'hello');

*/

sb.widget.particles = {
	total : 5,
	removed : 0,
	stopAnimation : 0,
	content : '',
	sound : '',
	particles : [],
	dx : [],
	xp : [],
	yp : [],
	am : [],
	stx : [],
	sty : [],
	playSound : function(){
		if(sb.widget.particles.sound !==''){
			var snd = new sb.sound(sb.widget.particles.sound).play();
		}
	},
	
	container : new sb.element({
		tag : 'sb_particles',
		styles : {
			display : 'block'
		},
		events : {
			mousedown : function(e){
				var target = sb.events.target(e);
				if(target.nodeName =='SB_PARTICLE' || (target.parentNode && target.parentNode.nodeName =='SB_PARTICLE')){
					sb.widget.particles.playSound();
					target.remove();
							
					sb.widget.particles.removed++;
					
					if(sb.widget.particles.removed >= sb.widget.particles.total){
						sb.widget.particles.stopAnimation =1;
					}
					
					
				}
			}
		}
		
	}),
	
	hideParticles : function(){
		this.floats.hide();
	},
	
	animate : function(){
		if(this.stopAnimation ==1){return;}
		
		for (var i = 0; i < this.particles.length; ++ i) { 
				
			this.yp[i] += this.sty[i];
			if (this.yp[i] > sb.browser.h-50) {
				
				var rand = Math.random();
				this.xp[i] = rand*(sb.browser.w-this.am[i]-30);
				this.yp[i] = 0;
				this.stx[i] = 0.02 + rand/10;
				this.sty[i] = 0.7 + rand;
				
				sb.widget.particles.playSound();
			}
		
			this.dx[i] += this.stx[i];
			var x= this.xp[i] + this.am[i]*Math.sin(this.dx[i]);
			var y = this.yp[i];
			this.particles[i].style.fontSize=this.sty[i]*20+'px';
			this.particles[i].mv(x,y);
				
		}
		var self = this;
		this.animating = window.setTimeout(function(){self.animate();}, 30);
			
	},
	
	create : function(){
		
		for (var i = 0; i < this.total; ++ i) {  
			this.dx[i] = 0;                       		 // set coordinate variables
			this.xp[i] = Math.random()*(sb.browser.w-50);  // set position variables
			this.yp[i] = Math.random()*sb.browser.h;
			this.am[i] = Math.random()*50;         // set amplitude variables
			this.stx[i] = 0.03 + Math.random()/10; // set step variables
			this.sty[i] = 0.7 + Math.random();     // set step variables
			
			var particle = new sb.element({
				tag : 'sb_particle',
				innerHTML : this.content,
				title : 'click to remove',
				styles: {
					cursor : 'pointer',
					display:'block'
				}
			});
			
			particle.mv(15, 15, i+1);
			this.particles.push(particle);
			particle.appendTo(this.container);
			
		}
		
		
	},
	
	init : function(total, content, sound){
		this.sound = sound ||'';
		this.total = total || this.total;
		this.content = content;
		this.container.appendTo('body');
		this.create();
		this.animate();
		
	}
};