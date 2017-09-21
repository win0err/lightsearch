<?php declare(strict_types=1);
/**
 * Copyright (c) 2017 Sergei Kolesnikov
 */

namespace win0err\LightSearch\Entity;

class Indexable
{

    /**
     * @var int
     */
    protected $id = null;
    /**
     * @var string
     */
    protected $externalId;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var \DateTime
     */
    protected $date;
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $hash;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var float
     */
    protected $rating;


    /**
     * Indexable constructor.
     *
     * @param int       $id
     * @param string    $externalId
     * @param string    $title
     * @param \DateTime $date
     * @param string    $url
     * @param string    $hash
     */
    public function __construct($id = null, $externalId = null, $title = null, \DateTime $date = null, $url = null, $hash = null, $rating = 1.0)
    {
        $this->id = $id;
        $this->externalId = $externalId;
        $this->title = $title;
        $this->date = $date;
        $this->url = $url;
        $this->hash = $hash;
        $this->rating = $rating;
    }


    /**
     * @return string
     */
    public function getExternalId(): string
    {
        return (string)$this->externalId;
    }

    /**
     * @param string $externalId
     */
    public function setExternalId(string $externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return (string)$this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return (string)$this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return (string)$this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text)
    {
        $this->text = $text;

        return $this;
    }


    /**
     * @return string
     */
    public function getHash(): string
    {
        return (string)$this->hash;
    }

    /**
     * @return string
     */
    public function getCalculatedHash(): string
    {
        return md5(serialize([$this->getTitle(), $this->getText()]));
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getRating(): float
    {
        return (float)$this->rating;
    }

    /**
     * @param float $rating
     */
    public function setRating(float $rating)
    {
        $this->rating = $rating;

        return $this;
    }
}
