<?php

	use Illuminate\Database\Eloquent\Model;

	return [
		'secret'             => env( 'SPHINX_SECRET_KEY', '' ),
		'access_expired_at'  => '+1 hour',
		'refresh_expired_at' => '+24 hour',
		'role_model'         => Model::class,
	];
