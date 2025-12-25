<?php

$GLOBALS['TYPO3_CONF_VARS']['SYS']['messenger']['routing'][\WapplerSystems\MessengerDemo\Message\DemoJobMessage::class] = 'messenger-demo';

/*
$GLOBALS['TYPO3_CONF_VARS']['SYS']['messenger']['transports']['messenger-demo'] = [
    'dsn' => 'doctrine://default?queue_name=messenger-demo',
    'options' => [
        'table_name' => 'tx_messengerdemo_queue',
        'queue_name' => 'messenger-demo'
    ]
];
*/
