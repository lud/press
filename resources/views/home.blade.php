
@extends('layouts.base')


@section('title')Home Page Title @stop

@section('content')
	@foreach ($articles as $article)
		<a href="{{ $article->url() }}">{{ $article->url() }}</a><br/>
	@endforeach

	<h3>Todo</h3>
	<ul>
		<li>Sélectionner le parser en fonction de l'extension</li>
		<li>Récup un fichier à partir d'une URL (schema 2 file) sans l'extension spécifiée</li>
		<li>Bouger tout ce qui concerne Path/URL dans un package <code>Press</code></li>
	</ul>
@stop
