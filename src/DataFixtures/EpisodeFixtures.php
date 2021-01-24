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

        for ($i = 0; $i < 40; $i++) {

            $episode = new Episode();
            $episode->setNumber($faker->numberBetween(1,18));
            $episode->setTitle($faker->jobTitle);
            $episode->setSynopsis($faker->sentence);
            $y = $faker->numberBetween(0,19);
            $episode->setSeason($this->getReference('season_' . $y));
            $url = $this->slug->generate($episode->getTitle());
            $episode->setSlug($url);
            $manager->persist($episode);
        }

        $manager->flush();

    }

    public function getDependencies()
    {
        return [SeasonFixtures::class];
    }
}
