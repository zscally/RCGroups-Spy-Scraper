<?php


require_once('RCGroupsForumSpyScraper.php');


$rcg = new RCGroupsForumSpyScraper();
echo $rcg->getLastPostId();