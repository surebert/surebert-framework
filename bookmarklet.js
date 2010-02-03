/**
 * Used when loading surebert from bookmarklet
 * e.g. <a href="javascript:var d=document;var s=d.createElement('script');s.src='http://webservicesdev.roswellpark.org/surebert/basic/bookmarklet';d.body.appendChild(s);void(0);">surebert</a>
 */
sb.include('forms.textarea');
sb.include('forms.textarea.allowTabs');

sb.include('widget.terminal', function(){
	sb.terminal = new sb.widget.terminal({
		type : 'input',
		className : 'sb_terminal',
		
		onResponse : function(r){
			//rp.notify(r);
			console.log(r);
		},
		onError : function(r){
			//rp.notify('Command not found', 'error');
		},
		onAfterCreate : function(){},
		processClientside : function(command){
			var m = command.match(/^(a|e|s)\.p\.(.*?)$/);
			if(m){
				var d = false;
				switch(m[1]){
					case 'e':
						d = 'Element';
						break;
					case 's':
						d = 'String';
						break;
					case 'a':
						d = 'Array';
						break;
				}
				if(d){

					sb.include(d+'.prototype.'+[m[2]]);
					sb.terminal.clear();
					return true;
				}
				
			}
			return false;
		}
	});
});