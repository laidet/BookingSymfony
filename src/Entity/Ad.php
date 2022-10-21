<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use App\Repository\AdRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AdRepository::class)]
#[ORM\HasLifecycleCallbacks]
/**
 * Gérer les doublons de titre
 * @UniqueEntity(
 * fields={"title"},
 * message="Une autre annonce a déjà ce titre, veuillez en changer"
 * )
 */
class Ad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    /**
     * @Assert\Length(min=10,minMessage="Le titre doit faire + de 10 caractères", maxMessage="Votre titre est trop long, pas plus de 255 caractères")
     */
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(type: Types::TEXT)]
    /**
     * @Assert\Length(min=30,minMessage="Merci de mettre au moins 30 caractères")
     *
     * @var string|null
     */
    private ?string $introduction = null;

    #[ORM\Column(type: Types::TEXT)]
    /**
     * @Assert\Length(min=100,minMessage="Merci de mettre au moins 100 caractères")
     *
     * @var string|null
     */
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $coverImage = null;

    #[ORM\Column]
    private ?int $rooms = null;

    #[ORM\OneToMany(mappedBy: 'ad', targetEntity: Image::class, orphanRemoval:true)]
    
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * Création d'une fonction pour permettre d'initialiser le slug (avant la persistance et avant le mise à jour)
     * 
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     */

        public function initiliazeSlug(){

            if(empty($this->slug)){

                $slugify = new Slugify();
                $this->slug = $slugify->slugify($this->title);
            }
        }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(string $coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(int $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setAd($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getAd() === $this) {
                $image->setAd(null);
            }
        }

        return $this;
    }
}
