<?php


use Freekattema\Tools\Routing\Route;


Route::get('/test', function () {
	echo 'Hello world!';
});

Route::get('/about', function () {
	echo 'This is the about page';
});

Route::get('/contact', function () {
	echo 'This is the contact page';
});

Route::subdomain('image', function () {
	Route::get('/{name}/{age}', function ($name, $age) {
		echo "Hello $name, you are $age years old";
	});
});

Route::handleRequest();
