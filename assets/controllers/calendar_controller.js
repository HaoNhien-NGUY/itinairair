import { Controller } from '@hotwired/stimulus';
import { Calendar } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/styles/index.min.css';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['calendar', 'startDaySelect', 'endDaySelect', 'startDayDisplay', 'endDayDisplay'];
    static values = {
        startDate: {type: String, default: ''},
        endDate: {type: String, default: ''},
        dayMapping: Object,
        isRange: {type: Boolean, default: false},
        inputMode: {type: Boolean, default: true},
        selectionTimeMode: {type: Number, default: 0},
        autoHide: {type: Boolean, default: false},
        inModal: {type: Boolean, default: false},
        fullWidth: {type: Boolean, default: false},
        displayWeekDay: {type: Boolean, default: true},
    };
    calendar;
    startDayElement;
    endDayElement;

    initialize() {
        if (this.hasStartDayDisplayTarget) this.startDayElement = this.startDayDisplayTarget.firstElementChild;
        if (this.hasEndDayDisplayTarget) this.endDayElement = this.endDayDisplayTarget.firstElementChild;

        const startDate =  this.startDateValue ? new Date(this.startDateValue) : null;
        const endDate =  this.endDateValue ? new Date(this.endDateValue) : null;
        const options = {
            type: 'default',
            locale: 'fr-FR',
            inputMode: this.inputModeValue,
            enableDates: Object.keys(this.dayMappingValue),
            displayDisabledDates: true,
            selectionTimeMode: this.selectionTimeModeValue,
            selectedTheme: 'light',
            enableEdgeDatesOnly: true,
            enableMonthChangeOnDayClick: false,
            onClickDate: (self) => this.handleDateSelection(self, self.context.selectedDates),
            selectionDatesMode: this.isRangeValue ? 'multiple-ranged' : 'single',
            disableAllDates: this.hasDayMappingValue,
            dateToday: startDate ?? 'today',
            selectedDates: [startDate, endDate ?? ''],
            selectedWeekends: [],
            onShow: (self) => {
                if (this.fullWidthValue) self.context.mainElement.style.width = `${this.element.offsetWidth}px`;
            },
            styles: {
                calendar: `vc border border-gray-300 ${this.inModalValue ? 'z-100' : ''}`,
                dateBtn: 'vc-date__btn text-base!',
                weekDay: 'vc-week__day text-sm!',
                month: 'vc-month font-medium!',
                year: 'vc-year font-medium!',
            },
        };

        if (startDate) this.startDayDisplayTarget.innerText = this.formatDate(startDate);
        if (endDate) this.endDayDisplayTarget.innerText = this.formatDate(endDate);

        this.calendar = new Calendar(this.calendarTarget, options);
        this.calendar.init();
    }

    connect() {
    }

    disconnect() {
        this.calendar?.destroy();
    }

    formatDate(date) {
        const options = {
            day: 'numeric',
            month: 'short'
        }
        if (this.displayWeekDayValue) options.weekday = 'short';

        return date.toLocaleDateString('fr-FR', options);
    }

    handleDateSelection(calendar, selectedDates) {
        if (selectedDates.length === 1) {
            if (this.hasEndDaySelectTarget) this.endDaySelectTarget.value = '';
            if (this.hasEndDayDisplayTarget) this.endDayDisplayTarget.replaceChildren(this.endDayElement);
            if (this.hasStartDayDisplayTarget) this.startDayDisplayTarget.innerText = this.formatDate(new Date(selectedDates[0]));
            this.startDaySelectTarget.value = this.hasDayMappingValue ? (this.dayMappingValue[selectedDates[0]] ?? '') : selectedDates[0];
        } else if (selectedDates.length === 2 && selectedDates[0] !== undefined && selectedDates[1] !== undefined) {
            this.startDaySelectTarget.value = this.hasDayMappingValue ? (this.dayMappingValue[selectedDates[0]] ?? '') : selectedDates[0];
            this.endDaySelectTarget.value = this.hasDayMappingValue ? (this.dayMappingValue[selectedDates[1]] ?? '') : selectedDates[1];
            if (this.hasStartDayDisplayTarget) this.startDayDisplayTarget.innerText = this.formatDate(new Date(selectedDates[0]));
            if (this.hasEndDayDisplayTarget) this.endDayDisplayTarget.innerText = this.formatDate(new Date(selectedDates[1]));
            calendar.hide();
        } else {
            this.startDaySelectTarget.value = '';
            if (this.hasEndDaySelectTarget) this.endDaySelectTarget.value = '';

            if (this.hasStartDayDisplayTarget) this.startDayDisplayTarget.replaceChildren(this.startDayElement);
            if (this.hasEndDayDisplayTarget) this.endDayDisplayTarget.replaceChildren(this.endDayElement);
        }

        if (this.autoHideValue) calendar.hide();
    }
}
