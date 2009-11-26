
/*
@Name: sb.browser.hashHistory
@Example:
sb.browser.hashHistory.init({
    onLoaded : function(r){
		//do something with the data
    },
    onLoading : function(){
       //show the user they are loading data
    },
	onPageNotFound : function(){

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

	onHashChange : function(){
		var hash = window.location.hash.substring(1);
		var self = this;
		self.refreshing = false;

		self.loading = true;
		self.currentHash = hash;

		if(self.currentHash == ''){

			self.loading = 0;
			return false;
		}

		if(typeof self.onLoading == 'function'){
			self.onLoading();
		}

		//check to make sure there were no &
		var m = self.currentHash.match(/(.*)\?(.*)$/);

		var url;
		
		url = self.currentHash;
		var aj = new sb.ajax({
			url : url,
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

				if(sb.browser.agent == 'ie'){
					//IE crap to make history work
					self.saveHistoryToIframeForIE(self.currentHash);
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
				self.onHashChange();
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
			sb.events.add(window, 'hashchange', function(){self.onHashChange});
		} else {
			this.startTimer();
		}
        

    }
};