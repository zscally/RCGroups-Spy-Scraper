<?php
/**
 * require our class
 */
require_once('RCGroupsForumSpyScrapper.php');

/**
 * set up the new object
 */
$RCG = new RCGroupsForumSpyScrapper();

/**
 * Define the fourms we wish to look in
 */
$RCG->forums = [
    'Aircraft - Electric - Multirotor (FS/W)',
    'Aircraft - General - Radio Equipment (FS/W)'
];

/**
 * define the keywords we wish to filter on.
 */
$RCG->keywords = ['x9d', 'qx7', 'Taranis', 'astrox', 'alien'. 'goggles'];

/**
 * Scrap the results
 */
$results = $RCG->scrap();

/**
 * print off the results
 */
print_r($results);