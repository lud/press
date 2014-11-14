
@extends('layouts.base')

@section('content')
	@foreach ($articles as $article)
		<a href="{{ $article->url() }}">
			{{ $article->get('title', $article->id) }}
		</a>
		<br/>
	@endforeach

	<h3>Todo</h3>
	<ul>
		<li>Bouger tout ce qui concerne Path/URL dans un package <code>Press</code></li>
		<li>Utiliser un middleware de cache</li>
	</ul>

	{!! $paginator->render() !!}
@stop

