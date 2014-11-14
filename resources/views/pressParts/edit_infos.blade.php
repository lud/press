<p>
	<span class="label label-info">
		Fichiers :
		<span class="dynamic-date" data-date="{{ date('Y-m-d H:i:s',$cacheInfo->indexMTime) }}">
			{{ date('d/m/Y \à H:i:s',$cacheInfo->indexMTime) }}
		</span>
	</span>
</p>
<p>
	@if ($cacheInfo->isCacheStale)
		<span class="label label-warning">
	@else
		<span class="label label-info">
	@endif
		Cache :
		<span class="dynamic-date" data-date="{{ date('Y-m-d H:i:s',$cacheInfo->cacheTime) }}">
			{{ date('d/m/Y \à H:i:s',$cacheInfo->cacheTime) }}
		</span>
	</span>
</p>
