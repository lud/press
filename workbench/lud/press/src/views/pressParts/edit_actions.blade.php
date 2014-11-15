		<a class="btn btn-info" href="{{ Url::route('press.stop_editing') }}">Terminer l'édition</a>
		<a class="btn btn-info" href="{{ Url::route('press.purge_cache') }}">Vider le cache</a>
		@if($cacheInfo->isCacheStale)
		<a class="btn btn-warning" href="{{ Press::cache()->currentRefreshURL() }}">Rafraîchir le cache pour cette page</a>
		@endif
