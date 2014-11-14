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

@if (isset($meta))
	@if($meta->title)
		<title>{{ $meta->title }}</title>
	@endif
	@if($meta->description)
		<meta name="description" content="{{ $meta->description }}" />
	@endif
	@if($meta->keywords)
		<meta name="keywords" content="{{ $meta->keywords }}" />
	@endif
@endif

<link href="{{ URL::asset('lib/css/bootstrap.min.css') }}" rel="stylesheet">
<!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet"/> -->


<style type="text/css">
	/* todo move to file */
	div.footnotes { font-size: 0.9em; }
	div.footnotes-sep1 { width:30px; border-top:3px solid #222; margin:1em 0 0;}
	div.footnotes-sep2 { width:170px; border-top:1px solid #222; margin:0 0 1em;}
	p { text-align: justify; }
	body { padding-bottom: 2em; }
</style>


<body>
	<div class="navbar navbar-default navbar-static-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="{{ URL::route('home') }}">
					{{ Config::get("app.project_name","app.project_name") }}
				</a>
			</div>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					<li class="active"><a href="{{ URL::route('home') }}">Home</a></li>
					<!-- <li><a href="#about">About</a></li> -->
					<!-- <li><a href="#contact">Contact</a></li> -->
				</ul>
			</div>
		</div>
	</div>



	<div class="container">
		@if(Novel::isEditing())
			<div class="btn-group-sm" style="float:right">
				@include('pressParts.edit_actions')
			</div>
		@endif
		@yield('content')
	</div>
	<div class="container">
		@if(Novel::isEditing())
			@include('pressParts.edit_infos')
		@else
			@include('pressParts.user_infos')
		@endif
	</div>
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<!-- Include all compiled plugins (below), or include individual files as needed -->
	@if(Novel::isEditing())
		<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.3/moment-with-locales.min.js"></script>
		<script>
			moment.locale('{{ App::getLocale() }}');
		</script>

		<script src="{{ URL::asset('lib/js/press-admin.js') }}"></script>
	@endif
</body>
