<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use ORM\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookingRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\HasLifecycleCallbacks()]

class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $booker = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ad $ad = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\Type("DateTimeInterface",message:'Le Format doit être une date')]
    #[Assert\GreaterThan("now",message:"La date d'arrivée doit être ultérieure à la date d'aujourd'hui")]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\Type("DateTimeInterface",message:'Le Format doit être une date')]
    #[Assert\GreaterThan(propertyPath:"startDate",message:"La date de départ doit être plus éloignée que la date d'arrivée")]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    // Callback appelé à chaque fois que on crée une réservation
    #[ORM\PrePersist]
    
    /**
     * @return Response
     */

    public function prePersist(){

        if(empty($this->createdAt)){
            $this->createdAt = new \DateTime();
        }    

        if(empty($this->amount)){

            // prix de l'annonce * nombre de jour
            $this->amount = $this->ad->getPrice() * $this->getDuration();
        }
    }

    public function isBookabledays(){

        // il faut connaitre des dates déjà réservées
        $notAvailableDays = $this->ad->getNotAvailableDays();

        // il faut connaitre les dates en cours de réservation
        $bookingDays = $this->getDays();

        // Comparaison
        $notAvailableDays = array_map(function($day){
            return $day->format('Y-m-d');
        },$notAvailableDays);

        $days = array_map(function($day){
            return $day->format('Y-m-d');
        },$bookingDays);

        // on retourne vrai ou faux (si on retrouve ou pas les dates choisi)
        foreach($days as $day){

            if(array_search($day,$notAvailableDays) !== false) return false;
        }
        return true;

    }

    public function getDays(){

        $resultat = range($this->startDate->getTimestamp(),$this->endDate->getTimestamp(),24*60*60);
        $days = array_map(function($dayTimestamp){

            return new \DateTime(date('Y-m-d',$dayTimestamp));
        },$resultat);

        return $days;
    }

    // Calcul du nombre de jours du séjour
    public function getDuration(){

        $difference = $this->endDate->diff($this->startDate);
        return $difference->days;

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAd(): ?Ad
    {
        return $this->ad;
    }

    public function setAd(?Ad $ad): self
    {
        $this->ad = $ad;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
