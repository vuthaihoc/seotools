<?php

namespace Artesaos\SEOTools;

use Illuminate\Support\Arr;
use Illuminate\Config\Repository as Config;
use Artesaos\SEOTools\Contracts\MetaTags as MetaTagsContract;

class SEOMeta implements MetaTagsContract
{
    /**
     * The meta title.
     *
     * @var string
     */
    protected $title;

    /**
     * The meta title session.
     *
     * @var string
     */
    protected $title_session;

    /**
     * The meta title session.
     *
     * @var string
     */
    protected $title_default;

    /**
     * The title tag separator.
     *
     * @var array
     */
    protected $title_separator;

    /**
     * The meta description.
     *
     * @var string
     */
    protected $description;
	
	/**
	 * m
	 * @var string
	 */
	protected $robots_index = null;
	
	/**
	 * @var string
	 */
	protected $robots_follow = null;

    /**
     * The meta keywords.
     *
     * @var array
     */
    protected $keywords = [];

    /**
     * extra metatags.
     *
     * @var array
     */
    protected $metatags = [];

    /**
     * The canonical URL.
     *
     * @var string
     */
    protected $canonical;

    /**
     * The AMP URL.
     *
     * @var string
     */
    protected $amphtml;

    /**
     * The prev URL in pagination.
     *
     * @var string
     */
    protected $prev;

    /**
     * The next URL in pagination.
     *
     * @var string
     */
    protected $next;

    /**
     * The alternate languages.
     *
     * @var array
     */
    protected $alternateLanguages = [];
    
    /**
     * The alternate medias.
     *
     * @var array
     */
    protected $alternateMedias = [];

    /**
     * The meta robots.
     *
     * @var string
     */
    protected $robots;

    /**
     * @var Config
     */
    protected $config;

    /**
     * The webmaster tags.
     *
     * @var array
     */
    protected $webmasterTags = [
        'google'   => 'google-site-verification',
        'bing'     => 'msvalidate.01',
        'alexa'    => 'alexaVerifyID',
        'pintrest' => 'p:domain_verify',
        'yandex'   => 'yandex-verification',
    ];
	
	/**
	 * @see https://github.com/joshbuchea/HEAD
	 * @var array
	 */
    protected $customTags = [
//    	[
//    		'tag' => 'meta',
//		    'options' => [
//		    	'name' => 'viewport',
//			    'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no',
//		    ],
//	    ]
    ];

    /**
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->setRobots( $this->config->get('defaults.robots_index', null), $this->config->get('defaults.robots_follow', null));
    }

    /**
     * {@inheritdoc}/
     */
    public function generate($minify = false)
    {
        $this->loadWebMasterTags();

        $title = $this->getTitle();
        $description = $this->getDescription();
        $keywords = $this->getKeywords();
        $metatags = $this->getMetatags();
        $canonical = $this->getCanonical();
        $amphtml = $this->getAmpHtml();
        $prev = $this->getPrev();
        $next = $this->getNext();
        $languages = $this->getAlternateLanguages();
        $robots = $this->getRobots();
        $alternateMedias = $this->getAlternateMedias();
        $customTags = $this->customTags;

        $html = [];

        if ($title) {
            $html[] = "<title>$title</title>";
        }

        if ($description) {
            $html[] = "<meta name=\"description\" content=\"{$description}\">";
        }
        
        if ($robots) {
            $html[] = "<meta name=\"robots\" content=\"{$robots}\">";
        }

        if (!empty($keywords)) {
            $keywords = implode(', ', $keywords);
            $html[] = "<meta name=\"keywords\" content=\"{$keywords}\">";
        }

        foreach ($metatags as $key => $value) {
            $name = $value[0];
            $content = $value[1];

            // if $content is empty jump to nest
            if (empty($content)) {
                continue;
            }

            $html[] = "<meta {$name}=\"{$key}\" content=\"{$content}\">";
        }

        if ($canonical) {
            $html[] = "<link rel=\"canonical\" href=\"{$canonical}\"/>";
        }

        if ($amphtml) {
            $html[] = "<link rel=\"amphtml\" href=\"{$amphtml}\"/>";
        }

        if ($prev) {
            $html[] = "<link rel=\"prev\" href=\"{$prev}\"/>";
        }

        if ($next) {
            $html[] = "<link rel=\"next\" href=\"{$next}\"/>";
        }
	
	    foreach ($languages as $lang) {
		    $html[] = "<link rel=\"alternate\" hreflang=\"{$lang['lang']}\" href=\"{$lang['url']}\"/>";
	    }
	    
	    foreach ($alternateMedias as $alternateMedia) {
		    $html[] = "<link rel=\"alternate\" media=\"{$alternateMedia['media']}\" href=\"{$alternateMedia['url']}\"/>";
	    }
	    
	    foreach ($customTags as $customTag){
        	$_html = ["<" . $customTag['tag']];
        	foreach ($customTag['options'] as $k => $v){
        		$_html[] = $k . "=\"" . $v . "\"";
	        }
        	$_html[] = ">";
		    $html[] = implode( " ", $_html);
	    }

        return ($minify) ? implode('', $html) : implode(PHP_EOL, $html);
    }
	
