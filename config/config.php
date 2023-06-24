<?php

	use Illuminate\Database\Eloquent\Model;

	return [

		/*
		|--------------------------------------------------------------------------
		| Secret key
		|--------------------------------------------------------------------------
		|
		| A static secret key that will use for encode and decode of the wrapper
		| token.
		|
		*/
		'secret'               => env( 'SPHINX_SECRET_KEY', '' ),

		/*
		|--------------------------------------------------------------------------
		| Access token expiration
		|--------------------------------------------------------------------------
		|
		| Determine the expiration time of the access token.
		|
		*/
		'access_expired_at'    => '+1 hour',

		/*
		|--------------------------------------------------------------------------
		| Refresh token expiration
		|--------------------------------------------------------------------------
		|
		| Determine expiration time of the refresh token.
		|
		*/
		'refresh_expired_at'   => '+24 hour',

		/*
		|--------------------------------------------------------------------------
		| Role model class
		|--------------------------------------------------------------------------
		|
		| The respondent model for roles. related model must implement
		| findAndCache method and also, keep up-to-date cached instances.
		|
		*/
		'role_model'           => Model::class,

		/*
		|--------------------------------------------------------------------------
		| Permissions separator
		|--------------------------------------------------------------------------
		|
		| Determine the separator for permissions
		|
		*/
		'permission_separator' => '-',
	];
