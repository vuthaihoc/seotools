<?php

namespace Artesaos\SEOTools\Contracts;

use Illuminate\Config\Repository as Config;

interface MetaTags
{
    /**
     * Configuration.
     *
     * @param Config $config
     * @return void
     */
    public function __construct(Config $config);

    /**
     * Generates meta tags.
     * 
     * @param bool $minify
     *
     * @return string
     */
    public function generate($minify = false);
	
	/**
	 * Add custom tag with options, pass $tag as null/fail to reset
	 * @param string $tag
	 * @param array $options
	 *
	 * @return mixed
	 */
    public function addCustomTag($tag, array $options = []);

    /**
     * Set the title.
     *
     * @param string $title
     * @param bool   $appendDefault
     *
     * @return MetaTags
     */
    public function setTitle($title, $appendDefault = true);

    /**
     * Sets the default title tag.
     *
     * @param string $default
     *
     * @return MetaTags
     */
    public function setTitleDefault($default);

    /**
     * Set the title separator.
     *
     * @param string $separator
     *
     * @return MetaTags
     */
    public function setTitleSeparator($separator);

    /**
     * Set the description.
     *
     * @param string $description
     *
     * @return MetaTags
     */
    public function setDescription($description);
	
	/**
	 * Setting meta robots
	 * @param $index
	 * @param $follow
	 */
	public function setRobots($index, $follow = null);
	
	/**
	 * Setting meta index
	 * @param $index
	 *
	 * @throws \Exception
	 */
	public function setRobotsIndex($index);
	
	/**
	 * Setting meta follow
	 * @param $follow
	 *
	 * @throws \Exception
	 */
	public function setRobotsFollow($follow);

    /**
     * Sets the list of keywords, you can send an array or string separated with commas
     * also clears the previously set keywords.
     *
     * @param array $keywords
     *
     * @return MetaTags
     */
    public function setKeywords($keywords);

    /**
     * Add a keyword.
     *
     * @param string|array $keyword
     *
     * @return MetaTags
     */
    public function addKeyword($keyword);

    /**
     * Remove a metatag.
     *
     * @param string $key
     *
     * @return MetaTags
     */
    public function removeMeta($key);

    /**
     * Add a custom meta tag.
     *
     * @param string|array $meta
     * @param string       $value
     * @param string       $name
     *
     * @return MetaTags
     */
    public function addMeta($meta, $value = null, $name = 'name');

    /**
     * Sets the canonical URL.
     *
     * @param string $url
     *
     * @return MetaTags
     */
    public function setCanonical($url);
	
	/**
	 * Sets the AMP html URL.
	 *
	 * @param string $url
	 *
	 * @return MetaTagsContract
	 */
	public function setAmpHtml($url);

    /**
     * Sets the prev URL.
     *
     * @param string $url
     *
     * @return MetaTags
     */
    public function setPrev($url);

    /**
     * Sets the next URL.
     *
     * @param string $url
     *
     * @return MetaTags
     */
    public function setNext($url);

    /**
     * Add an alternate language.
     *
     * @param string $lang language code in format ISO 639-1
     * @param string $url
     * @return MetaTags
     */
    public function addAlternateLanguage($lang, $url);
	
	/**
	 * Add an alternate media.
	 *
	 * @param string $media media or devices
	 * @param string $url
	 *
	 * @return MetaTagsContract
	 */
	public function addAlternateMedia($media, $url);

    /**
     * Add alternate languages.
     *
     * @param array $languages
     *
     * @return MetaTags
     */
    public function addAlternateLanguages(array $languages);
	
	/**
	 * Add alternate medias.
	 *
	 * @param array $alternateMedias
	 *
	 * @return MetaTagsContract
	 */
	public function addAlternateMedias(array $alternateMedias);

    /**
     * Get the title formatted for display.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get the title that was set.
     *
     * @return string
     */
    public function getTitleSession();

    /**
     * Get the title separator that was set.
     *
     * @return string
     */
    public function getTitleSeparator();

    /**
     * Get all metatags.
     *
     * @return array
     */
    public function getMetatags();

    /**
     * Get the Meta keywords.
     *
     * @return array
     */
    public function getKeywords();

    /**
     * Get the Meta description.
     *
     * @return string
     */
    public function getDescription();
	
	/**
	 * Get the robots index.
	 *
	 * @return string|null
	 */
	public function getRobotsIndex();
	
	/**
	 * Get the Robots follow.
	 *
	 * @return string|null
	 */
	public function getRobotsFollow();
	
	/**
	 * Get robots setting as text
	 * @return string
	 */
	public function getRobots();
	

    /**
     * Get the canonical URL.
     *
     * @return string
     */
    public function getCanonical();
	
	/**
	 * Get the AMP html URL.
	 *
	 * @return string
	 */
	public function getAmpHtml();

    /**
     * Get the prev URL.
     *
     * @return string
     */
    public function getPrev();

    /**
     * Get the next URL.
     *
     * @return string
     */
    public function getNext();

    /**
     * Get alternate languages.
     *
     * @return array
     */
    public function getAlternateLanguages();
	
	/**
	 * Get alternate medias
	 * @return array
	 */
	public function getAlternateMedias();

    /**
     * Takes the default title.
     *
     * @return string
     */
    public function getDefaultTitle();

    /**
     * Reset all data.
     *
     * @return void
     */
    public function reset();
}
