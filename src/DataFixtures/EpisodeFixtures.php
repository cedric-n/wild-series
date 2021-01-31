<?php


namespace App\DataFixtures;


use App\Entity\Episode;
use App\Service\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    private $slug;

    public function __construct(Slugify $slug) {
        $this->slug = $slug;
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        // TODO: Implement load() method.
        $faker = Faker\Factory::create('en_US');


            for ($p= 0; $p <= 5; $p++) {

                for ($y = 0; $y < 5; $y++) {

                    for ($e = 0; $e < 10; $e++) {

                        $episode = new Episode();
                        $episode->setNumber($e);
                        $episode->setTitle($faker->jobTitle);
                        $episode->setSynopsis($faker->sentence);
                        $episode->setSeason($this->getReference('season_' . $y . $p));
                        $url = $this->slug->generate($episode->getTitle());
                        $episode->setSlug($url);
                        $manager->persist($episode);

                    }

                }

            }


        $manager->flush();

    }

    public function getDependencies()
    {
        return [SeasonFixtures::class];
    }
}
