function TimezoneDetect() {
	var dtDate = new Date('1/1/' + (new Date()).getUTCFullYear());
	var intOffset = 10000; //set initial offset high so it is adjusted on the first attempt
	var intMonth;

	// Go through each month to find the lowest offset to account for DST
	for (intMonth = 0; intMonth < 12; intMonth++) {
			//go to the next month
			dtDate.setUTCMonth(dtDate.getUTCMonth() + 1);

			// To ignore daylight saving time look for the lowest offset.
			// Since, during DST, the clock moves forward, it'll be a bigger number.
			if (intOffset > (dtDate.getTimezoneOffset() * (-1))) {
				intOffset = (dtDate.getTimezoneOffset() * (-1));
			}
	}

	var isNeg = (intOffset < 0);
	if (isNeg)
		intOffset = intOffset * (-1);
	var h = Math.floor(intOffset / 60);
	var m = intOffset % 60;

	intOffset = (isNeg ? '-' : '+') + (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;

	return intOffset;
}

(function(jQuery) {
	// const tzid = Intl.DateTimeFormat(); //.resolvedOptions(); //.timeZone;
	// console.log(tzid);

	const tzoff = TimezoneDetect();
	// console.log(tzoff);

	$.cookie('_timezone', tzoff, { path:'/', expires:365 });

})(jQuery || this.jQuery || window.jQuery);
