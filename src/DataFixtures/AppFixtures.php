<?php

namespace App\DataFixtures;


use Faker\Factory;
use App\Entity\User;
use App\Entity\Photo;
use App\Entity\Trick;
use App\Entity\Video;
use App\Entity\Comment;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\GroupTrickFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private SluggerInterface $slugger;
    public function __construct(UserPasswordHasherInterface $userPasswordHasher, SluggerInterface $slugger)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->slugger = $slugger;

    }
    public function load(ObjectManager $manager): void
    {
        // 2-les catégories
        // 3-Les Tricks
        // 4-les commentaires

        $faker = Factory::create('fr_FR');
        $users = [];

        $tricks = [
            1 => [
                'name' => 'Tail Press',
                'group' => 'butters',
                'videos' => [
                    1 => 'https://youtu.be/LNDVil48oN4',
                    2 => 'https://youtu.be/Kv0Ah4Xd8d0'
                ]
            ],
            2 => [
                'name' => 'Nose Press',
                'group' => 'butters',
                'videos' => [
                    1 => 'https://youtu.be/Px2YuKQVS_g',
                    2 => 'https://youtu.be/qEVaINaRpf0'
                ]
            ],
            3 => [
                'name' => 'Tripod',
                'group' => 'butters',
                'videos' => [
                    1 => 'https://youtu.be/msD1jQL63dA',
                    2 => 'https://youtu.be/88GJqNWZ5kY',
                    3 => 'https://youtu.be/P6crQSwDjJY'
                ]
            ],
            4 => [
                'name' => 'Stalefish',
                'group' => 'grabs',
                'videos' => [
                    1 => 'https://youtu.be/f0PyFsOcnIw',
                    2 => 'https://youtu.be/f9FjhCt_w2U'
                ]
            ],
            5 => [
                'name' => 'Weddle',
                'group' => 'grabs',
                'videos' => [
                    1 => 'https://youtu.be/c1vfTvXjVxY'
                ]
            ],
            6 => [
                'name' => 'Melon',
                'group' => 'grabs',
                'videos' => [
                    1 => 'https://youtu.be/OMxJRz06Ujc',
                    2 => 'https://youtu.be/MAj_pzqmC4o',
                    3 => 'https://youtu.be/FjxRYEMHyLw'
                ]
            ],
            7 => [
                'name' => 'Wildcat',
                'group' => 'spins',
                'videos' => [
                    1 => 'https://youtu.be/7KUpodSrZqI',
                    2 => 'https://youtu.be/_wWahwZc4ZA'
                ]
            ],
            8 => [
                'name' => 'Tamedog',
                'group' => 'spins',
                'videos' => [
                    1 => 'https://youtu.be/gevwK7GxZAQ',
                    2 => 'https://youtu.be/qvnsjVJCbA0'
                ]
            ],
            9 => [
                'name' => 'Frontside Boardslide',
                'group' => 'rails',
                'videos' => [
                    1 => 'https://youtu.be/WRjNFodnOHk',
                    2 => 'https://youtu.be/xczPvfa2LIk',
                    3 => 'https://youtu.be/Ri3eR8yrvR8'
                ]
            ],
            10 => [
                'name' => 'Backside Lipslide',
                'group' => 'rails',
                'videos' => [
                    1 => 'https://youtu.be/pfkiK_RBsNc',
                    2 => 'https://youtu.be/8dVcv-_1WLE'
                ]
            ]
        ];

        for ($i = 0; $i < 4; $i++) {
            $user = new User();
            $user->setUsername($faker->name);
            $user->setEmail("user$i@snowtricks.com");
            $user->setRoles(['ROLE_USER_VERIFIED']);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, "passwordUser$i"));
            $user->setAvatarPath("avatar-$i.jpg");
            $user->setActive(true);
            $users[] = $user;
            $manager->persist($user);
        }



        foreach ($tricks as $trickName) {
            $trick = new Trick();
            $trick->setName($trickName['name']);
            $trick->setDescription($faker->paragraphs(3, true));
            $trick->setSlug($this->slugger->slug($trick->getName())->lower());
            $trick->setFeaturedImage($trick->getSlug() . '-featured.jpg');
            $trick->setUpdatedAt(new \DateTimeImmutable);
            $trick->setAuthor($faker->randomElement($users));
            $trick->setGroupTrick($this->getReference($trickName['group']));
            $manager->persist($trick);

            for ($j = 0; $j < 3; $j++) {
                $photo = new Photo();
                $photo->setPath($trick->getSlug() . '-' . $j . '.jpg');
                $photo->setTrick($trick);
                $manager->persist($photo);
            }

            foreach ($trickName['videos'] as $videoURL) {
                $video = new Video();
                $video->setEmbed($videoURL);
                $video->setTrick($trick);
                $manager->persist($video);

            }

            for ($k = 0; $k < mt_rand(0, 20); $k++) {
                $comment = new Comment();
                $comment->setContent($faker->sentences(mt_rand(1, 4), true));
                $comment->setAuthor($faker->randomElement($users));
                $comment->setTrick($trick);
                $manager->persist($comment);
            }

        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [GroupTrickFixtures::class];
    }
}