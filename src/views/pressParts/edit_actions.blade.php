@if(Press::isEditing())
	<div class="btn-group-sm" style="float:right">
		<a class="btn btn-info" href="{{ URL::route('press.stop_editing',['redir'=>Url::current()]) }}">Terminer l'édition</a>
		<a class="btn btn-info" href="{{ URL::route('press.purge_cache',['redir'=>Url::current()]) }}">Vider le cache</a>
		@if($cacheInfo->isCacheStale)
			<a class="btn btn-warning" href="{{ URL::route('press.refresh_page_cache',['key'=>Press::cache()->currentKey()]) }}">Rafraîchir le cache pour cette page</a>
		@endif
	</div>
@endif
