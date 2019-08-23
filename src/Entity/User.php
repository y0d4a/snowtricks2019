<?php

// src/Entity/User.php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @UniqueEntity(fields="email", message="Email already taken")
 * @UniqueEntity(fields="username", message="Username already taken")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * The below length depends on the "algorithm" you use for encoding
     * the password, but this works well with bcrypt.
     *
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @var string le token qui servira lors de l'oubli de mot de passe
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $resetToken;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Tricks", mappedBy="Author")
     */
    private $tricksAuthor;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Tricks", mappedBy="Editor")
     */
    private $tricksEditor;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="authorId", orphanRemoval=true)
     */
    private $comments;

    public function __construct()
    {
        $this->roles = array('ROLE_USER');
        $this->tricksAuthor = new ArrayCollection();
        $this->tricksEditor = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }
    public function __toString()
    {
        return $this->getUsername();
    }
    // other properties and methods
    public function getId()
    {
        return $this->id;
    }
    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getResetToken()
    {
        return $this->resetToken;
    }

    /**
     * @param string $resetToken
     */
    public function setResetToken(?string $resetToken): void
    {
        $this->resetToken = $resetToken;
    }

    public function getSalt()
    {
        // The bcrypt and argon2i algorithms don't require a separate salt.
        // You *may* need a real salt if you choose a different encoder.
        return null;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return Collection|Tricks[]
     */
    public function getTricksAuthor(): Collection
    {
        return $this->tricksAuthor;
    }

    public function addTricksAuthor(Tricks $tricksAuthor): self
    {
        if (!$this->tricksAuthor->contains($tricksAuthor)) {
            $this->tricksAuthor[] = $tricksAuthor;
            $tricksAuthor->setAuthor($this);
        }

        return $this;
    }

    public function removeTricksAuthor(Tricks $tricksAuthor): self
    {
        if ($this->tricksAuthor->contains($tricksAuthor)) {
            $this->tricksAuthor->removeElement($tricksAuthor);
            // set the owning side to null (unless already changed)
            if ($tricksAuthor->getAuthor() === $this) {
                $tricksAuthor->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tricks[]
     */
    public function getTricksEditor(): Collection
    {
        return $this->tricksEditor;
    }

    public function addTricksEditor(Tricks $tricksEditor): self
    {
        if (!$this->tricksEditor->contains($tricksEditor)) {
            $this->tricksEditor[] = $tricksEditor;
            $tricksEditor->setEditor($this);
        }

        return $this;
    }

    public function removeTricksEditor(Tricks $tricksEditor): self
    {
        if ($this->tricksEditor->contains($tricksEditor)) {
            $this->tricksEditor->removeElement($tricksEditor);
            // set the owning side to null (unless already changed)
            if ($tricksEditor->getEditor() === $this) {
                $tricksEditor->setEditor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuthorId($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAuthorId() === $this) {
                $comment->setAuthorId(null);
            }
        }

        return $this;
    }
}