sb.include('String.prototype.stripHTML');

/**
@Name: sb.widget.magicTable
@Version: 1.3 12-08-2008 12-11-2008
@Author: Paul Visco
@Description:  Makes or adds interactivity to an HTML table.  All events are attached to the table and delegated from their to keep overhead very low
@param o Object The following properties are used, however, you can add any additional properties to o that you would like and they will be transferred to your magicTable instance
o.table String/Node Reference to an HTML table e.g. #myTable, or myTable
o.table Object Define table to be created {headers : [], rows : []}
o.sortable boolean Should the table be sortable
o.defaultSortedBy number The cell index of the column to sort by, starts at zero, default to zero
o.classes Object class names to use for properties, see magicTable.prototype.classes for more info
o.onCellClick Function - see below
o.onCellMouseOut Function - see below
o.onCellMouseOver Function - see below
o.onRowClick Function - see below
o.onColClick Function - see below
o.onBeforeSort Function - see below
o.onAfterSort Function - see below
o.sortTypes Array - An array of sort types from sb.widget.magicTable.compare that are then used as forced sort types for the columns, must match the number of columns
@Example:
var myTable = new sb.widget.magicTable({
	table : '#jimmy',
	sortable : 1,
	defaultSortBy : 2,
	onCellClick : function(td){
		td.style.backgroundColor = 'pink';
		if(td.innerHTML == 'delete'){
			$(td.parentNode).remove();
		}
	},
	onRowClick : function(tr){
		tr.style.backgroundColor = 'red';
		//alert(tr.innerHTML);
	}
});
myTable.sortBy('colName');

//or
var myTable = new sb.widget.magicTable({
	table : {
		headers : ['name', 'age', 'phone'],
		rows : [
			['Paul', 31, '228-7445'],
			['Matthew', 36, '228-7613'],
			['Terry', 29, '228-5731']
		]
	},
	sortable : 1,
	onCellClick : function(td){
		td.style.backgroundColor = 'pink';
		if(td.innerHTML == 'delete'){
			$(td.parentNode).remove();
		}
	},
	onRowClick : function(tr){
		tr.style.backgroundColor = 'red';
		//alert(tr.innerHTML);
	}
});
myTable.table.appendTo('body');

*/
sb.widget.magicTable = function(o){
	
	for(var prop in o){
		if(typeof this[prop] != 'function' || (typeof this[prop] == 'function' && prop.match(/^on/))){
			if(prop == 'classes' && typeof o[prop] == 'object'){
				var classes = o[prop];
				for(var cls in classes){
					this.classes[cls] = classes[cls];
				}
			} else {
				this[prop] = o[prop];
			}
		}
	}
	
	if(this.table && this.table.headers && this.table.rows){
		this.create(this.table.headers, this.table.rows, this.sortTypes);
	}

	if(typeof this.table == 'string' || this.table.appendTo){
		this.table = $(this.table);
		
		this.head = $(this.table.tHead);
		
		this.body = $(this.table.tBodies[0]);
	}
	
	if(this.sortable){
		
		if(this.sortTypes){
			this.setSortTypes(this.sortTypes);
		}
		this.setSortStyles();
	}
	
	this.addEvents();
	
	this.sortBy(this.defaultSortedBy || 0);

};

