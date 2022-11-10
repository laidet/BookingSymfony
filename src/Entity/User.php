<?php

namespace App\Entity;

use App\Entity\Ad;
use App\Entity\Role;
use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]

#[UniqueEntity(fields: "email", message: "Un autre utilisateur s'est déjà inscrit avec cette adresse mail.")]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank()]
    private $firstname;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank()]
    private $lastname;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Email(message:"Veuillez renseigner un mail valide.")]
    private $email;
    

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url()]
    private $avatar;

    #[ORM\Column(type: 'string', length: 255)]
    private $hash;


    // Comparaison avec le champs hash
    #[Assert\EqualTo(propertyPath:"hash", message: "Les deux mots de passe ne correspondent pas.")]
    public $passwordConfirm;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min:10,minMessage:"Votre Intro doit comporter au moins 10 caractères")]
    private ?string $introduction = null;

    #[ORM\Column(type: 'text')]
    #[Assert\Length(min:100,minMessage:"Votre description doit comporter au moins 100 caractères")]
    private $description;

    #[ORM\Column(type: 'string', length: 255)]
    private $slug;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Ad::class, orphanRemoval: true)]
    private $ads;

    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'users')]
    private Collection $userRoles;

    #[ORM\OneToMany(mappedBy: 'booker', targetEntity: Booking::class)]
    private Collection $bookings;

    public function getFullName(){
        return "{$this->firstname} {$this->lastname}";
    }

    public function __construct()
    {
        $this->ads = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    /**
     * Creation d'une fonction pour permettre d initialiser le slug(avant la persitance et avant la maj)
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]

    public function initiliazeSlug(){

        if(empty($this->slug)){

            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->firstname.' '.$this->lastname);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Ad>
     */
    public function getAds(): Collection
    {
        return $this->ads;
    }

    public function addAd(Ad $ad): self
    {
        if (!$this->ads->contains($ad)) {
            $this->ads->add($ad);
            $ad->setAuthor($this);
        }

        return $this;
    }

    public function removeAd(Ad $ad): self
    {
        if ($this->ads->removeElement($ad)) {
            // définir le côté propriétaire sur null (sauf si déjà modifié)
            if ($ad->getAuthor() === $this) {
                $ad->setAuthor(null);
            }
        }

        return $this;
    }


    public function getRoles():array{

        $roles = $this->userRoles->map(function($role){
            return $role->getTitle(); // map permet de renvoyer un tableau en chaine de caractère 
        })->toArray();

        $roles[]='ROLE_USER';

        return $roles;
    }

    public function getPassword(): string{

        return $this->hash;
    }

    public function getUserIdentifier(): string{

        return $this->email;
    }

    public function eraseCredentials(){}

    /**
     * @return Collection<int, Role>
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(Role $userRole): self
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles->add($userRole);
            $userRole->addUser($this);
        }

        return $this;
    }

    public function removeUserRole(Role $userRole): self
    {
        if ($this->userRoles->removeElement($userRole)) {
            $userRole->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setBooker($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            // définir le côté propriétaire sur null (sauf si déjà modifié)
            if ($booking->getBooker() === $this) {
                $booking->setBooker(null);
            }
        }

        return $this;
    }
    
}