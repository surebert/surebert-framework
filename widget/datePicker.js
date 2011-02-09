sb.include('Element.prototype.disableSelection');

sb.widget.datePicker = function(params){

	var cal = sb.widget.datePicker.instance ? sb.widget.datePicker.instance : this;
	
	if(cal.target){
		cal.hide();
	}
	sb.objects.infuse(params, cal);
	cal.parentNode = cal.parentNode || document.body;
	cal.setDate(cal.date);

	sb.widget.datePicker.instance = cal;
	cal.show();
	return cal;
};

sb.widget.datePicker.instance = false;

sb.widget.datePicker.prototype = {
	months : ["January", "Feburary", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
	calendar : false,
	calendarDay : '',
	calendarYear : '',
	calendarMonth : '',
	onHide : function(){},
	onShow : function(){},
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
			this._minDate = new Date(this.minDate);
			if(date < this._minDate){
				this.onExceedsMinDate(this.getDate(this.minDate));
				return false;
			}
		}

		if(this.maxDate){
			this._maxDate = new Date(this.maxDate);
			if(this.date > this._maxDate){
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
							if(td == false || td.className == 'sb_no_day' ||  td.className == 'sb_day_not_allowed'){ return false;}
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
		console.log('hide');
		this.untrackDocKeyPress();

		this.calendar.remove();
		this.onHide();
	},

	trackDocKeyPress : function(){
		var self = this;
		if(this.onDocKeyPress){
			this.untrackDocKeyPress();
		}
		this.onDocKeyPress = sb.events.add('html', 'keydown', function(e){
			switch(e.keyCode){
				//up
				case 38:
					self.switchToMaxDate();
					break;
				//left
				case 37:
					self.switchToPrevMonth();
					break;

				//right
				case 39:
					self.switchToNextMonth();
					break;

				//down
				case 40:
					self.switchToMinDate();
					break;
			}
		});
	},

	untrackDocKeyPress :function(){
		 sb.events.remove(this.onDocKeyPress);
		this.onDocKeyPress = false;
	},
	show : function(){
		var self = this;
		
		var target = this.target;
		var yPos = target.getY().toString();
        var xPos = target.getWidth() + target.getX();
		this.createCalendar();
		this.trackDocKeyPress();
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
		
		this.calendar.appendTo(document.body);
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

		this.onShow();
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
				if(date < this._minDate || date > this._maxDate){

					html += ' class="sb_day_not_allowed" ';
					if(date < this._minDate){
						this.prevMonthBtn.style.visibility = 'hidden';
					} else {
						this.nextMonthBtn.style.visibility = 'hidden';
					}
				}
				if(this.calendarMonth == this.date.getMonth() && day == this.date.getDate() && this.date.getFullYear() == this.calendarYear){
					html += ' class="sb_today" ';
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