<?php

$config = [
    'url'          => 'fritz.box',          // your Fritz!Box IP (or set '192.168.178.1' or ...)
    'user'         => 'dslf_config',        // your Fritz!Box user ('dslf_config' is the standard TR-064 user)
    'password'     => 'xxxxxxxxx',          // your Fritz!Box user password
    'http' => [                 // http client options are directly passed to Guzzle http client
        // 'verify' => false,   // uncomment to disable certificate check
    ],
    'device'       => '',       // the label of your desired device e.g. 'Bens-iPhone'
    'filter'       => 'Unbeschränkt',       // e.g. 'Kinder' (the label of your desired filter)
];