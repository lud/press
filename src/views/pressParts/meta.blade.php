@if (isset($meta))
@if(isset($meta->title))<title>{{ $meta->title }}</title>@endif

@if(isset($meta->description))<meta name="description" content="{{ $meta->description }}" />@endif

@if(isset($meta->keywords))<meta name="keywords" content="{{ $meta->keywords }}" />@endif
@endif