sb.widget.magicTable.prototype = {
	
	/**
	 @Name: sb.widget.table.prototype.classes
	 @Description: The CSS classnames to use.  These are all optional and some have defaults, see below
	 @Example:
	 	myTable.classes.unsortable String The classname for columns you want to set as unsortable, the default is 'unsortable'
		myTable.classes.sortable String The classname added to all sortable columns <th>s by sb.magicTable, default is 'sortable'
		myTable.classes.sorted_by String The classname added to the <th> being sorted on, default is 'sorted_by'
		myTable.classes.force_sort String The className used for forcing sort type
		myTable.classes.asc String The className you want used for ascending sorts, if you don't just want the default arrow
		myTable.classes.desc String The className you want used for descending sorts, if you don't just want the default arrow
		myTable.classes.even String The className used even rows if you want even/odd CSS
		myTable.classes.odd String The className used odd rows if you want even/odd CSS
	*/
	classes : {
		unsortable : 'sb_unsortable',
		sortable : 'sb_sortable',
		sorted_by : 'sb_sorted_by',
		force_sort : 'sb_force_sort'
	},
	
	/**
	@Name: sb.widget.table.prototype.defaultSortedBy
	@Description: The default column to sort by, either by cellIndex or title.  See constructor arguments above.
	@Example:
	var myTable = new sb.widget.magicTable({
		table : '#jimmy',
		sortable : 1,
		defaultSortBy : 2,
	});
	var myTable = new sb.widget.magicTable({
		table : '#jimmy',
		sortable : 1,
		defaultSortBy : 'age',
	});
	*/
	defaultSortedBy : 0,
	
	/**
	@Name: sb.widget.table.prototype.addHeaders
	@Description: Adds rows to table headers
	@Example:
	myTable.addHeaders(['Blythe', '50','Wed, November 24, 2004','04/12/03','3.9.05', '$6,89']);
	*/
	addHeaders : function(data){
		this.addCells(data, this.head, 'th');
	},
	
	/**
	@Name: sb.widget.table.prototype.addRows
	@Description: Adds rows to a table instance
	@Example:
	myTable.addRows(['Blythe', '50','Wed, November 24, 2004','04/12/03','3.9.05', '$6,89']);
	//or
	myTable.addRows([
	 	['Julie', 'f', 54, 3456],
		['Wendy', 'f', 22, 4562],
		['Gina', 'f', 78, 5773],
		['Timmy', 'm', 12, 5467],
		['Jason', 'm', 45, 3452],
		['Tony', 'm', 5, 3456]
	]);
	*/
	addRows : function(data){
		this.addCells(data, this.body, 'td');
	},
	
	/**
	@Name: sb.widget.magicTable.prototype.removeRows
	@Description: Removes rows from a magicTable instance, rows start at 0
	@Example:
	//removes row 1
	mymagicTable.removeRows(1);
	//all rows in the array
	mymagicTable.removeRows([0,1,5,7]);
	//remove all rows in range
	mymagicTable.removeRows('1-3');
	*/
	
	removeRows : function(rows){
		if(typeof rows == 'string'){
			
			var matches = rows.match(/(\d)+-(\d+)/);
			if(matches){
				rows = [];
				for(var x=matches[1];x<=matches[2];x++){
					rows.push(parseInt(x, 10));
				}
			}
		}
		
		this.table.$('tbody tr').forEach(function(v,k){
			
			if(rows.inArray(k) ){
				v.remove();
			}
		});
	},

	/**
	@Name: sb.widget.magicTable.loadRows
	@Description: Loads more rows from the server.  It expects the loading page to be in json format and to be an array of arrays which represent the values in the rows.  Null values should be represented by null.
	@Param: o String The url to load the data from
	@Param: o object
	o.url String The url to load the data from
	o.onLoad Function The function that runs, once the rows are returned from the server but before they are added.  If it returns false they are not added.
	@Example:
	myTable.loadRows('/some/data');
 
	myTable.loadRows({
		url : '/some/data',
		//if you return false, the rows are not added
		onLoad : function(response){
			alert('response');
			return true;
		}
	});
	*/
	
	loadRows : function(o){
		var self = this;
		if(typeof o == 'string'){
			o = {url: o};
		}
		o.format = 'json';
		o.onResponse = o.onResponse || function(r){
			var addRows = true;
			if(typeof o.onLoad == 'function'){
				addRows = o.onLoad(r);
			}
			if(addRows !== false){
				self.addRows(r);
			}
		};
		var aj = new sb.ajax(o).fetch();
	},

	/**
	@Name: sb.widget.magicTable.sortBy()
	@Description: Loads more rows from the server.  It expects the loading page to be in json format and to be an array of arrays which represent the values in the rows.  Null values should be represented by null.
	@Param: header string Either the text value of the header to sort by, or a DOM reference to the header itself.  Always lowercase.
	@Param: reverse boolean Sort DESC
	@Example:
	myTable.sortBy('age');
	//or reverse
	myTable.sortBy('age', true);
	//sort by column 0
	myTable.sortBy(0);
	//sort by a <th> node DOM reference
	myTable.sortBy(th);
	*/
	sortBy : function(header, reverse){
		
		this.onBeforeSort(header);
		
		var rows = [];
		var col = 0;
		var sortRule = '';
		var self = this;
	
		this.table.$('thead th').forEach(function(v,k){
			v.removeClassName(self.classes.sorted_by);
			v.reverse = null;
			
			if(self.classes.desc && self.classes.asc){
				v.removeClassName(self.classes.desc);
				v.removeClassName(self.classes.asc);
			} else {
				v.innerHTML = v.innerHTML.replace(/ \u2191|\u2193/, '');
			}
			
			if((typeof header == 'string' && header == v.innerHTML.stripHTML().toLowerCase()) || header == v || header == k){
				
				col = k;
				var customSort = v.className.match(new RegExp(self.classes.force_sort+"_(\\w+)"));
				if(customSort){
					sortRule = customSort[1];
				}
		
				if(!sb.widget.magicTable.compare[sortRule]){
					sortRule = self.guessSortRule(k);
				}
				
				v.addClassName(self.classes.sorted_by);
				
				if(self.classes.desc && self.classes.asc){
					v.className += ' '+((reverse) ? self.classes.desc : self.classes.asc);
				} else {
					v.innerHTML = v.innerHTML+' '+((reverse) ? "\u2191" : "\u2193");
				}
				
				v.reverse = (reverse) ? true : false;
				
			}
		});

		var trs = this.table.$('tbody tr');
		trs.forEach(function(tr,k,a){
			
			rows.push({
				text: tr.cells[col].innerHTML.stripHTML(), 
				td: tr.cells[col],
				tr: tr
			});
		});

		var compare = sb.widget.magicTable.compare[sortRule];
		
		rows.sort(function(a, b) {
			return compare(a.text + '', b.text + '', a.td, b.td);
		});

		if(reverse){
			rows.reverse();
		}
		
		
		var tbody = this.table.$('tbody').nodes[0];
		rows.forEach(function(row,k,a){
			var tr = row.tr;
			
			if(self.classes.even){
				tr.removeClassName('even');
				tr.removeClassName('odd');
				if(k % 2 == 0){ tr.addClassName('even');} else {
					tr.addClassName('odd');
				}
			}
			
			tbody.appendChild(tr);
		});

		rows = null;
		this.onAfterSort(header);
	},
	
	/**
	@Name: sb.widget.magicTable.prototype.getHeaderValue
	@Description: Cleans the header data for a th
	@Param: th Element The th that was clicked.
	@Example:
	myTable.getHeaderValue = function(){
	   
	};	
	*/
	getHeaderValue : function(){
		
		if(typeof arguments[0] == 'number'){
			cell = this.head.rows[0].cells[arguments[0]];
		} else if(arguments[0].innerHTML){
			cell = arguments[0];
		}
		
		if(!cell.innerHTML){
			throw("argument must be th node or cellIndex");
			return false;
		}
		var str = cell.innerHTML.stripHTML();
		str = str.replace(/ \u2191|\u2193/, '');
		return str;
	},
	
	/**
	@Name: sb.widget.magicTable.prototype.getHeaderValue
	@Description: Cleans the header data for a th
	@Param: td Element The td element to get the value of
	@Param: rowIndex The row index
	@Param: cellIndex The cell index
	@Example:
	//returns the value of the td node passed to the function
	var data = myTable.getCellValue(td);
	
	//returns the value of cell 3 in row 0
	var data = myTable.getCellValue(0,3);
	*/
	getCellValue : function(){
		if(arguments.length == 2){
			cell = this.body.rows[arguments[0]].cells[arguments[1]];
		} else if(arguments[0].innerHTML){
			cell = arguments[0];
		}
		
		if(!cell.innerHTML){
			throw("argument must be td node or cellIndex");
			return false;
		}
		
		return cell.innerHTML.stripHTML();
	},
	
	/**
	@Name: sb.widget.magicTable.prototype.onBeforeSort
	@Description: fires after .sortBy is run but before actual sort begins.  The "this" is the magicTable instance itself.
	@Param: th Element The th that was clicked.
	@Example:
	myTable.onBeforeSort = function(th){
	   //do something
	};	
	*/
	onBeforeSort : function(th){},
	
	/**
	@Name: sb.widget.magicTable.prototype.onAfterSort
	@Description: fires after .sortBy is run and after actual sort is done.  The "this" is the magicTable instance itself.
	@Param: td Element The th that was clicked.
	@Example:
	myTable.onAfterSort = function(th){
	   //renumber the first td in each row after sort
		var rows = this.table.$('tbody tr');
		var x = 1;
		rows.forEach(function(v){
			v.firstChild.innerHTML = x;
			x++;
		});
	};		
	*/
	onAfterSort : function(th){},
	
	/**
	@Name: sb.widget.magicTable.prototype.onCellClick
	@Description: fires when a cell is clicked.  The "this" is the magicTable instance itself.
	@Param: td Element The td that was clicked.
	@Example:
	myTable.onCellClick = function(td){
		td.style.backgroundColor = 'pink';
		if(td.innerHTML == 'delete'){
			$(td.parentNode).remove();
		}
	};	
	*/
	onCellClick : function(td){},

	/**
	@Name: sb.widget.magicTable.prototype.onRowClick
	@Description: fires when a row is clicked.  The "this" is the magicTable instance itself.
	@Param: td Element The td that was clicked.
	@Example:
	myTable.onRowClick = function(tr){
		tr.style.backgroundColor = 'red';
	};
	*/
	onRowClick : function(tr){},
	
	/**
	@Name: sb.widget.magicTable.prototype.onColClick
	@Description: fires when a cell in a column is clicked.  Because there is no correspionding column node in HTML it returns an sb.nodeList of all the tds in the column.  The "this" is the magicTable instance itself.
	@Param: column Object An object representing the column clicked and some additional data
	column.title String The title of the column
	column.values Array An array of the values in the column
	column.th Element The TH of the column clicked
	column.tds sb.nodeList containing the TDs of the column clicked
	column.prevColumn A reference to the last Column clicked if there was one.  This allows to reset any changes (e.g. style changes) from the last column.
	@Example:
	myTable.onColClick = function(column){
		//change all the columns to yellow
		column.tds.styles({
			backgroundColor : 'yellow'
		});
		
		if(column.prevColumn){
			column.prevColumn.tds.styles({
				backgroundColor : ''
			});
		}
		
		column.values = column.values.map(function(v){
			return parseInt(v, 10);
		});
		alert(column.values.sum());
	};
	*/
	onColClick : function(column){},

	/**
	@Name: sb.widget.magicTable.prototype.onHeaderClick
	@Description: fires when a header TH is clicked.  The "this" is the magicTable instance itself.
	@Param: th Element The th that was clicked.
	@Example:
	myTable.onHeaderClick = function(th){
		th.style.backgroundColor = 'red';
	};
	*/
	onHeaderClick : function(th){},

	/**
	@Name: sb.widget.magicTable.prototype.onCellMouseOver
	@Description: fires when a table cell is moused over.  The "this" is the magicTable instance itself.
	@Param: td Element The td that was mousedover.
	@Example:
	myTable.onCellMouseOver = function(td){
		td.style.backgroundColor = 'pink';
	};
	*/
	onCellMouseOver : function(td){},

	/**
	@Name: sb.widget.magicTable.prototype.onCellMouseOut
	@Description: fires when a table cell is moused out.  The "this" is the magicTable instance itself.
	@Param: td Element The td that was mousedout.
	@Example:
	myTable.onCellMouseOut = function(td){
		td.style.backgroundColor = '';
	};
	*/
	onCellMouseOut : function(td){},
	
	/**
	@Name: sb.widget.magicTable.prototype.setSortStyles
	@Description: Used internally
	*/
	setSortStyles : function(){
		
		var self = this;
		
		this.table.$('th').forEach(function(v,k){
			
			v.onselectstart = function() {
		        return false;
		    };
		    v.unselectable = "on";
		    v.style.MozUserSelect = "none";
			v.style.cursor = 'pointer';
			
		    if(v.title == ''){
			    if(v.hasClassName(self.classes.unsortable)){
					v.title = 'Column not sortable';
					
			    } else {

					v.addClassName(self.classes.sortable);
			    	v.title = 'Click to sort, click again to reverse sort';
			    }
		    }
		});
	},
	
	/**
	@Name: sb.widget.magicTable.prototype.guessSortRule
	@Description: Used internally
	*/
	guessSortRule: function(col) {
		var rows = this.table.tBodies[0].rows;
		var len = rows.length;
		for(var row = 0; row < len; row++) {
			var text = this.getCellValue(row, col);
			if(text.length) return sb.widget.magicTable.guessFormat(text);
		}
		return 'nocase';
	},
	
	/**
	@Name: sb.widget.table.prototype.create
	@Description: Used Internally
	*/
	create : function(headers, rows, sortTypes){
	
		this.table = new sb.element({
			tag : 'table'
		});
	
		this.head = new sb.element({
			tag : 'thead'
		});
	
		this.body = new sb.element({
			tag : 'tbody'
		});
		
		this.table.appendChild(this.head);
		this.table.appendChild(this.body);
		this.addHeaders(headers);
		this.addRows(rows);
		if(sortTypes instanceof Array){
			this.setSortTypes(sortTypes);
		}
	},
	
	/**
	@Name: sb.widget.table.prototype.addCells
	@Description: Used Internally
	*/
	addCells : function(data, parent, tag){
		var self = this;
		if(data[0].forEach){
			data.forEach(function(row){
				self.addCells(row, parent, tag);
			});
		} else {
		
			var tr = parent.appendChild(new sb.element({
				tag : 'tr'
			}));
			
			data.forEach(function(cell){
				tr.appendChild(new sb.element({tag : tag, innerHTML : cell}));
			});
		}
	},
	
	/**
	@Name: sb.widget.table.prototype.setSortTypes
	@Description: Allow you to predefine the column sort types, must match column header.  Compare types defined in sb.widget.magicTable.prototype.compare
	@Param: headers Array An array that represents the predefined sort type for each column as in 
	@Example:
	myTable.setSortTypes(['alpha', 'numeric', 'natural', 'natural', 'usdate', 'usdate', 'alpha']);
	*/
	setSortTypes : function(headers){
		var ths = this.table.$('thead th');
		var self = this;
		
		if(headers.length == ths.length()){
			ths.forEach(function(v,k){
				v.addClassName(self.classes.force_sort+'_'+headers[k]);
			});
		}
	},
	
		/**
	@Name: sb.widget.magicTable.prototype.addEvents
	@Description: Used internally
	*/
	
	addEvents : function(){
		var self = this;
		this.table.events({
			mousemove : function(e){
			
				target = sb.events.target(e);
				
				if(target.nodeName == 'TD'){
					if(self.prevover != target){
						if(self.prevover){
							self.onCellMouseOut(self.prevover);
						}
						self.onCellMouseOver(target);
					}

					self.prevover = target;
				}
				
			},
			click : function(e){
				var target = sb.events.target(e);
				
				if(target.nodeName == 'TD'){
					
					if(typeof self.onCellClick == 'function'){
						self.onCellClick(target);
					}
					self.onRowClick(target.parentNode);
				
					if(typeof self.onColClick == 'function'){
						
						var header = self.head.rows[0].cells[target.cellIndex];
						
						var column = {
							th : header,
							title : self.getHeaderValue(target.cellIndex),
							values : [],
							tds : new sb.nodeList(),
							prevColumn : self._prevColumn
						};
					
						self.body.$('tr').forEach(function(tr){
							var td = tr.cells[target.cellIndex];
							column.tds.add(td);
							column.values.push(td.innerHTML.stripHTML());
						});
						
						self.onColClick(column);
						self._prevColumn = column;
					}
					
				} else if(target.nodeName == 'TH'){
					self.onHeaderClick(target);
				}
			},
			
			mousedown : function(e){
				
				if(self.sortable && target.nodeName == 'TH'  && !target.hasClassName(self.classes.unsortable)){
					if(target.reverse === false){
						target.reverse = true;
					} else {
						target.reverse = false;
					}
					
					self.sortBy(target, target.reverse);
				}
			}
		});
	}
		
};

