# PRESS

Press is a blogging library targeting Laravel 5 with a focus on blogging speed, with automagic pagination and page caching.

[![Code Climate](https://codeclimate.com/github/lud/press/badges/gpa.svg)](https://codeclimate.com/github/lud/press)

### Routes sample config

```php
<?php

\Press::SetRoutes();

Route::controllers([
	'auth' => 'App\Http\Controllers\Auth\AuthController',
]);

\Press::listRoute('tag/{tag}', 'tag|sort', ['as' => 'press.tag']);
\Press::listRoute('/', 'dir:articles|sort', ['as' => 'press.home', 'view' => '_::home']);
```


