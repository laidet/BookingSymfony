<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Image;
use App\DataFixtures\AppFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{

    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder){

        $this->encoder=$encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('FR-fr');
        
        // Gestion des roles
            $adminRole = new Role();
            $adminRole->setTitle('ROLE_ADMIN');
            $manager->persist($adminRole);

        // Création d'un utilisateur spécial avec un role admin

            $adminUser = new User();
            
            $adminUser  ->setFirstName('chagrelle')
                        ->setLastName('valérie')
                        ->setEmail('valoche1669@aol.com')
                        ->setHash($this->encoder->hashPassword($adminUser,'password'))
                        ->setAvatar('https://randomuser.me/api/portraits/women/10.jpg
                        ')
                        ->setIntroduction($faker->sentence())
                        ->setDescription("<p>".join("</p><p>",$faker->paragraphs(5)). "</p>")
                        ->addUserRole($adminRole)
                        ;
            
            $manager->persist($adminUser);

        $users = [];
        $genres=['male','female'];

        // Utilisateurs

                    for($i=1; $i<=10;$i++){

                        $user = new User();
                        $genre=$faker->randomElement($genres);
                        $avatar ='https://randomuser.me/api/portraits/';
                        $avatarId = $faker->numberBetween(1,99).'.jpg';
                        $avatar .= ($genre == 'male' ? 'men/' : 'women/') . $avatarId;
                        $hash = $this->encoder->hashPassword($user,'password');

                        $title = $faker->sentence();

                        $description = "<p>".join("</p><p>",$faker->paragraphs(5)). "</p>";
                        $user->setDescription($description)
                            ->setFirstname($faker->firstname)
                            ->setLastname($faker->lastname)
                            ->setEmail($faker->email)
                            ->setIntroduction($faker->sentence())
                            ->setHash($hash)
                            // ->setSlug(str_replace(" ", "-", $title))
                            ->setAvatar($avatar)
                            ;
                        
                        $manager->persist($user);
                        $users[]=$user;
                    }

        // Annonces
        
                    for($i=1; $i<=30;$i++){
                        $ad = new Ad();

                        $title = $faker->sentence();
                        $coverImage = $faker->imageUrl(1000,350);
                        $introduction = $faker->paragraph(2);
                        $content = "<p>".join("</p><p>",$faker->paragraphs(5)). "</p>";
                        $user = $users[mt_rand(0,count($users)-1)];

                        $ad->setTitle("$title")
                            ->setCoverImage("$coverImage")
                            ->setIntroduction("$introduction")
                            ->setContent("$content")
                            ->setPrice(mt_rand(30,200))
                            ->setRooms(mt_rand(1,5))
                            // ->setSlug(str_replace(" ", "-", $title))
                            ->setAuthor($user)
                            ;

                        $manager->persist($ad);

                        for($j=1;$j<=mt_rand(2,5);$j++){

                            // on crée une nouvelle instance de l'entité Image
                            $image = new Image();
                            $image  ->setUrl($faker->imageUrl())
                                    ->setCaption($faker->sentence())
                                    ->setAd($ad);

                            // on sauvegarde en faisant persister
                            $manager->persist($image);
                        }
                    }

        $manager->flush();
    }
}


