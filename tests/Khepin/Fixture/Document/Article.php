<?php

namespace Khepin\Fixture\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class Article
{
    /**
     * @ODM\Id
     */
    private $id;

    /**
     * @ODM\String
     */
    private $title;

    /**
     * @ODM\EmbedOne(targetDocument="Author")
     */
    private $author;

    /**
     * @ODM\EmbedMany(targetDocument="Tag")
     */
    private $tags;

    /**
     * @ODM\String
     *
     * @var type
     */
    private $content;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor(Author $author)
    {
        $this->author = $author;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function addTags(Tag $tag)
    {
        $this->tags[] = $tag;
    }
}
