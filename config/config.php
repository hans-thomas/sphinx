<?php
    return [
        'private_key'        => env( 'SPHINX_PRIVATE_KEY', 'XEf8aKCbfucOCDS3utBoN9cEA8eF3PlTtyPkooqpuygSDOCDS3utBoN9ceqzshfJrw' ),
        'expired_at'        => '+1 hour',
        'refreshExpired_at' => '+24 hour',

	    'model' => \App\Models\User::class
    ];
