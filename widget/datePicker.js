sb.include('Element.prototype.disableSelection');
sb.include('Array.prototype.iteration');
sb.include('date.isDate');

/**
@Name: sb.widget.datePicker
@Author: Greg Dean and Paul Visco
@Description: used to create a datepicker widget.  Always
returns the current reference to the only instance
@Example:
var dp = new sb.widget.datePicker();
dp.onDateSelect = function(date){
	alert(date);
	this.hide();
};
dp.show({
	x: 200,
	y : 200,
	z : 1000
});

*/
sb.widget.datePicker = function(params){

	var cal = sb.widget.datePicker.instance ? sb.widget.datePicker.instance : this;
	sb.objects.infuse(params, cal);
	cal.parentNode = sb.$(cal.parentNode) || document.body;
	if(!sb.widget.datePicker.instance){
		sb.widget.datePicker.handleKeyEvents();
	}
	sb.widget.datePicker.instance = cal;
	return cal;
};
/**
@Name: sb.widget.datePicker.showing
@Description: boolean true if showing, false if not showing a date picker
*/
sb.widget.datePicker.showing = false;

/**
@Name: sb.widget.datePicker.listen
@Description: Sets up global event listeners for click and keydown on .sb_date_picker elements
@Param: param object Same arguments as sb.widget.datePicker constructor e.g.
.onDateSelect(date) optional function - default fills in target of event
.onClick(e)
.onHeaderClick(e)

@Example:
<input class="sb_date_picker" value ="01/22/1977"/>

//listens for any sb_date_picker actions
sb.widget.datePicker.listen();
*/
sb.widget.datePicker.listen = function(params){
	this.display = function(e){
			var target = e.target;
			if(e.target.hasClassName('sb_date_picker')){
				e.preventDefault();
				var args = {
					date : e.target.value || sb.$(e.target.attr('sb_date_target')).value,
					target : e.target,
					minDate : e.target.attr('sb_min_date'),
					maxDate : e.target.attr('sb_max_date')
				};

				sb.objects.infuse(params, args);
				var cal = new sb.widget.datePicker(args);

				cal.show({
					x : e.target.getX(),
					y : e.target.getY()
				});
			}
		};

		sb.events.add('html', 'click', sb.widget.datePicker.display);
};