/*
@Name: sb.widget.magicTable.getCurrencyValue
@Description: Used internally
*/
sb.widget.magicTable.getCurrencyValue = function(s) {
	//-$1,234.56 or -1,234.56$
	var m = '';
	s = s.replace(/\,/g, '');
	if(m = s.match(/^(-?)\D(\d+(\.\d+)?)$/)) {
		return parseFloat(m[1] + m[2]);
	}
	if(m = s.match(/^(-?\d+(\.\d+)?)\D$/))
		return parseFloat(m[1]);
	return parseFloat('NaN');
};


/**
@Name: sb.widget.magicTable.guessFormat
@Description: Used internally
*/
sb.widget.magicTable.guessFormat = function(text) {
	if(!isNaN(Number(text)))
		return 'numeric';
	if(text.match(/^\d{2}[\/-]\d{2}[\/-]\d{2,4}$/))
		return 'usdate';
	if(text.match(/^\d\d?\.\d\d?\.\d{2,4}$/))
		return 'eudate';
	if(!isNaN(Date.parse(text)))
		return 'date';
	if(!isNaN(sb.widget.magicTable.getCurrencyValue(text)))
		return 'currency';
	if(text.match(/^[a-z_]+\d+(\.\w+)$/))
		return 'natural';
	return 'nocase';
};

