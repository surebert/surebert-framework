sb.include('events.observer');
sb.include('events.listener');
/**
@Name: sb.events.formListener
@Description: used to create a new form listener that saves data back to a set url
@Param: object params Used to preseed the listener with class listeners
@Return: type desc
@Example:
<input name="fname" sb:set_url="/admin/set_value" value="joe" />
var myListener = new sb.events.formListener({
	onBeforeSave : function(e){
	
	}
});
//when you hit enter it would pass the data back to the url given
*/
sb.events.formListener = function(params){
	var self = this;
	if(!this.listener){
		this.listener = {
			events : {
				click : function(e){self._onSave(e)},
				change :  function(e){self._onSave(e)},
				keyup :  function(e){self._onKeyUp(e)}
			}

		};

		sb.events.observer.observe(this.listener);
	}
	
	sb.objects.infuse(params, this);
	return this.listener;
	
};

sb.events.formListener.prototype = {
	/**
	@Name: sb.events.formListener.prototype.onBeforeSave
	@Description: Fires right before saving, if your explicitly return false it cancels
	@Param: e The event that triggered the save with an additional sb.ajax property
	attached that represents the ajax object that will fetch
	 */
	onBeforeSave : function(e){},
	_onKeyUp : function(e){
		if(e.keyCode == 13){
			this._onSave(e);
		}
	},
	_onSave : function(e){
		var data,value, target = e.target;
		var nodeName = target.nodeName;
		var url = target.attr('sb:set_url');
		
		if(e.type == 'click' && nodeName != 'BUTTON'){
			return;
		}
		
		value = target.value;
		
		switch(nodeName){
			case 'BUTTON':
				if(!url){
					target = target.getPreviousSibling();
					url = target.attr('sb:set_url');
				}
					break;
			case 'SELECT':
				value = target.options[target.selectedIndex].value;
				break;
					
		}
		if(!url){
			return;
		}
		
		data = {
			name : target.name,
			value : target.value
		};

		eval("var extdata = "+target.attr('sb:data') +" || {};");
		sb.objects.infuse(extdata, data);
		
		e.ajax = new sb.ajax({
			target : target,
			url : url,
			method : target.attr('sb:http_method') || 'post',
			data : data
		});
		
		if(this.onBeforeSave(e) !== false){
			e.ajax.fetch();
		}
		
		
	}
};