/**
@Name: sb.widget.datePicker.prototype
*/
sb.widget.datePicker.prototype = {
	calendarDay : '',
	calendarYear : '',
	calendarMonth : '',
	/**
	@Name: sb.widget.datePicker.prototype.onClick
	@Description: fires anytime the datepicker is clicked
	*/
	onClick : function(){},
	/**
	@Name: sb.widget.datePicker.prototype.onHeaderClick
	@Description: fires anytime the header is clicked
	@Example:
	 myPicker.onHeaderClick = function(e){};
	*/
	onHeaderClick : function(e){},

	/**
	@Name: sb.widget.datePicker.prototype.onDateSelect
	@Description: fires anytime a date is selected, by default it sets the originating
	 target's value to the date selected and hides the datepicker, you can override
	@Example:
	 myPicker.onDateSelect = function(e){};
	*/
	onDateSelect : function(date){
		var target = sb.$(this.target.attr('sb_date_target')) || this.target;
		target.value = date;
		this.hide();
	},

	/**
	@Name: sb.widget.datePicker.prototype.switchToNextMonth
	@Description: switches to the next month
	@Example:
	 myPicker.switchToNextMonth();
	*/
	switchToNextMonth : function(){
		var na = this.days.$('td');
		if(na.length() && na.nodes.pop().className == 'sb_day_not_allowed'){
			return false;
		}

		this.calendarMonth++;

		if(this.calendarMonth > 11){
			this.calendarMonth = 0;
			this.calendarYear++;
		}

		this.refreshCalendar();
		if(this.debug){
			console.log(this.calendarMonth+' '+this.calendarYear);
		}

		var tds = this.days.$('td');
		var len = tds.length();
		if(!len){
			return false;
		}
		var x = 0;
		for(x;x<len;x++){
			if(tds.nodes[x].className != 'sb_no_day'){
				tds.nodes[x].className =  'sb_day_selected';
				this.setDate(this.getDate(this.calendarMonth+1+'/'+(parseInt(tds.nodes[x].innerHTML, 10))+'/'+this.calendarYear));
				break;
			}
		}

	},

	/**
	@Name: sb.widget.datePicker.prototype.switchToNextDay
	@Description: switches to the next day
	@Example:
	 myPicker.switchToNextDay();
	*/
	switchToNextDay : function(){
		var tds = this.days.$('td');
		var len = tds.length();
		if(!len){
			return false;
		}
		var selectedIndex=0,x = 0;

		for(x;x<len;x++){
			if(tds.nodes[x].className == 'sb_day_selected'){
				selectedIndex = x;
			}
		}

		if(selectedIndex){
			var next_td = tds.nodes[selectedIndex+1];
			if(!next_td || next_td.className == 'sb_no_day'){
				this.switchToNextMonth();

			} else if(next_td.className != 'sb_day_not_allowed'){
				tds.nodes[selectedIndex].className = '';
				next_td.className = 'sb_day_selected';
				this.setDate(this.getDate(this.calendarMonth+1+'/'+(parseInt(next_td.innerHTML, 10))+'/'+this.calendarYear));
			}
		}


	},

	/**
	@Name: sb.widget.datePicker.prototype.switchToNextYear
	@Description: switches to the next year
	@Example:
	 myPicker.switchToNextYear();
	*/
	switchToNextYear : function(){
		var na = this.days.$('td');
		if(na.length() && na.nodes.pop().className == 'sb_day_not_allowed'){
			return false;
		}

		this.calendarYear++;

		this.refreshCalendar();
		if(this.debug){
			console.log(this.calendarMonth+' '+this.calendarYear);
		}

		var tds = this.days.$('td');
		var len = tds.length();
		if(!len){
			return false;
		}
		var x = 0;
		for(x;x<len;x++){
			if(tds.nodes[x].className != 'sb_no_day'){
				tds.nodes[x].className =  'sb_day_selected';
				this.setDate(this.getDate(this.calendarMonth+1+'/'+(parseInt(tds.nodes[x].innerHTML, 10))+'/'+this.calendarYear));
				break;
			}
		}

	},

	/**
	@Name: sb.widget.datePicker.prototype.switchToPrevMonth
	@Description: switches to the prev month
	@Example:
	 myPicker.switchToPrevMonth();
	*/
	switchToPrevMonth : function(){
		var na = this.days.$('td.sb_day_not_allowed');
		if(na.length() && na.nodes[0].innerHTML == '1'){
			return false;
		}

		this.calendarMonth--;

		if(this.calendarMonth < 0){
			this.calendarMonth = 11;
			this.calendarYear--;
		}

		this.refreshCalendar();
		if(this.debug){
			console.log(this.calendarMonth+' '+this.calendarYear);
		}

		var tds = this.days.$('td');
		var len = tds.length();
		if(!len){
			return false;
		}
		var x = len-1;
		for(x;x>=0;x--){
			if(tds.nodes[x].className != 'sb_no_day'){
				tds.nodes[x].className =  'sb_day_selected';
				this.setDate(this.getDate(this.calendarMonth+1+'/'+(tds.nodes[x].innerHTML)+'/'+this.calendarYear));
				break;
			}
		}

	},

	/**
	@Name: sb.widget.datePicker.prototype.switchToPrevDay
	@Description: switches to the prev day
	@Example:
	 myPicker.switchToPrevDay();
	*/
	switchToPrevDay : function(){
		var tds = this.days.$('td');
		var len = tds.length();
		if(!len){
			return false;
		}
		var selectedIndex=0,x = len-1;
	
		for(x;x>=0;x--){
			if(tds.nodes[x].className == 'sb_day_selected'){
				selectedIndex = x;
			}
		}
		
		if(selectedIndex){
			var prev_td = tds.nodes[selectedIndex-1];
			if(prev_td.className == 'sb_no_day'){
				this.switchToPrevMonth();
			} else if(prev_td.className != 'sb_day_not_allowed'){
				if(this.setDate(this.getDate(this.calendarMonth+1+'/'+(prev_td.innerHTML)+'/'+this.calendarYear))){

					tds.nodes[selectedIndex].className = '';
					prev_td.className = 'sb_day_selected';
				}
			}
		}

	},

	/**
	@Name: sb.widget.datePicker.prototype.switchToPrevYear
	@Description: switches to the prev year
	@Example:
	 myPicker.switchToPrevYear();
	*/
	switchToPrevYear : function(){
		var na = this.days.$('td');
		if(na.length() && na.nodes.pop().className == 'sb_day_not_allowed'){
			return false;
		}

		this.calendarYear--;

		this.refreshCalendar();
		if(this.debug){
			console.log(this.calendarMonth+' '+this.calendarYear);
		}

		var tds = this.days.$('td');
		var len = tds.length();
		if(!len){
			return false;
		}
		var x = len-1;
		for(x;x>=0;x--){
			if(tds.nodes[x].className != 'sb_no_day'){
				tds.nodes[x].className =  'sb_day_selected';
				this.setDate(this.getDate(this.calendarMonth+1+'/'+(tds.nodes[x].innerHTML)+'/'+this.calendarYear));
				break;
			}
		}

	},

	skipToDate : function(date){
		var date = this.getDate(date);
		
		this.setDate(date);
		this.refreshCalendar();
	},

	/**
	@Name: sb.widget.datePicker.prototype.switchToPrevRow
	@Description: switches to the prev row
	@Example:
	 myPicker.switchToPrevRow();
	*/
	switchToPrevRow : function(){
		var tds = this.days.$('td');
		var len = tds.length();
		if(!len){
			return false;
		}
		var selectedIndex=false,x = 0;
		var selectedTd = false;
		for(x;x<len;x++){
			if(tds.nodes[x].className == 'sb_day_selected'){
				selectedIndex = x;
				selectedTd = tds.nodes[x];
			}
		}

		if(selectedIndex !== false){
			var tr = tds.nodes[selectedIndex].getContaining('tr');
			var tdIndex = 0;
			tr.$('td').forEach(function(v,k,a){
				if(v == selectedTd){
					tdIndex = k;
				}
			});
			tr = tr.getPreviousSibling();
			if(!tr){
				this.switchToPrevMonth();
				return;
			}

			var td = tr.$('td').nodes[tdIndex];
			if(!td || td.className == 'sb_no_day'){
				this.switchToPrevMonth();
				return;
			}

			if(td && this.setDate(this.getDate(this.calendarMonth+1+'/'+(tr.$('td').nodes[tdIndex].innerHTML)+'/'+this.calendarYear))){
				selectedTd.className = '';
				
				tr.$('td').nodes[tdIndex].className = 'sb_day_selected';
			}

		}
	},

	/**
	@Name: sb.widget.datePicker.prototype.switchToNextRow
	@Description: switches to the prev row
	@Example:
	 myPicker.switchToNextRow();
	*/
	switchToNextRow : function(){
		var tds = this.days.$('td');
		var len = tds.length();
		
		if(!len){
			return false;
		}
		var selectedIndex=false,x = 0;
		var selectedTd = false;
		for(x;x<len;x++){
			if(tds.nodes[x].className == 'sb_day_selected'){
				selectedIndex = x;
				selectedTd = tds.nodes[x];
			}
		}

		if(selectedIndex !== false){
			var tr = tds.nodes[selectedIndex].getContaining('tr');

			var tdIndex = false;
			tds = tr.$('td');
			tds.forEach(function(v,k,a){
				if(v == selectedTd){
					tdIndex = k;
				}
			});
			tr = tr.getNextSibling();
			if(!tr){
				this.switchToNextMonth();
				return;
			}

			var td = tr.$('td').nodes[tdIndex];
			if(!td){
				this.switchToNextMonth();
				return;
			}
			
			
			if(td && this.setDate(this.getDate(this.calendarMonth+1+'/'+(td.innerHTML)+'/'+this.calendarYear))){

				selectedTd.className = '';
				if(!td || td.className == 'sb_no_day'){
					this.switchToNextMonth();
					return;
				}
				if(td){
					td.className = 'sb_day_selected';
				} else {
					this.switchToNextMonth();
				}
			}

		}
	},

	/**
	@Name: sb.widget.datePicker.prototype.switchToMaxDate
	@Description: switches to the max date view
	@Example:
	 myPicker.switchToMaxDate();
	*/
	switchToMaxDate : function(){
		if(this.maxDate){
			this.setDate(this.maxDate);
		}
		
		this.refreshCalendar();
		if(this.debug){
			console.log(this.calendarMonth+' '+this.calendarYear);
		}

	},

	/**
	@Name: sb.widget.datePicker.prototype.switchToMinDate
	@Description: switches to the min date view
	@Example:
	 myPicker.switchToMinDate();
	*/
	switchToMinDate : function(){
		if(this.minDate){
			this.setDate(this.minDate);
		}

		this.refreshCalendar();
		if(this.debug){
			console.log(this.calendarMonth+' '+this.calendarYear);
		}

	},

	/**
	@Name: sb.widget.datePicker.prototype.setDate
	@Description: sets the date
	@Param: date String e.g. 01/22/1977 or new Date('01/22/1977');
	@Example:
	 myPicker.setDate();
	*/
	setDate : function(date){
	
		if(!sb.date.isDate(date)){
			var date = new Date();
		} else if(!date.getFullYear){
			var date = new Date(date);
		}
		
		if(this.minDate){
			var minDate = new Date(this.minDate);
			if(date < minDate){
				return false;
			}
		}

		if(this.maxDate){
			var maxDate = new Date(this.maxDate);
			if(this.date > maxDate){
				return false;
			}
		}
		
		this.date = date;
		this.calendarDay = this.date.getDate();
		this.calendarYear = this.date.getFullYear();
		this.calendarMonth = this.date.getMonth();
		if(this.yearInput){
			this.yearInput.value = this.getDate();
		}
		
		return true;

	},

	/**
	@Name: sb.widget.datePicker.prototype.getDate
	@Description: gets the date
	@Param: date If passed 1/22/1977 it would change to 01/22/1977, if no arg is given
	returns current cal date
	@Example:
	 myPicker.getDate();
	*/
	getDate : function(date){
		var date = date ? new Date(date) : this.date;
		var month = date.getMonth()+1;
		month = month < 10 ? '0'+month : month;
		var day = date.getDate();
		day = day < 10 ? '0'+day : day;
		var year = date.getFullYear();
		return [month, day, year].join('/');
	},

	/**
	@Name: sb.widget.datePicker.prototype.cycleTips
	@Description: cycles the help tips
	@Example:
	 myPicker.cycleTips();
	*/
	cycleTips : function(){
		this.days.innerHTML = '<p>'+sb.widget.datePicker.tips.cycle()+'</p><div><a href="#" class="sb_datep_tip_exit" style="margin-right:20px;">exit help</a> <a href="#" class="sb_datep_tip">next tip</a></div>';
	},

	/**
	@Name: sb.widget.datePicker.prototype.refreshCalendar
	@Description: refreshes the calendar view
	@Example:
	 myPicker.refreshCalendar();
	*/
	refreshCalendar : function(){
		this.currentYear.innerHTML = sb.widget.datePicker.months[parseInt(this.calendarMonth)]+' '+ this.calendarYear;
		this.days.innerHTML = this._drawCalendar();
		this._setSliderYear(this.calendarYear);
	},

	/**
	@Name: sb.widget.datePicker.prototype.hide
	@Description: hides the datepicker
	@Example:
	 myPicker.hide();
	*/
	hide : function(){
		sb.widget.datePicker.showing = false;
		this.calendar.remove();
	},

	/**
	@Name: sb.widget.datePicker.prototype.show
	@Description: show the datepicker
	@Param: params Object
	.x The x position to show it at
	.y The y position to show it at
	.z The z index to show it at
	@Example:
	 myPicker.show({x : 100, y : 100, z : 100});
	*/
	show : function(params){
		params = params || {};
		var self = this;
		sb.widget.datePicker.showing = true;

		if(!params.x && this.target){
			console.log('g');
			params.x = this.target.getX();
			params.y = this.target.getY();
		}
		this._createCalendar();
		this.setDate(this.date);
		this.calendar.styles({
			position: 'absolute',
			left : (params.x || sb.browser.w/2)+'px',
			top : (params.y || sb.browser.h/2)+'px',
			zIndex : params.z || 1000
		});

		this.refreshCalendar();

		this.calendar.appendTo(this.parentNode);
		var year = this.date.getFullYear();
		if(!this.sizeSet){
			this.calendar.style.width = this.days.offsetWidth+'px';
			this.sizeSet = true;
			var div = new sb.el('div');
			div.style.backgroundColor = 'red';
			div.style.width = this.calendar.style.width;
			div.appendBefore(this.days);
			
			this.slider = new sb.widget.slider({
				name : ' year',
				min : this.minYear || year-10,
				max : this.maxYear || year+30,
				id : 'sb_datepicker_year',
				defaultValue : year,
				allowFloats : 0,
				onStopSlide : function(){
					self.skipToDate((self.calendarMonth+1)+'/01/'+this.value);
				},
				onClickValue : function(){
					self.skipToDate((self.calendarMonth+1)+'/01/'+this.value);
				}
			});

			this.slider.appendTo(div);
			this._setSliderYear(year);
		}
		

	},

	/**
	@Name: sb.widget.datePicker.prototype._setSliderYear
	@Description: used internally to set the year on the slider
	*/
	_setSliderYear : function(year){
		if(this.slider){
			this.slider.defaultValue = year;
			this.slider.min = this.minYear || year-10;
			this.slider.max = this.maxYear || year+30;
			this.slider.calibrate();
			this.slider.setValue(year);
		}
		
	},

	/**
	@Name: sb.widget.datePicker.prototype._createCalendar
	@Description: used internally
	*/
	_createCalendar : function(){
		
		var self = this;
		if(!this.calendar){
			
			this.calendar = new sb.element({
				tag: 'div',
				innerHTML: '',
				className: 'sb_datepicker',
				styles : {
					textAlign : 'center'
				},
				events : {
					keyup : function(e){
						var target = e.target;
						if(target == self.yearInput){
							if(e.keyCode == 13){

								if(sb.date.isDate(target.value)){
									if(self.setDate(self.getDate(target.value)) !== false){
										self.onDateSelect(self.getDate());
									};

								} else {
									self.yearInput.value = self.getDate();
								}
							}
						}
					},
					dblclick : function(e){
						var target = e.target;

						if(target == self.currentYear){
							self.yearInput.style.display = 'block';
							self.yearInput.value = self.getDate();
							self.yearInput.select();
							self.yearInput.focus();
						}
					},
					click : function(e){
					
						var target = e.target;
						e.preventDefault();
						if(target.className == 'sb_datep_tip'){
							self.cycleTips();
						} else if(target.className == 'sb_datep_tip_exit'){
							self.refreshCalendar();
						}
						if(typeof self.onClick === 'function'){
							if(self.onClick(e) === false){
								return false;
							}
						}
						
						if(typeof self.onHeaderClick === 'function' && target.isWithin(self.header)){
							if(self.onHeaderClick(e) === false){
								return false;
							}
						}

						if(target == self.prevMonthBtn){
							self.switchToPrevMonth();
						}


						if(target == self.nextMonthBtn){
							self.switchToNextMonth();
						}

						if(target.isWithin(self.days)){
							var td = target.nodeName == 'TD' ? target : target.getContaining('td');
							if(td == false || td.className == 'sb_no_day' ||  td.className == 'sb_day_not_allowed'){
								return false;
							}
							var month = self.calendarMonth+1;
							if(month < 10){
								month ='0'+month;
							}
							var day = td.innerHTML;
							if(day < 10){
								day ='0'+day;
							}
							
							if(self.setDate(month+'/'+day+'/'+self.calendarYear) !== false){
								
								if(typeof self.onDateSelect === 'function'){
									self.onDateSelect(self.getDate());
									if(self.target && self.target.focus){
										self.target.focus();
									}
								}
							}
							
						}

					}
				}
			});

			this.header = new sb.element({
				tag: 'table',
				className : 'sb_datepicker_header',
				styles : {
					width : '100%'
				},
				innerHTML: ''
			});

			this.header.appendTo(this.calendar);
			this.tbody = new sb.el('tbody');
			this.tbody.appendTo(this.header);
			this.tr = this.tbody.insertRow(0);

			this.prevMonthBtn = sb.$(this.tr.insertCell(0));
			this.prevMonthBtn.innerHTML = '&laquo;';
			this.prevMonthBtn.className = 'sb_datepicker_prev_month';
			this.prevMonthBtn.styles({
				textAlign : 'left',
				cursor : 'pointer'
			});
			this.prevMonthBtn.disableSelection();
			this.currentYear = sb.$(this.tr.insertCell(1));
			this.currentYear.title = 'Double-click to manually enter date';
			this.currentYear.disableSelection();
			this.currentYear.styles({
				cursor : 'pointer',
				textAlign: 'center'
			});
			this.nextMonthBtn = sb.$(this.tr.insertCell(2));
			this.nextMonthBtn.innerHTML = '&raquo;';
			this.nextMonthBtn.className = 'sb_datepicker_next_month';
			this.nextMonthBtn.styles({
				textAlign : 'right',
				cursor : 'pointer'
			});
			this.nextMonthBtn.disableSelection();


			this.yearInput = new sb.element({
				tag : 'input',
				type : 'text',
				size : 10,
				styles : {
					display : 'none',
					width : '130px',
					margin : '2px auto 2px auto'
				}
			});

			this.yearInput.appendTo(this.calendar);
			

			this.days = new sb.element({
				tag : 'div',
				className : 'sb_datepicker_days',
				innerHTML : 'days'
			});

			this.days.appendTo(this.calendar);
			this.days.disableSelection();

			this.help = new sb.element({
				tag : 'div',
				innerHTML : '?',
				title : 'see tips',
				styles : {
					textAlign : 'right',
					padding : '3px',
					cursor : 'pointer'
				},
				onclick : function(){
					self.cycleTips();
				}
			});

			this.help.appendTo(this.calendar);

		}
	},

	/**
	@Name: sb.widget.datePicker.prototype._drawCalendar
	@Description: used internally turns calendar data into HTML table
	*/
	_drawCalendar : function(){
		var day = 1;
		var i = 0;
		var html = '';

		var daysInMonth = (32 - new Date(this.calendarYear, this.calendarMonth, 32).getDate());
		var date = new Date(this.calendarYear, this.calendarMonth, 1);
		var fday = date.getDay();

		html += '<table width="100%">';
		html += '<thead><tr>';
		['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'].forEach(function(v){
			html += '<th>'+v+'</th>';
		})
		html += '</tr></thead><tbody>';
		this.prevMonthBtn.style.visibility = '';
		this.nextMonthBtn.style.visibility = '';
		var maxDate = this.maxDate ? new Date(this.maxDate) : maxDate;
		var minDate = this.minDate ? new Date(this.minDate) : minDate;
		while(day <= daysInMonth){
			var day_of_week = i % 7;
			if(day_of_week == 0){
				html += '<tr>';
			}

			if(i < fday){
				html += '<td class="sb_no_day"> </td>';
			}
			else if(i >= fday){
				html += '<td ';
				var month = this.calendarMonth+1;
				if(month < 10){
					month ='0'+month;
				}
				
				var  _day = day < 10 ? '0'+day : day;
				
				var date = new Date(this.getDate(month+'/'+_day+'/'+this.calendarYear));
				if((minDate && date < minDate) || (maxDate && date > maxDate)){

					html += ' class="sb_day_not_allowed" ';
					if(date < minDate){
						this.prevMonthBtn.style.visibility = 'hidden';
					} else {
						this.nextMonthBtn.style.visibility = 'hidden';
					}
				}
				if(this.calendarMonth == this.date.getMonth() && day == this.date.getDate() && this.date.getFullYear() == this.calendarYear){
					html += ' class="sb_day_selected" ';
				}
				html += '>'+day+'</td>';
				day++;
			}
			if(day_of_week == 6){
				html += '</tr>';
			}
			i++;
		}
		html += '</tbody></table>';

		return html;
	}
};