/**
@Name: sb.widget.magicTable.prototype.compare
@Description: The sort methods avaiable to sb.widget.magicTable.  You can add your own too!  compare, getCurrencyValue and guessFormat sort functions adapted from http://www.tagarga.com/blok/post/2
*/
sb.widget.magicTable.compare = {

	/**
	@Name: sb.widget.magicTable.prototype.alpha
	@Description: Sort alphabetically
	*/
	alpha : function(a, b) {
		return a > b ? 1 : a < b ? -1 : 0;
	},
	
	/**
	@Name: sb.widget.magicTable.prototype.reverseAlpha
	@Description: Sort reverse alphabetically
	*/
	reverseAlpha : function(a, b) {
		return a < b ? 1 : a > b ? -1 : 0;
	},
	
	/**
	@Name: sb.widget.magicTable.prototype.nocase
	@Description: Sort alphabetically, case insensitively
	*/
	nocase : function(a, b) {
		return sb.widget.magicTable.compare.alpha(a.toLowerCase(), b.toLowerCase());
	},

	/**
	@Name: sb.widget.magicTable.prototype.numeric
	@Description: Sort numerically
	*/
	numeric : function(a, b) {
		return (Number(a) || 0) - (Number(b) || 0);
	},

	/**
	@Name: sb.widget.magicTable.prototype.natural
	@Description: Sort naturally
	*/
	natural : function(a, b) {
		function prepare(s) {
			var q = [];
			s.replace(/(\D)|(\d+)/g, function($0, $1, $2) {
				q.push($1 ? 1 : 2);
				q.push($1 ? $1.charCodeAt(0) : Number($2) + 1)
			});
			q.push(0);
			return q;
		}
		var aa = prepare(a), bb = prepare(b), i = 0;
		do {
			if(aa[i] != bb[i])
				return aa[i] - bb[i];
		} while(aa[i++] > 0);
		return 0;
	},

	/**
	@Name: sb.widget.magicTable.prototype.currency
	@Description: Sort by currency
	*/
	currency : function(a, b) {
		return (sb.widget.magicTable.getCurrencyValue(a) || 0) -
			(sb.widget.magicTable.getCurrencyValue(b) || 0);
	},
	
	/**
	@Name: sb.widget.magicTable.prototype.date
	@Description: Sort by date
	*/
	date : function(a, b) {
		return Date.parse(a) - Date.parse(b);
	},

	/**
	@Name: sb.widget.magicTable.prototype.date
	@Description: Sort by US date
	*/
	usdate : function(a, b) {
		a = a.split(/\D+/);
		b = b.split(/\D+/);
		return (a[2] - b[2]) || (a[0] - b[0]) || (a[1] - b[1]);
	}
};