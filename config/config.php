<?php

	return [
		'private_key'        => env( 'SPHINX_PRIVATE_KEY', false ),
		'expired_at'         => '+1 hour',
		'refresh_expired_at' => '+24 hour',
	];
