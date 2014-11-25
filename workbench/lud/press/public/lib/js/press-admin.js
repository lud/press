(function($){
	$(function(){

		var datesSpans = $('.dynamic-date');

		function refreshDates() {
			datesSpans.each(function(){
				var dt = moment(this.getAttribute('data-date'));
				$(this).html(dt.fromNow());
			});
		}

		setInterval(refreshDates,5000);
		refreshDates();
	});
}(jQuery));
