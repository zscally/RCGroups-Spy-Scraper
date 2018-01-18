<?php


require_once('RCGroupsForumSpyScrapper.php');


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
        "username" => "rcg_spy_scrapper",
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
$RCG = new RCGroupsForumSpyScrapper();


/**
 * define the keywords we wish to filter on.
 */
//$RCG->keywords = ['x9d', 'qx7', 'Taranis', 'astrox'];

$RCG->forums = [
    'Aircraft - Electric - Multirotor (FS/W)',
    'Aircraft - General - Radio Equipment (FS/W)',
    'FPV Equipment (FS/W)'
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
    $message .= "with the following Keywords '" . implode("', '", $RCG->keywords) . "' \n\n";


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

    slack(print_r($message, true), 'rcgroup-spyscrapper');
}
