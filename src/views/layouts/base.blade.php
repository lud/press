<!doctype html>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->


@include('press::pressParts.meta')

@section('assets_styles')
@foreach ($themeAssets['styles'] as $styleHref)
	<link href="{{ $styleHref }}" type="text/css" rel="stylesheet"/>
@endforeach
@show

@section('top')
	@include('press::pressParts.navbar')
@show

@section('full_content')
	<div class="container">
		@include('press::pressParts.edit_actions')
		@yield('content')
		@include('press::pressParts.cache_infos')
	</div>
@show

@section('assets_scripts')
	@foreach ($themeAssets['scripts'] as $scriptSrc)
		<script src="{{ $scriptSrc }}" type="text/javascript"></script>
	@endforeach
@show

@include('press::pressParts.admin_js_lib')
