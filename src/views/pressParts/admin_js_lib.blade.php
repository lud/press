@if(Press::isEditing())
	<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.3/moment-with-locales.min.js"></script>
	<script>
		moment.locale('{{ App::getLocale() }}');
	</script>
	<script src="{{ URL::asset('packages/lud/press/lib/js/press-admin.js') }}"></script>
@endif