	/**
	 * Add custom tag with options, pass $tag as null/fail to reset
	 * @param string $tag
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function addCustomTag( $tag, array $options = [] ) {
		if($tag == null){
			$this->customTags = [];
		}else{
			$this->customTags[] = [
				'tag' => $tag,
				'options' => $options,
			];
		}
		return $this;
	}
	
	
	/**
	 * {@inheritdoc}
	 */
    public function setTitle($title, $appendDefault = true)
    {
        // clean title
        $title = strip_tags($title);

        // store title session
        $this->title_session = $title;

        // store title
        if (true === $appendDefault) {
            $this->title = $this->parseTitle($title);
        } else {
            $this->title = $title;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitleDefault($default)
    {
        $this->title_default = $default;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitleSeparator($separator)
    {
        $this->title_separator = $separator;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        // clean and store description
        // if is false, set false
        $this->description = (false == $description) ? $description : htmlspecialchars($description, ENT_QUOTES, 'UTF-8', false);
//        $this->description = (false == $description) ? $description : SEOTools::safeValue($description);

        return $this;
    }
	
	/**
	 * Setting meta robots
	 * @param $index
	 * @param $follow
	 */
    public function setRobots($index, $follow = null)
    {
        $this->setRobotsIndex( $index);
        $this->setRobotsFollow( $follow);
    }
	
	/**
	 * Setting meta index
	 * @param $index
	 *
	 * @throws \Exception
	 */
    public function setRobotsIndex($index){
    	if($index === null){
    		$this->robots_index = null;
	    }elseif ($index === false || $index === 'noindex'){
		    $this->robots_index = 'noindex';
	    }elseif ($index === true || $index === 'index'){
		    $this->robots_index = 'index';
	    }else{
		    throw new \Exception("Dont support value(" . $index . ") for robots meta tag");
	    }
    }
	
	/**
	 * Setting meta follow
	 * @param $follow
	 *
	 * @throws \Exception
	 */
    public function setRobotsFollow($follow){
	    if($follow === null){
		    $this->robots_follow = null;
	    }elseif ($follow === false || $follow === 'nofollow'){
		    $this->robots_follow = 'nofollow';
	    }elseif ($follow === true || $follow === 'follow'){
		    $this->robots_follow = 'follow';
	    }else{
		    throw new \Exception("Dont support value(" . $follow . ") for robots meta tag");
	    }
    }

    /**
     * {@inheritdoc}
     */
    public function setKeywords($keywords)
    {
        if (!is_array($keywords)) {
            $keywords = explode(', ', $keywords);
        }

        // clean keywords
        $keywords = array_map('strip_tags', $keywords);

        // store keywords
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addKeyword($keyword)
    {
        if (is_array($keyword)) {
            $this->keywords = array_merge($keyword, $this->keywords);
        } else {
            $this->keywords[] = strip_tags($keyword);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeMeta($key)
    {
        Arr::forget($this->metatags, $key);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMeta($meta, $value = null, $name = 'name')
    {
        // multiple metas
        if (is_array($meta)) {
            foreach ($meta as $key => $value) {
                $this->metatags[$key] = [$name, $value];
            }
        } else {
            $this->metatags[$meta] = [$name, $value];
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCanonical($url)
    {
        $this->canonical = $url;

        return $this;
    }

    /**
     * Sets the AMP html URL.
     *
     * @param string $url
     *
     * @return MetaTagsContract
     */
    public function setAmpHtml($url)
    {
        $this->amphtml = $url;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrev($url)
    {
        $this->prev = $url;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNext($url)
    {
        $this->next = $url;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAlternateLanguage($lang, $url)
    {
        $this->alternateLanguages[] = ['lang' => $lang, 'url' => $url];

        return $this;
    }

    /**
     * Add an alternate media.
     *
     * @param string $media media or devices
     * @param string $url
     *
     * @return MetaTagsContract
     */
    public function addAlternateMedia($media, $url)
    {
        $this->alternateLanguages[] = ['media' => $media, 'url' => $url];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAlternateLanguages(array $languages)
    {
        $this->alternateLanguages = array_merge($this->alternateLanguages, $languages);

        return $this;
    }

    /**
     * Add alternate medias.
     *
     * @param array $alternateMedias
     *
     * @return MetaTagsContract
     */
    public function addAlternateMedias(array $alternateMedias)
    {
        $this->alternateMedias = array_merge($this->alternateMedias, $alternateMedias);

        return $this;
    }
	
	/**
	 * {@inheritdoc}
	 */
    public function getTitle()
    {
        return $this->title ?: $this->getDefaultTitle();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultTitle()
    {
        if (empty($this->title_default)) {
            return $this->config->get('defaults.title', null);
        }

        return $this->title_default;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitleSession()
    {
        return $this->title_session ?: $this->getTitle();
    }

    /**
     * {@inheritdoc}
     */
    public function getTitleSeparator()
    {
        return $this->title_separator ?: $this->config->get('defaults.separator', ' - ');
    }

    /**
     * {@inheritdoc}
     */
    public function getKeywords()
    {
        return $this->keywords ?: $this->config->get('defaults.keywords', []);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetatags()
    {
        return $this->metatags;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        if (false === $this->description) {
            return;
        }

        return $this->description ?: $this->config->get('defaults.description', null);
    }

    /**
     * Get the robots index.
     *
     * @return string|null
     */
    public function getRobotsIndex()
    {
    	return $this->robots_index;
    }

    /**
     * Get the Robots follow.
     *
     * @return string|null
     */
    public function getRobotsFollow()
    {
        return $this->robots_follow;
    }
	
	/**
	 * Get robots setting as text
	 * @return string
	 */
    public function getRobots(){
    	$robots = [];
    	if($index = $this->getRobotsIndex()){
		    $robots[] = $index;
	    }
    	if($follow = $this->getRobotsFollow()){
		    $robots[] = $follow;
	    }
	    return implode( ", ", $robots);
    }

    /**
     * {@inheritdoc}
     */
    public function getCanonical()
    {
        $canonical_config = $this->config->get('defaults.canonical', false);

        return $this->canonical ?: (($canonical_config === null) ? app('url')->full() : $canonical_config);
    }

    /**
     * Get the AMP html URL.
     *
     * @return string
     */
    public function getAmpHtml()
    {
        return $this->amphtml;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * {@inheritdoc}
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlternateLanguages()
    {
        return $this->alternateLanguages;
    }
	
	/**
	 * Get alternate medias
	 * @return array
	 */
    public function getAlternateMedias(){
    	return $this->alternateMedias;
    }

    /**
     * Reset all data.
     *
     * @return void
     */
    public function reset()
    {
        $this->description = null;
        $this->title_session = null;
        $this->next = null;
        $this->prev = null;
        $this->canonical = null;
        $this->amphtml = null;
        $this->metatags = [];
        $this->keywords = [];
        $this->alternateLanguages = [];
        $this->alternateMedias = [];
        $this->robots_index = null;
        $this->robots_follow = null;
    }

    /**
     * Get parsed title.
     *
     * @param string $title
     *
     * @return string
     */
    protected function parseTitle($title)
    {
        $default = $this->getDefaultTitle();

        if (empty($default)) {
            return $title;
        }
        $defaultBefore = $this->config->get('defaults.titleBefore', false);

        return $defaultBefore ? $default.$this->getTitleSeparator().$title : $title.$this->getTitleSeparator().$default;
    }

    /**
     * Load webmaster tags from configuration.
     */
    protected function loadWebMasterTags()
    {
        foreach ($this->config->get('webmaster_tags', []) as $name => $value) {
            if (!empty($value)) {
                $meta = Arr::get($this->webmasterTags, $name, $name);
                $this->addMeta($meta, $value);
            }
        }
    }
}
