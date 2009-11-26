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

    currentHash : '',
    loading : false,
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


    updateHash : function(hash){
        window.location.hash = hash;
    },

	processHashChange : function(){
		var hash = window.location.hash.substring(1);
		var self = this;
		self.refreshing = false;

		self.loading = true;
		self.currentHash = hash;

		if(self.currentHash == ''){

			self.loading = false;
			return false;
		}

		if(typeof self.onLoading == 'function'){
			self.onLoading();
		}

		//IE crap to make history work
		if(sb.browser.agent == 'ie'){
			self.saveHistoryToIframeForIE(self.currentHash);
		}

		self.onHashChange(self.currentHash);

	},

	onHashChange : function(hash){
		var self = this;
		var aj = new sb.ajax({
			url : hash,
			onHeaders : function(status){

				if(status != 200){
					self.loading = false;
					self.onPageNotFound();
				}
			},
			onResponse : function(r){

				if(typeof self.onLoaded == 'function'){
					self.onLoaded(r);
				}

				sb.browser.hashHistory.loading = false;
			}
		}).fetch();
	},

    startTimer : function(){
        var self = this;
        window.setInterval(function(){
			var hash = window.location.hash.substring(1);
            if(!self.loading  && self.refreshing || (hash != self.currentHash) ){
				self.processHashChange();
            }
        }, 100);
    },

	reload : function(){
		this.refreshing = true;
	},

    init : function(o){
		var self = this;
        this.onLoading = o.onLoading;
        this.onLoaded = o.onLoaded;
		this.onPageNotFound = o.onPageNotFound || function(){};

		if(window.onhashchange){
			sb.events.add(window, 'hashchange', function(){self.processHashChange});
		} else {
			this.startTimer();
		}
        

    }
};