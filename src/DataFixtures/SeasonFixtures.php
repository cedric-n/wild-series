<?php


namespace App\DataFixtures;


use App\Entity\Program;
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
        $z = 0;
        for ($i = 0; $i <= 5; $i++) {

            for ($y = 0; $y < 5; $y++) {

                $season = new Season();
                $season->setDescription($faker->sentence);
                $season->setYear($faker->year);
                $season->setProgram($this->getReference('program_' . $y));
                $season->setNumber($i+1);
                $manager->persist($season);
                $this->setReference('season_' . $y . $i, $season);

            }

        }



        $manager->flush();


    }

    public function getDependencies()
    {
        return [ProgramFixtures::class];
    }
}
