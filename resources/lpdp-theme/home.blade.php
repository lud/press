@extends ('press::layouts.base')


<svg id="home-banner">
	<a xlink:href="{{ URL::route('press::tag',['test']) }}">
		<text x="10" y="15" fill="white">This is a test !</text>
	</a>
</svg>

@section('content')
	@foreach ($articles as $article)
		<a href="{{ $article->url() }}">
			{{ $article->get('title', $article->id) }}
		</a>
		<br/>
	@endforeach

	{!! $paginator->render() !!}
@stop
