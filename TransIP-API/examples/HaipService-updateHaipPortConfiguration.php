<?php

/**
 * This example updates a port configuration
 *
 * @copyright Copyright 2017 TransIP BV
 * @author TransIP BV <support@transip.nl>
 */

// Include haipservice
require_once('Transip/HaipService.php');

try {
    // Update the HA-IP port configuration by haip name and port configuration id
    $haip = Transip_HaipService::updateHaipPortConfiguration('example-haip', 1, 'HTTP', 80, 'http');

    print_r($haip);
} catch (SoapFault $f) {
    // It is possible that an error occurs when connecting to the TransIP Soap API,
    // those errors will be thrown as a SoapFault exception.
    echo 'An error occurred: ' . $f->getMessage(), PHP_EOL;
}
