@if(Press::isEditing())
	<div class="btn-group-sm navbar-right">
		<a class="btn btn-info navbar-btn" href="{{ URL::route('press.stop_editing',['redir'=>Url::current()]) }}">Terminer l'édition</a>
		<a class="btn btn-info navbar-btn" href="{{ URL::route('press.purge_cache',['redir'=>Url::current()]) }}">Vider le cache</a>
		@if($cacheInfo->isCacheStale)
			<a class="btn btn-warning navbar-btn" href="{{ URL::route('press.refresh_page_cache',['key'=>Press::cache()->currentKey()]) }}">Rafraîchir le cache pour cette page</a>
		@endif
	</div>
@endif
