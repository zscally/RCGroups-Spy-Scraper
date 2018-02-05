# RCGroups Forum Spy Scraper

Simple scraper that can be configured to scrap RCGroups forums.

For the most part, This thing looks like it could be ran ever 1 second. If I was to automate the scan 
I would set up a crontab to run this script every 3 seconds, unfortunately crons are limited to every 1min and I feel
that the nature of how the forum spy works you could miss out on a post. for this reason you could setup PHP to allow
unlimited execution time with

```
set_time_limit(0);
```

Now for those that would rather have a more hands off approach you could setup a deamon, however I will not cover that 
in this repo. I will provide a bash script that will run the script in a infinate loop and sleep for 3 seconds.

below you would put this in a file called scraper.sh chmod it to 0777 and execute it like so

`./scraper.sh &` 

this will put the script in the background.

below will execute the script indefinitely.

```
#!/bin/bash
while true
do
	php /path/to/script.php
	sleep 3
done
```


Example: 

### Very Basic scrap for all results
```
<?php
set_time_limit(0);

/**
 * require our class 
 */
require_once('RCGroupsForumSpyScraper.php');

/**
 * set up the new object
 */
$RCG = new RCGroupsForumSpyScraper();

/**
 * Scrap the results
 */
$results = $RCG->scrap();

/**
 * print off the results
 */
print_r($results);
```

### More Detailed filtered down.
```
<?php
set_time_limit(0);

/**
 * require our class 
 */
require_once('RCGroupsForumSpyScraper.php');

/**
 * set up the new object
 */
$RCG = new RCGroupsForumSpyScraper();

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
```

### Slack Integration Example:

Now that we have the scraper taken care of the way you wish to receive alerts is totally up to you. in the examples
below I will show you how I use slack to send out a message with a link to the post if one is found.


```
<?php


require_once('RCGroupsForumSpyScraper.php');


/**
 * Grab your Token: Go to https://api.slack.com/web to create your access-token. The token will look somewhat like this:
 * xoxo-2100000415-0000000000-0000000000-ab1ab1
 *
 * https://api.slack.com/custom-integrations/legacy-tokens
 *
 * @param string $message The message to post into a channel.
 * @param string $channel The name of the channel prefixed with #, example #foobar
 * @return boolean
 */
function slack($message, $channel)
{
    $ch = curl_init("https://slack.com/api/chat.postMessage");
    $data = http_build_query([
        "token" => "",
        "channel" => $channel, //"#mychannel",
        "text" => $message, //"Hello, Foo-Bar channel message.",
        "username" => "rcg_spy_scraper",
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


/**
 * set up the new object
 */
$RCG = new RCGroupsForumSpyScraper();


/**
 * define the keywords we wish to filter on.
 */
$RCG->keywords = ['x9d', 'qx7', 'Taranis', 'astrox', 'alien'. 'goggles', 'kiss', 'plane', 'car', 'betaflight', 'motors'];

$RCG->forums = [
    'Aircraft - Electric - Multirotor (FS/W)',
    'Aircraft - General - Radio Equipment (FS/W)'
];

/**
 * Scrap the results
 */
$results = $RCG->scrap();

/**
 * print off the results
 */
if(!empty($results)) {
    $rcgurl = 'https://www.rcgroups.com/forums/showthread.php?';
    $message = "Found post within the following Threads '" . implode("', '", $RCG->forums) . "' \n";
    $message = "with the following Keywords '" . implode("', '", $RCG->keywords) . "' \n\n";


    /**
     * Example:
     *         (
     * [what] => New Post
     * [when] => Today 01:04 PM
     * [title] => Durafly Bf-109E 1100mm - Official owners thread
     * [urltitle] => Durafly-Bf-109E-1100mm-Official-owners-thread
     * [preview] => Yeah, that's why triggered it I'm sure. For yucks, I dropped a DF Mk1 Spit in my cart just now. When I went to check out, sure enough, free shipping. Good Job HK! Sure hope it...
     * [poster] => FLTRI
     * [threadid] => 2767987
     * [postid] => 39009623
     * [lastpost] => 1516298646
     * [userid] => 454055
     * [forumid] => 248
     * [forumname] => Electric Warbirds
     * )
     */
    foreach($results as $post)
    {
        $message .= "Title: *{$post->title}*\n";
        $message .= "When: *{$post->when}*\n";
        $message .= "URL: " . $rcgurl . $post->threadid . '-' . $post->urltitle . "\n";
        $message .= "Preview: {$post->preview}\n\n";
        $message .= "----------------------------------------------------------\n\n";
    }

    slack(print_r($message, true), 'rcgroup-spyscraper');
}

```


# voila!!!


#### TODO 

1. Better examples on how to implement with alerting
2. Better filtering 

#### Contributing

1. Star & Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D



