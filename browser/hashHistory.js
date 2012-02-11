/**
@Name: sb.browser.hashHistory
@Description: Used to allow for back and forward, bookmarkable navigation with ajax

It tries to load the data from the url after the # whenever the hash changes

e.g. http://example.com/testing#/rest/test would load /rest/test.

First it would fire onloading.
Then it would fire either onLoaded or onPageNotFound
and pass the data loaded from /rest/test to it so that you could do what you would like

@Example:
sb.browser.hashHistory.init({
    onLoaded : function(r){
		//do something with the data
    },
    onLoading : function(response){
       //show the user they are loading data
    },
	onPageNotFound : function(response){

	}
});
*/

sb.browser.hashHistory = {
	/**
	 * The currentHash tag
	 */
    currentHash : '',
	/**
	 * @Name: sb.browser.hashHistory.loading
	 * @Description: Boolean Is hash url currently being loaded
	 * 
	 */
    loading : false,
	/**
	 * @Name sb.browser.hashHistory.saveHistoryToIframeForIE
	 * @Description: Internal Use Only makes hash history work in IE7
	 */
    saveHistoryToIframeForIE : function(hash){

        if(!this.iframe){
            this.iframe = new sb.element({
               tag : 'iframe',
               src : '',
               id : 'sb_history_iframe2',
               styles : {
                   display : 'none'
               }
            });
            this.iframe.appendToTop('body');
        }

        var doc = this.iframe.contentWindow.document;

        doc.open("javascript:'<html></html>'");
        doc.write("<html><head><title>"+document.title+"</title><scri" + "pt type=\"text/javascript\">parent.sb.browser.hashHistory.updateHash('"+ hash + "');</scri" + "pt></head><body></body></html>");
        doc.close();
    },

	/**
	 * @Name: sb.browser.hashHistory.updateHash
	 * @Description: Changes the current hash
	 */
    updateHash : function(hash){
        window.location.hash = hash;
    },

	/**
	 * @Name: sb.browser.hashHistory.processHashChange
	 * @Description: Internal Use Only handles the hash change event
	 */
	processHashChange : function(){
		var hash = window.location.hash.substring(1);
		
		this.refreshing = false;
		this.currentHash = hash;
		
		if(this.currentHash == ''){
			this.loading = false;
			return false;
		}
		
		//IE crap to make history work
		if(sb.browser.agent == 'ie'){
			this.saveHistoryToIframeForIE(this.currentHash);
		}
		
		this.loading = true;
		
		if(this.onLoading(hash) === false){
			this.loading = false;
			return false;
		}
		this.onHashChange(this.currentHash);

	},
	
	/**
	 * @Name: sb.browser.hashHistory.onHashChange
	 * @Description: Internal Use Only fires off ajax request based on hash
	 */
	onHashChange : function(hash){
		var self = this;
		
		hash = hash.replace('+', '%2B');
		var aj = new sb.ajax({
			url : hash,
			method : this.method || 'post',
			onHeaders : function(status, statusText){

				if(status != 200){
					self.loading = false;
					self.onPageNotFound();
				}
				sb.ajax.prototype.onHeaders.call(this, status, statusText);
			},
			onResponse : function(r){

				if(typeof self.onLoaded == 'function'){
					self.onLoaded(r, hash);
				}

				sb.browser.hashHistory.loading = false;
			}
		}).fetch();
	},
	
	/**
	 * @Name: sb.browser.hashHistory.onLoading
	 * @Description: Fires when the hash changes
	 * @Param: hash string the hash
	 */
	onLoading : function(hash){},
	
	/**
	 * @Name: sb.browser.hashHistory.onLoaded
	 * @Description: Fires when the URL loads
	 * @Param: content string the content from the URL
	 * @Param: hash string the hash
	 */
	onLoaded : function(content, hash){},
	
	/**
	 * @Name: sb.browser.hashHistory.onPageNotFound
	 * @Description: Fires when the URL returns a 404
	 */
	onPageNotFound : function(){},

	/**
	 * @Name: sb.browser.hashHistory.reload
	 * @Description: sets the process event to allow reload
	 */
	reload : function(){
		this.refreshing = true;
	},

	/**
	 * @Name: sb.browser.hashHistory.init
	 * @Description: sets a hashchange event or starts a timer if the hashchange event is not available
	 */
    init : function(o){
		var self = this;
		sb.objects.infuse(o, this);
		if(window.onhashchange){
			sb.events.add(window, 'hashchange', function(){self.processHashChange});
		} else {
			var self = this;
			window.setInterval(function(){
				var hash = window.location.hash.substring(1);
				if(!self.loading  && self.refreshing || (hash != self.currentHash) ){
					self.processHashChange();
				}
			}, 100);
		}
        

    }
};