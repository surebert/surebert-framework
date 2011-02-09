sb.include('Element.prototype.disableSelection');

sb.widget.datePicker = function(params){

	var cal = sb.widget.datePicker.instance ? sb.widget.datePicker.instance : this;
	
	sb.objects.infuse(params, cal);
	cal.setDate(cal.date);
	this.parentNode = sb.$(this.parentNode) || document.body;
	
	sb.widget.datePicker.instance = cal;
	cal.show();
	return cal;
};
sb.widget.datePicker.showing = false;
sb.widget.datePicker.instance = false;
sb.widget.datePicker.init = function(){
	this.display = function(e){
		var target = e.target;
		if(e.target.hasClassName('sb_date_picker')){
			e.preventDefault();
			var x = new sb.widget.datePicker({
				date : e.target.value,
				target : e.target,
				minDate : e.target.attr('sb_min_date'),
				maxDate : e.target.attr('sb_max_date')
			});
		}
	};
	sb.events.add('html', 'keydown', function(e){
		if(e.target.className == 'sb_date_picker'){
			if(e.keyCode == 9){
				return;
			}
			e.preventDefault();
		}
	});
	sb.events.add('html', 'keyup', function(e){
		
		if(e.keyCode == 9){
			return;
		}

		if(!e.shiftKey){
			sb.widget.datePicker.display(e);
		}
		var i = sb.widget.datePicker.instance;
		switch(e.keyCode){

				//ret
				case 13:
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
					i.switchToMinDate();
					break;


				//page down
				case 34:
					i.switchToMaxDate();
					break;

				//up
				case 38:
					
					break;
				//left
				case 37:
					if(e.shiftKey){
						i.switchToPrevMonth();
					} else {
						i.switchToPrevDay();
					}
					break;

				//right
				case 39:
					if(e.shiftKey){
						i.switchToNextMonth();
					} else {
						i.switchToNextDay();
					}
					break;

				//down
				case 40:
					
					break;
			}
	});

	sb.events.add('html', 'click', sb.widget.datePicker.display);

};


