<div class="container">
	@if(Novel::isEditing())
		Date du cache {{ date('d/m/Y \à H:i:s',Novel::cache()->current()->cache_at) }}
		<br/>
		Date des fichiers {{ date('d/m/Y \à H:i:s',Novel::index()->getModTime()) }}
		<br/>
		@if (Novel::index()->getModTime() > Novel::cache()->current()->cache_at)
			<small>Le cache n'est pas à jour par rapport à l'index des fichiers.</small>
		@else
			<small>Le cache est à jour.</small>
		@endif
		<br/>
		<a href="{{ Url::route('press.stop_editing') }}">Terminer l'édition</a>
		<br/>
		<a href="{{ Novel::cache()->currentRefreshURL() }}">Rafraîchir la page</a>
		<br/>
		<a href="{{ Url::route('press.purge_cache') }}">Vider le cache</a>
	@else
		En cache depuis le {{ date('d/m/Y \à H:i:s',time()) }}
		&mdash;
		<a href="{{ Url::route('press.editing') }}">Éditer</a>
	@endif
</div>
