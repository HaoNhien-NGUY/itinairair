import { Controller } from '@hotwired/stimulus';
import { Calendar } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/styles/index.min.css';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['calendar', 'startDaySelect', 'endDaySelect', 'startDayDisplay', 'endDayDisplay'];
    static values = {
        startDate: {type: String, default: ''},
        dayMapping: Object,
        isRange: {type: Boolean, default: false},
        inputMode: {type: Boolean, default: false},
        selectionTimeMode: {type: Number, default: 0},
        autoHide: {type: Boolean, default: false}
    };
    calendar;

    initialize() {
        const startDate =  this.startDateValue ? new Date(this.startDateValue) : null;
        const options = {
            type: 'default',
            locale: 'fr-FR',
            inputMode: this.inputModeValue,
            enableDates: Object.keys(this.dayMappingValue),
            displayDisabledDates: true,
            selectionTimeMode: this.selectionTimeModeValue,
            // positionToInput: ['top', 'center'],
            selectedTheme: 'light',
            enableEdgeDatesOnly: true,
            enableMonthChangeOnDayClick: false,
            onClickDate: (self) => this.handleDateSelection(self, self.context.selectedDates),
            selectionDatesMode: this.isRangeValue ? 'multiple-ranged' : 'single',
            disableAllDates: true,
            dateToday: startDate ?? 'today',
            selectedDates: [startDate],
            selectedWeekends: [],
            styles: {
                calendar: 'vc',
                dateBtn: 'vc-date__btn text-base!',
                weekDay: 'vc-week__day text-sm!',
                month: 'vc-month font-medium!',
                year: 'vc-year font-medium!',
            },
        };

        this.calendar = new Calendar(this.calendarTarget, options);
        this.calendar.init();
    }

    connect() {
    }

    disconnect() {
        this.calendar?.destroy();
    }

    formatDate(date) {
        const [year, month, day] = date.split('-');
        return `${day}/${month}`;
    }

    handleDateSelection(calendar, selectedDates) {
        if (selectedDates.length === 1) {
            if (this.hasEndDaySelectTarget) this.endDaySelectTarget.value = '';
            if (this.hasEndDayDisplayTarget) this.startDayDisplayTarget.innerText = '';
            if (this.hasStartDayDisplayTarget) this.startDayDisplayTarget.innerText = this.formatDate(selectedDates[0]);
            this.startDaySelectTarget.value = this.dayMappingValue[selectedDates[0]] ?? '';
        } else if (selectedDates.length === 2) {
            this.startDaySelectTarget.value = this.dayMappingValue[selectedDates[0]] ?? '';
            this.endDaySelectTarget.value = this.dayMappingValue[selectedDates[1]] ?? '';

            if (this.hasStartDayDisplayTarget) this.startDayDisplayTarget.innerText = this.formatDate(selectedDates[0]);
            if (this.hasEndDayDisplayTarget) this.endDayDisplayTarget.innerText = this.formatDate(selectedDates[1]);
        } else {
            this.startDaySelectTarget.value = '';
            if (this.hasEndDaySelectTarget) this.endDaySelectTarget.value = '';

            if (this.hasStartDayDisplayTarget) this.startDayDisplayTarget.innerText = '';
            if (this.hasEndDayDisplayTarget) this.endDayDisplayTarget.innerText = '';
        }

        if (this.autoHideValue) calendar.hide();
    }
}
