<?php


namespace App\DataFixtures;


use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        // TODO: Implement load() method.
        $faker = Faker\Factory::create('en_US');
        $y = 0;
        $z = 0;
        for ($i = 0; $i < 20; $i++) {

            $season = new Season();
            $season->setDescription($faker->sentence);
            $season->setYear($faker->year);
            $y = rand(0,5);
            $season->setProgram($this->getReference('program_' . $y));
            $season->setNumber($faker->numberBetween(1,10));
            $manager->persist($season);
            $this->setReference('season_' . $z, $season);
            $z++;
        }

        $manager->flush();


    }

    public function getDependencies()
    {
        return [ProgramFixtures::class];
    }
}