sb.widget.datePicker.prototype = {
	months : ["January", "Feburary", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
	calendar : false,
	calendarDay : '',
	calendarYear : '',
	calendarMonth : '',
	 onClick : function(){},
	 onHeaderClick : function(){},
	 onExceedsMinDate : function(date){alert(date+' exceeds min date: '+this.minDate);},
	 onExceedsMaxDate : function(date){alert(date+' exceeds max date: '+this.maxDate);},
	 onInvalidDate : function(date){alert('bad date format');},
	 onNextMonthClick : function(){},
	 onDateSelect : function(date){
		 this.target.value = date;
		 this.hide();
	 },

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

		this.updateCalendar();
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

		this.updateCalendar();
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
				tds.nodes[selectedIndex].className = '';
				prev_td.className = 'sb_day_selected';
				this.setDate(this.getDate(this.calendarMonth+1+'/'+(tds.nodes[selectedIndex].innerHTML)+'/'+this.calendarYear));
			}
		}

	},

	switchToMaxDate : function(){
		if(this.maxDate){
			this.setDate(this.maxDate);
		}
		
		this.updateCalendar();
		if(this.debug){
			console.log(this.calendarMonth+' '+this.calendarYear);
		}

	},

	switchToMinDate : function(){
		if(this.minDate){
			this.setDate(this.minDate);
		}

		this.updateCalendar();
		if(this.debug){
			console.log(this.calendarMonth+' '+this.calendarYear);
		}

	},

	setDate : function(date){
	
		if(!sb.date.isDate(date)){
			var date = new Date();
		} else if(!date.getFullYear){
			var date = new Date(date);
		}
		
		if(this.minDate){
			var minDate = new Date(this.minDate);
			if(date < minDate){
				this.onExceedsMinDate(this.getDate(this.minDate));
				return false;
			}
		}

		if(this.maxDate){
			var maxDate = new Date(this.maxDate);
			if(this.date > maxDate){
				this.onExceedsMaxDate(this.getDate(this.maxDate));
				return false;
			}
		}
		
		this.date = date;
		this.calendarDay = this.date.getDate();
		this.calendarYear = this.date.getFullYear();
		this.calendarMonth = this.date.getMonth();

	},

	getDate : function(date){
		var date = date ? new Date(date) : this.date;
		var month = date.getMonth()+1;
		month = month < 10 ? '0'+month : month;
		var day = date.getDate();
		day = day < 10 ? '0'+day : day;
		var year = date.getFullYear();
		return [month, day, year].join('/');
	},

	createCalendar : function(){
		
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
									if(self.setDate(target.value) !== false){
										self.updateCalendar();
										self.currentYear.replace(target);
										self.onDateSelect(self.getDate());
									};

								} else {
									self.yearInput.value = self.getDate();
									self.onInvalidDate();
								}
							} else if(e.keyCode == 27){
								self.currentYear.replace(target);
							}
						}
					},
					dblclick : function(e){
						var target = e.target;
						if(target == self.currentYear){
							self.yearInput.value = self.getDate();
							self.yearInput.replace(target);
							self.yearInput.select();
							self.yearInput.focus();
						}
					},
					click : function(e){
						
						var target = e.target;
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
							if(td == false || td.className == 'sb_no_day' ||  td.className == 'sb_day_not_allowed'){return false;}
							 var month = self.calendarMonth+1;
							 if(month < 10){
								 month ='0'+month;
							 }
							 var day = td.innerHTML;
							 if(day < 10){
								 day ='0'+day;
							 }
							 if(self.setDate(month+'/'+day+'/'+self.calendarYear) !== false && typeof self.onDateSelect === 'function'){
								self.onDateSelect(self.getDate());
								if(self.target && self.target.focus){
									self.target.focus();
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
				cursor : 'pointer'
			});
			this.nextMonthBtn = sb.$(this.tr.insertCell(2));
			this.nextMonthBtn.innerHTML = '&raquo;';
			this.nextMonthBtn.className = 'sb_datepicker_next_month';
			this.nextMonthBtn.styles({
				textAlign : 'right',
				cursor : 'pointer'
			});
			this.nextMonthBtn.disableSelection();

			this.days = new sb.element({
				tag : 'div',
				className : 'sb_datepicker_days',
				innerHTML : 'days'
			});

			this.days.appendTo(this.calendar);
			this.days.disableSelection();



		}
	},
	updateCalendar : function(){
		this.currentYear.innerHTML = this.months[parseInt(this.calendarMonth)]+' '+ this.calendarYear;
		this.days.innerHTML = this.getDaysHTML();

	},
	hide : function(){
		sb.widget.datePicker.showing = false;
		this.calendar.remove();
		
	},

	show : function(){
		var self = this;
		sb.widget.datePicker.showing = true;
		var target = this.target;
		if(target.blur){target.blur();}
		var yPos = target.getY().toString();
        var xPos = target.getX();
		this.createCalendar();
		
		this.calendar.styles({
			left: xPos.toString(),
			top: yPos,
			position: 'absolute',
			zIndex: 1000
		});

		this.updateCalendar();
		/*if(this.blurEvent){
			sb.events.remove(this.blurEvent);
		} else {
			this.blurEvent = sb.events.add(this.target, 'blur', function(e){
				alert('s');
				self.hide();});
		}*/
		
		this.calendar.appendTo(this.parentNode);
		if(!this.sizeSet){
			this.calendar.style.width = this.days.getWidth()+'px';
			this.sizeSet = true;
		}

		this.yearInput = new sb.element({
			tag : 'input',
			type : 'text',
			size : 10,
			fontSize : '0.8em',
			value : this.calendarYear

		});

		this.target = target;
	},

	days_in_month: function(){
        return 32 - new Date(this.calendarYear, this.calendarMonth, 32).getDate();
    },
    first_day_of_month: function(){
        var date = new Date(this.calendarYear, this.calendarMonth, 1);
        return date.getDay();
    },

	getDaysHTML : function(){
        var day = 1;
        var i = 0;
		var html = '';

        var days_in_month = this.days_in_month();
        var fday = this.first_day_of_month();

        html += '<table width="100%">';
		html += '<thead><tr>';
		['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'].forEach(function(v){html += '<th>'+v+'</th>';})
		html += '</tr></thead><tbody>';
		this.prevMonthBtn.style.visibility = '';
		this.nextMonthBtn.style.visibility = '';
		var maxDate = this.maxDate ? new Date(this.maxDate) : maxDate;
		var minDate = this.minDate ? new Date(this.minDate) : minDate;
        while(day <= days_in_month){
            var day_of_week = i % 7;
            if(day_of_week == 0){
                html += '<tr>';
            }

            if(i < fday){
                html += '<td class="sb_no_day"> </td>';
            }
            else if(i >= fday){
                html += '<td sb_day="1" ';
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
    },

	reset : function(){
		this.box.innerHTML = '';
	}
};