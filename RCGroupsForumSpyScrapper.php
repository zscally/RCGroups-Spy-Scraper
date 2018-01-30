<?php
/*****
 * RCGroups Fourm scrapper for keywords. this script will look at the form
 * spy and watch for keywords in the title, preview context and or by user
 * @date 01/07/2018
 * @author Zachary Scally
 * @github https://github.com/zscally
 */

class RCGroupsForumSpyScrapper
{
    /**
     * URL to the main RCG forum spy page this is needed so we can grab the max post ID from the Javascript
     * @var string
     */
    private $RCGForumSpyURL = 'https://www.rcgroups.com/forums/spy.php';

    /**
     * This is the XML url that sends us the payload using the last post ID
     * @var string
     */
    private $RCGForumSpyXMLURL = 'https://www.rcgroups.com/forums/spyxml.php?last=';

    /**
     * This is the last post ID used to with the XML url to pull the results
     *
     * @var null
     */
    private $lastPostId = null;

    /**
     * Keys words can be anything, if set to empty it will look at everything
     *
     * @var array
     */
    public $keywords = [];

    /**
     * Poster is the person that posted the new thread or new post, you can specific who that person is
     * and only results found by that person will be returned
     *
     * @var array
     */
    public $posters = [];

    /**
     * with forms you can be specific to the forum you with to look in if nothing is pass we'll look at everything
     *
     * @var array
     */
    public $forums = [];

    /**
     * Look in what determines if we're only looking at new threads or new post, this would be handy to weed out
     * looking at people post about the topic at hand.
     *
     * @var array
     */
    public $lookInWhat = [];

    /**
     * this method sets the last post id
     *
     * @param $lastPostId
     */
    public function setLastPostId($lastPostId)
    {
        $this->lastPostId = $lastPostId;
    }

    /**
     * This method will return the last post id, if the last post ID is null then we'll try to fetch one.
     *
     * @return int
     */
    public function getLastPostId()
    {
        if( is_null($this->lastPostId) )
        {
            $this->setLastPostId($this->getAndSetFirstPostId());
        }
        return $this->lastPostId;
    }

    /**
     * This method will reach out to RCGroups  scrap the last post ID from the page source code.
     *
     * @return mixed
     */
    private function getAndSetFirstPostId()
    {
        $html = file_get_contents($this->RCGForumSpyURL);
        preg_match('#var highestid = (.*?);\s*$#m', $html, $matches);
        return $matches[1];
    }

    /**
     * This method will pull in the XML payload from the forum spy and return a simpleXML object.
     *
     * @return SimpleXMLElement
     */
    private function pullAndParseXML()
    {
        $xml = file_get_contents($this->RCGForumSpyXMLURL . $this->getLastPostId());
        if($xml)
        {
            return simplexml_load_string($xml);
        }
    }

    /**
     * This method fetches the data and return the XML object parsing it out in the indivigual arrays.
     *
     * @return array
     */
    private function getData()
    {
        $dataarr = [];
        $data = $this->pullAndParseXML();
        if( !empty($data) )
        {
            foreach($data->events->event as $eventObject)
            {
                $dataarr[] = $eventObject;
            }
        }
        return $dataarr;
    }

    /**
     * This method looks at what type of post'
     * Example: 'New Post', 'New Thread'
     *
     * @param $needle
     * @return bool
     */
    private function lookInWhat($needle)
    {
        return in_array($needle, $this->lookInWhat);
    }

    /**
     * This method looks at who posted the forum element.
     *
     * @param $needle
     * @return bool
     */
    private function byWho($needle)
    {
        return in_array($needle, $this->posters);
    }

    /**
     * This method looks at specific forums
     *
     * @param $needle
     * @return bool
     */
    private function inForums($needle)
    {
        return in_array($needle, $this->forums);
    }

    /**
     * This method looks at an array of keywords inside the forum object.
     *
     * @param $post
     * @return bool
     */
    private function hasKeywords($post)
    {
        foreach($this->keywords as $keyword)
        {
            $input = preg_quote($keyword, '~');
            $result = preg_grep('~' . $input . '~', get_object_vars($post));

            if( !empty( $result ) )
            {
                return true;
            }
        }
        return false;
    }

    /**
     * This is the magic that ties all this together, this will grab the data apply the filters
     * and return the found results
     *
     * @return array
     */
    public function scrap()
    {
        $results = [];
        $data = $this->getData();
        if( ! empty( $data ) )
        {
            foreach( $data as $post )
            {
                //check if the post is in the threads we care about
                if( ! empty($this->lookInWhat) && ! $this->lookInWhat($post->what) )
                {
                    continue;
                }

                if( ! empty($this->posters) && ! $this->byWho($post->poster) )
                {
                    continue;
                }

                if( ! empty($this->forums) &&  ! $this->inForums($post->forumname) )
                {
                    continue;
                }

                if( ! empty($this->keywords) && ! $this->hasKeywords($post) )
                {
                    continue;
                }
                $results[] = $post;
            }

            return $results;
        }
    }
}