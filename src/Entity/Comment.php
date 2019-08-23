<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $body;

    /**
     * @ORM\Column(type="datetime")
     */
    private $publishedat;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\tricks", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tricksId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\user", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $authorId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $editedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getPublishedat(): ?\DateTimeInterface
    {
        return $this->publishedat;
    }

    public function setPublishedat(\DateTimeInterface $publishedat): self
    {
        $this->publishedat = $publishedat;

        return $this;
    }

    public function getTricksId(): ?tricks
    {
        return $this->tricksId;
    }

    public function setTricksId(?tricks $tricksId): self
    {
        $this->tricksId = $tricksId;

        return $this;
    }

    public function getAuthorId(): ?user
    {
        return $this->authorId;
    }

    public function setAuthorId(?user $authorId): self
    {
        $this->authorId = $authorId;

        return $this;
    }

    public function getEditedAt(): ?\DateTimeInterface
    {
        return $this->editedAt;
    }

    public function setEditedAt(\DateTimeInterface $editedAt): self
    {
        $this->editedAt = $editedAt;

        return $this;
    }
}
