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