/**
@Name: sb.widget.datePicker.tips
@Description: help screen tips, could be changed overridden for other languages
*/
sb.widget.datePicker.tips = [
'You can use the mouse to select a date.',
'Clicking the arrows icons to the left and right of the title bar moves between months.',
'Use the slider below the header to quickly drag/click between years',
'You can also use the keyboard\'s arrow keys to move around the calendar, then hit enter to select the highlighted date.',
'Hit esc key to hide the date picker.',
'Hitting shift + left arrow key moves back by one month.',
'Hitting shift + right arrow key moves forward by one month.',
'Hitting shift + up arrow moves back by one year.',
'Hitting shift + down arrow moves forward by one year.',
'Alternatively, you can double-click header to bring up manual date entry box.'
];

/**
@Name: sb.widget.datePicker.months
@Description: Array with month names, could be overridden in other language scenarios
*/
sb.widget.datePicker.months = ["January", "Feburary", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

/**
@Name: sb.widget.datePicker.handleKeyEvents
@Description: used internally to handle key events for calendar manipulation
*/
sb.widget.datePicker.handleKeyEvents = function(){

	sb.events.add('html', 'keydown', function(e){
		if(e.target.className == 'sb_date_picker'){
			if(e.keyCode == 9){
				return;
			}

			e.preventDefault();
		}
		var i = sb.widget.datePicker.instance;
		var showing = sb.widget.datePicker.showing;
		if(i && showing){

			e.stopPropagation();
			e.preventDefault();
		}
	});
	sb.events.add('html', 'keyup', function(e){

		if(e.keyCode == 9){
			return;
		}

		var i = sb.widget.datePicker.instance;
		var showing = sb.widget.datePicker.showing;
		if(e.target.className == 'sb_date_picker' && !showing){
			sb.widget.datePicker.display(e);
		}

		switch(e.keyCode){

			//ret
			case 13:
				if(e.target == i.yearInput){
					return;
				}
				i.onDateSelect(i.getDate());
				if(i.target && i.target.focus){
					i.target.focus();
				}
				break;

			//esc
			case 27:
				i.hide();
				break;

			//page up
			case 33:
				if(showing){
					i.switchToMinDate();
					e.preventDefault();
				}
				break;


			//page down
			case 34:
				if(showing){
					i.switchToMaxDate();
					e.preventDefault();
				}
				break;

			//up
			case 38:
				if(showing){
					if(e.shiftKey){
						i.switchToPrevYear();
					} else {
						i.switchToPrevRow();
					}
					e.preventDefault();

				}
				break;
			//left
			case 37:
				if(showing){
					if(e.shiftKey){
						i.switchToPrevMonth();
					} else {
						i.switchToPrevDay();
					}
					e.preventDefault();
				}
				break;

			//right
			case 39:
				if(showing){
					if(e.shiftKey){
						i.switchToNextMonth();
					} else {
						i.switchToNextDay();
					}
					e.preventDefault();
				}
				break;

			//down
			case 40:
				if(showing){
					
					if(e.shiftKey){
						i.switchToNextYear();
					} else {
						i.switchToNextRow();
					}
					
				}

				break;
		}
	});

};