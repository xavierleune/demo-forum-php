<?php

namespace App\Entity;

use Elastica\ArrayableInterface;

class Link implements ArrayableInterface
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $link;

    /**
     * @var string
     */
    private $image;

    /**
     * @var string
     */
    private $description;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Link
     */
    public function setTitle(string $title): Link
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return Link
     */
    public function setLink(string $link): Link
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     * @return Link
     */
    public function setImage(string $image): Link
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Link
     */
    public function setDescription(string $description): Link
    {
        $this->description = $description;
        return $this;
    }

    public function getHost(): string
    {
        return parse_url($this->getLink(), PHP_URL_HOST);
    }

    public function toArray()
    {
        return [
            'title' => $this->getTitle(),
            'link' => $this->getLink(),
            'image' => $this->getImage(),
            'description' => $this->getDescription(),
            'host' => $this->getHost(),
            'date' => (new \DateTime())->format('c')
        ];
    }
}
