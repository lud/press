<div class="navbar navbar-default navbar-static-top navbar-press" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="{{ URL::route('press.home') }}">
				{{ Config::get("app.project_name","app.project_name") }}
			</a>
		</div>
		<div class="collapse navbar-collapse">
			<ul class="nav navbar-nav">
				<li class="active"><a href="{{ URL::route('press.home') }}">Home</a></li>
				<!-- <li><a href="#about">About</a></li> -->
				<!-- <li><a href="#contact">Contact</a></li> -->
			</ul>
		</div>
	</div>
</div>
