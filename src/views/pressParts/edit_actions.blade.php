@if(Press::isEditing())
	<div class="btn-group-sm" style="float:right">
		<a class="btn btn-info" href="{{ URL::route('press.stop_editing') }}">Terminer l'édition</a>
		<a class="btn btn-info" href="{{ URL::route('press.purge_cache') }}">Vider le cache</a>
		@if($cacheInfo->isCacheStale)
			<a class="btn btn-warning" href="{{ Press::cache()->URLToRefreshCurrent() }}">Rafraîchir le cache pour cette page</a>
		@endif
	</div>
@endif
