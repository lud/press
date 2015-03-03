@extends('press::layouts.base')

@section('content')
	@foreach ($articles as $article)
		<a href="{{ $article->url() }}">
			{{ $article->get('title', $article->id) }}
		</a>
		<br/>
	@endforeach

	{!! $paginator->render() !!}
@stop

