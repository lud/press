
@extends('layouts.base')


@section('title')Home Page Title @stop

@section('content')
	@foreach ($articles as $article)
		<a href="{{ $article->url() }}">{{ $article->url() }}</a><br/>
	@endforeach

	<h3>Todo</h3>
	<ul>
		<li>Bouger tout ce qui concerne Path/URL dans un package <code>Press</code></li>
		<li>Utiliser un middleware de cache</li>
	</ul>
@stop

