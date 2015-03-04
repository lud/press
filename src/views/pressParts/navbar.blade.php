<div class="navbar navbar-default navbar-press" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="/">
				{{ Config::get("press.brand") }}
			</a>
		</div>
		<div class="collapse navbar-collapse">
			<ul class="nav navbar-nav">
				<!-- <li><a href="#about">About</a></li> -->
				<!-- <li><a href="#contact">Contact</a></li> -->
			</ul>

			@include('press::pressParts.edit_actions')
		</div>
	</div>
</div>
