sb.include('Element.prototype.getNextSibling');



sb.nodeList.prototype.getLastChildren = function(){
	var nl = new sb.nodeList();
	nl.nodes = this.nodes.filter(function(n){
		var ns = n.getNextSibling();
		if(ns == null){
			return n;
		}

	});

	return nl;
};

sb.include('Element.prototype.getPreviousSibling');


sb.nodeList.prototype.getFirstChildren = function(){
	var nl = new sb.nodeList();
	nl.nodes = this.nodes.filter(function(n){
		var ns = n.getPreviousSibling();
		if(ns == null){
			return n;
		}

	});

	return nl;
};

sb.nodeList.prototype.addClass = function(classname){

	this.nodes.forEach(function(n){
		n.className = n.className+' '+classname;

	});

	return this;
};

sb.nodeList.prototype.hide = function(){

	this.nodes.forEach(function(n){
		n.style.display = 'none';

	});

	return this;
};

sb.nodeList.prototype.show = function(){

	this.nodes.forEach(function(n){
		n.style.display = '';

	});

	return this;
};

if(!console){
	var console = {
		log : function(x){

		}
	};
}


sb.widget.tree = function(params){
	sb.objects.infuse(params, this);
	this.dynamicLoadURL = this.dynamicLoadURL || '';
	this.root = sb.$(this.root);
	this.root.addClassName('sb_tree');

	this.addClasses(this.root);

	this.collapse();
	var self = this;
	this.root.events({

		click : function(e){
			var target = e.target;

			if(typeof self.onClick == 'function'){
				if(self.onClick(e) === false){
					return false;
				};
			}

			var node = target.nodeName == 'LI' ? target : target.getContaining('li');

			if(target.nodeName == 'SPAN' && target.className.match(/expand/)){

				if(self.onToggle(e, node) === false){
					return false;
				}

				if(target.className.match(/expand contract/)){
					target.className = 'expand';
				} else {
					target.className = 'expand contract';
					if(node.getAttribute('sb_dynamicLoad')){
						self.onDynamicLoad(e, node);
						return false;
					}
				}

				sb.toArray(target.parentNode.childNodes).forEach(function(v){

					if(v.nodeName == 'UL'){
						v.toggle();
					}
				});

			} else {
				if(self.onNodeClick(e, node) === false){
					return false;
				};
			}
		}
	});

};

sb.widget.tree.prototype = {

	addClasses : function(ul){
		var self = this;
		var nodes = ul.$('li');

		nodes.getLastChildren().addClass('last');

		//iterate through all list items
		nodes.forEach(function(li){

			//if list-item contains a child list

			/*li.onselectstart = function() {
				return false;
			};
			li.unselectable = "on";
			li.style.MozUserSelect = "none";*/
			if ( li.$('ul').length() > 0 || li.getAttribute('sb_dynamicLoad')) {
				li.addClassName('root');

				if(self.draggable){
					if(!li.dragHandle){
						//add expand/contract control
						li.dragHandle = new sb.element({
							tag : 'span',
							className : 'draghandle',
							innerHTML : ' '
						});

						li.dragHandle.appendToTop(li);
					}
				}

				if(!li.expander){


					//add expand/contract control
					li.expander = new sb.element({
						tag : 'span',
						className : 'expand',
						innerHTML : ' '
					});

					li.expander.appendToTop(li);

				}
			}

		});
	},
	expand : function(){
		this.root.$('ul').show();
	},

	collapse : function(){
		this.root.$('ul').hide();
	},

	addSubList : function(node, html, open){

		node = sb.$(node);
		//remove the old sublist
		node.$('ul').forEach(function(v){v.remove();v=null;});
		//replace new sublist
		var ul = node.appendChild(html.toElement());
		if(!open){
			ul.style.display = 'none';
		}

		this.addClasses(this.root);
		ul.$('ul').hide();
		return ul;
	},

	onDynamicLoad : function(e, node){
		var self = this;

		if(node.getAttribute('node_id')){
			//make it cache
			node.setAttribute('sb_dynamicLoad', '');
			var aj = new sb.ajax({
				data : {
					subtree : 1
				},
				url : self.dynamicLoadURL,
				onResponse : function(html){
					self.addSubList(node, html, true);
				}
			}).fetch();

		}
		return false;
	},

	onToggle : function(){
		console.log('toggle click');
	},

	onNodeClick : function(e, node){
		console.log('node click');
	}
};

sb.widget.tree.prototype.makeDraggable = function(){

		console.log('making draggable');
		this.draggable = true;
		this.root.events({
			mousedown : function(e){
				var target = e.target;
				if(target.className == 'draghandle'){
					var li = target.getContaining('li');
					this.cloneOrig = li;
					this.cloneOrig.style.backgroundColor = '#EFEFEF';
					this.clone = li.cloneNode(true);
					this.clone.style.display = 'none';
					this.clone.appendToTop('body');
					this.clone.style.position = 'absolute';
				}
			},

			mouseup : function(e){

				if(this.clone){
					var target = e.target;
					var li = target.nodeName == 'LI' ? target : target.getContaining('li');
					var self = this;
					if(li){

						this.cloneOrig.appendBefore(li);
						this.cloneOrig.style.backgroundColor = 'pink';
						window.setTimeout(function(){
							self.cloneOrig.style.backgroundColor = '';
							self.cloneOrig = null;
						}, 1000);
						li.style.borderTop = '';
					}

					this.clone.remove();
					this.clone = null;

				}
			},

			mousemove : function(e){
				if(this.clone){
					var target = e.target;
					sb.browser.removeSelection();
					this.clone.style.display = 'block';
					this.clone.style.top = e.clientY+'px';
					this.clone.style.left = (e.clientX+this.clone.offsetWidth/2)+20+'px';
					var li = target.nodeName == 'LI' ? target : target.getContaining('li');

					if(li && (!this._last_li || (this._last_li && li != this._last_li))){
						li.style.borderTop = '2px solid pink';
						if(this._last_li){
							this._last_li.style.borderTop = '';
						}
						this._last_li = li;

					}
				}
			}
		});

		this.addClasses(this.root);

};