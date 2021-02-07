<?php

namespace Artesaos\SEOTools;

use Illuminate\Support\Arr;
use Illuminate\Config\Repository as Config;
use Artesaos\SEOTools\Contracts\MetaTags as MetaTagsContract;

/**
 * SEOMeta provides implementation for `MetaTags` contract.
 *
 * @see \Artesaos\SEOTools\Contracts\MetaTags
 */
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
        'norton'   => 'norton-safeweb-site-verification',
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
            $html[] = Arr::get($this->config, 'add_notranslate_class', false) ? "<title class=\"notranslate\">$title</title>" : "<title>$title</title>";
        }

        if ($description) {
            $html[] = "<meta name=\"description\" content=\"{$description}\">";
        }

        if (!empty($keywords)) {
            
            if($keywords instanceof \Illuminate\Support\Collection){
                $keywords = $keywords->toArray();
            }
            
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

        if ($robots) {
            $html[] = "<meta name=\"robots\" content=\"{$robots}\">";
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
     *
     * @param string $tag
     * @param array $options
     *
     * @return mixed
     */
    public function addCustomTag( $tag, array $options = [] ) {
        if ( $tag == null ) {
            $this->customTags = [];
        } else {
            $this->customTags[] = [
                'tag'     => $tag,
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
        // open redirect vulnerability fix
        $title = str_replace(['http-equiv=', 'url='], '', $title);
        
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
     * Sets the meta robots.
     *
     * @param string $robots
     *
     * @return MetaTagsContract
     */
    public function setRobots($robots)
    {
        $this->robots = $robots;
        
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
	 * Get meta robots.
	 *
	 * @return string
	 */
	public function getRobots()
	{
		return $this->robots ?: $this->config->get('defaults.robots', null);
	}
	
	/**
	 * Get alternate medias
	 * @return array
	 */
    public function getAlternateMedias(){
    	return $this->alternateMedias;
    }

    /**
     * {@inheritdoc}
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
        $this->robots = null;
        $this->alternateMedias = [];
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
