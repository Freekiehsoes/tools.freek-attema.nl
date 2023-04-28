<?php

require __DIR__ . '/vendor/autoload.php';

function require_glob($pattern)
{
	foreach (glob($pattern) as $filename) {
		require_once $filename;
	}
}

require_glob(__DIR__ . '/routes/*.php');
