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
#[UniqueEntity(fields:'title',message:"une autre annonce a déja éte ce titre, veuillez en changer")]


class Ad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    //#[Assert\Length(min:10,max:255,minMessage:" le titre doit faire + de 10 caractères",maxMessage:"votre message est trop long pas plus de de 255 caractères")]

    private $id;


  
    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\Column(type: 'string', length: 255)]
    private $slug;

    #[ORM\Column(type: 'float')]
    private $price;

    #[ORM\Column(type: 'text')]
    // #[Assert\Length(min:30,minMessage:" le titre doit faire + de 10 caractères"),]
     private $introduction;

     #[ORM\Column(type: 'text')]
     #[Assert\Length(min:100,minMessage:" Merci de mettre au moins 100 caractères"),]
     private $content;

     #[ORM\Column(type: 'string', length: 255)]
    private $coverImage;

    
    #[ORM\Column(type: 'integer')]
    private $rooms;

    #[ORM\OneToMany(mappedBy: 'ad', targetEntity: Image::class, orphanRemoval:true)]
    
    private Collection $images;

    #[ORM\ManyToOne(inversedBy: 'ads')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\OneToMany(mappedBy: 'ad', targetEntity: Booking::class)]
    private Collection $bookings;

    #[ORM\OneToMany(mappedBy: 'ad', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * Creation dune fonction pour permettre d initilisaer le slug(avant la persitance et avant la maj)
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]

        public function initiliazeSlug(){

            if(empty($this->slug)){

                $slugify = new Slugify();
                $this->slug = $slugify->slugify($this->title);
            }
        }

    /**
     *  fonction pour récupérer le commentaire d'un auteur par rapport à une annonce
     *
     * @param User $author
     * @return Comment|null
     */
    public function getCommentFromAuthor(User $author){

        foreach($this->comments as $comment){

            if($comment->getAuthor() === $author) return $comment;
        }
    }

    // fonction pour gérer la moyenne des notes des annonces
    public function getAverageRatings(){

        // Calcul de la somme des notes
        $sum = array_reduce($this->comments->toArray(),function($total,$comment){

            // On retourne le total + la note de chaque commentaire
            return $total + $comment->getRating();

        },0);

        // Diviser le total par le nombre de notes
        if(count($this->comments) > 0)return $sum / count($this->comments);
        return 0;
    }

    // fonction pour récupérer les jours déjà réservés et consertir les dates en nombres numériques
    public function getNotAvailableDays(){

        $notAvailableDays = [];

        foreach($this->bookings as $booking){

            // $resultat = range(10,20,2); =>[10,12,14,16,18,20]
            // $resultat = range(03-20-2019,03-25-2019) =[]
            $resultat = range(
                                $booking ->getStartDate()->getTimestamp(),
                                $booking ->getEndDate()->getTimestamp(),
                                24 * 60 * 60
            );

            $days = array_map(function($dayTimestamp){

                return new \DateTime(date('Y-m-d',$dayTimestamp));
            },$resultat);

            $notAvailableDays = array_merge($notAvailableDays,$days);
        }

        return $notAvailableDays;
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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

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
            $booking->setAd($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getAd() === $this) {
                $booking->setAd(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAd($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAd() === $this) {
                $comment->setAd(null);
            }
        }

        return $this;
    }
}
