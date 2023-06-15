<?php

	use App\Models\User;

	return [
		'private_key'       => env( 'SPHINX_PRIVATE_KEY', false ),
		'expired_at'        => '+1 hour',
		'refreshExpired_at' => '+24 hour', // TODO: snake case
		'model'             => User::class,
		// TODO: use this instead of mode
		'authenticatables'  => [
			User::class
		]
	];
