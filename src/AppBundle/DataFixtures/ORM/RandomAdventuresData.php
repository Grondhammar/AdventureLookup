<?php

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\Adventure;
use AppBundle\Entity\TagContent;
use AppBundle\Entity\TagName;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Faker;
use function Symfony\Component\VarDumper\Tests\Caster\reflectionParameterFixture;

class RandomAdventuresData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->container->get('doctrine');

        $tags = $doctrine->getRepository('AppBundle:TagName')->findAll();

        $faker = Faker\Factory::create();

        // Disable indexing temporarily.
        $doctrine->getManager()->getEventManager()->removeEventSubscriber(
            $this->container->get('search_index_updater')
        );

        for ($i = 0; $i < 200; $i++) {
            $adventure = new Adventure();
            $adventure->setTitle($faker->text(120));

            foreach ($tags as $tag) {
                $info = new TagContent();
                $info
                    ->setAdventure($adventure)
                    ->setTag($tag)
                    ->setApproved(true);
                if ($this->customFaker($tag, $info, $faker)) {
                    // Do nothing.
                } else if ($tag->getType() == 'integer') {
                    $info->setContent($faker->numberBetween(1, 10));
                } else if ($tag->getType() == 'boolean') {
                    $info->setContent($faker->boolean ? '1' : '0');
                } else if ($tag->getType() == 'string') {
                    $info->setContent($faker->name);
                } else {
                    $info->setContent($faker->realText(2000));
                }
                $manager->persist($info);
            }

            $manager->persist($adventure);
        }
        $manager->flush();
    }

    private function customFaker(TagName $tag, TagContent $info, Faker\Generator $faker)
    {
        $fakes = [
            '# of Pages' => function (Faker\Generator $faker) {
                return $faker->numberBetween(20, 200);
            },
            'Year of Release' => function (Faker\Generator $faker) {
                return $faker->numberBetween(1980, 2016);
            },
            'Language' => function (Faker\Generator $faker) {
                return $faker->randomElement([
                    'English',
                    'German',
                    'Italian'
                ]);
            },
            'Link' => function (Faker\Generator $faker) {
                return $faker->url;
            },
            'System / Edition' => function (Faker\Generator $faker) {
                return $faker->randomElement([
                    'DnD 2e',
                    'DnD 3e',
                    'DnD 3.5e',
                    'DnD 4e',
                    'DnD 5e',
                    'Pathfinder',
                    'Advanced DnD',
                ]);
            },
            'Availability' => function (Faker\Generator $faker) {
                return $faker->randomElement([
                    'In Print',
                    'Out of Print',
                    'Print on Demand',
                    'Digital',
                ]);
            },
            'Available Formats' => function (Faker\Generator $faker) {
                return $faker->randomElement([
                    'PDF',
                    'ePub',
                    'Paper',
                ]);
            },
            'Publisher' => function (Faker\Generator $faker) {
                return $faker->company;
            },
            'Setting' => function (Faker\Generator $faker) {
                return $faker->randomElement([
                    'Birthright',
                    'Blackmoor',
                    'Council of Wyrms',
                    'Dark Sun',
                    'Dragon Fist',
                    'Dragonlance',
                    'Time of the Dragon',
                    'Eberron',
                    'Forgotten Realms',
                    'Al-Qadim',
                    'The Horde',
                    'Kara-Tur',
                    'Malatra: The Living Jungle',
                    'Maztica',
                    'Ghostwalk',
                    'Greyhawk',
                    'Jakandor',
                    'Kingdoms of Kalamar',
                    'Lankhmar',
                    'Mahasarpa',
                    'Mystara',
                    'Hollow World',
                    'Savage Coast',
                    'Nentir Vale',
                    'Pelinore',
                    'Planescape',
                    'Ravenloft',
                    'Masque of the Red Death',
                    'Rokugan',
                    'Spelljammer',
                    'Thunder Rift',
                    'Warcraft',
                    'Wilderlands of High Fantasy'
                ]);
            },
            'Region' => function (Faker\Generator $faker) {
                return $faker->randomElement(explode(', ', 'Aeskeem, Aevhyr Anganor, Ardur, Aurna, Awk, Bahli, Balk, Belka, Benere, Borune, Bre\'gyn, Briggs, Brourd, Cahathic, Camas, Catip, Cerios, Cerran, Chathoc, Chetatyr, Clarmont, Criik, Dater Bass, Danto, Delthestal, Dhariy, Dinal, Dnher, Entnab, Eshul, Evarz, Exaple, Falkland, Fhalk, Figlomere, Forlen, Fortena, Gan, Ghonyg, Ginad, Giud, Gorge, Gourne, Gynnikt, Hashma, Haug, Havaco, Hevali, Hiwhe, Idierz, Iget, Inan, Inteka, Ivester, Jyshmon, Kallak, Khand, Khees, Khis, Krynn, La\'poch, Leore, Libernab, Loevlee, Loria, Manto, Memnon, Mohrin, Nabrynn, Najal (soft j), Nanduka, Nareik, Nexur, Noydhea, Ocea, Ofer, Omine, Opake, Parthin, Phorquard, Phyion, Piegar, Pikuko, Qa, Qar\'ul, Rankino, Rath, Rith, Ravan, Rhutyne, Rogure, Rush Valley, Sallow Valley, Santhica, Sar\'ukt, Scholl, Scretob, Shaal, Shorol, Siel, Speld, Stoeln, Sypes, T\'Narg, Toi, Tranda, Tribliko, Uth\'nuul, Vaargh, Verdival, Verios, Washougal, Wyshnal, Xing, Xyron, Yerda, Zaramon, Zreall, Zubair'));
            },
            'Environment' => function (Faker\Generator $faker) {
                return $faker->randomElement([
                    'Underground',
                    'Aquatic',
                    'Deserts',
                    'Forests',
                    'Hills',
                    'Marshes',
                    'Mountains',
                    'Plains',
                    'City',
                ]);
            },
            'Magic Level' => function (Faker\Generator $faker) {
                return $faker->randomElement([
                    'Low', 'High', 'Magitech'
                ]);
            },
            'Alignment' => function (Faker\Generator $faker) {
                return $faker->randomElement([
                    'Good', 'Evil', 'Any', 'Lawful/Evil', 'Chaotic/Evil'
                ]);
            },
            'Race / Social Class' => function (Faker\Generator $faker) {
                return $faker->randomElement([
                    'Gnomes', 'Drow', 'Human or Halfelf', 'Any'
                ]);
            },
            'Level progression' => function (Faker\Generator $faker) {
                return $faker->randomElement([
                    'Milestones', 'XP'
                ]);
            },
            'Items' => function (Faker\Generator $faker) {
                return $faker->randomElement([
                    'Arcane Door',
                    'Arcane Lock Box',
                    'Arcanum Spellbook',
                    'Archer Gloves',
                    'Armbands of Prestidigitation',
                    'Auril\'s Kiss',
                    'Axe of Changing State',
                    'Azura\'s Star',
                    'Bag of Bags',
                    'Banished One\'s Cloak',
                    'Beholder Eye',
                    'Belt of Battle',
                    'Blanket of Warmness',
                    'Book of Time',
                ]);
            },
            'NPCs' => function (Faker\Generator $faker) {
                return $faker->name;
            },
            'Creatures' => function (Faker\Generator $faker) {
                return $faker->randomElement([
                    'Aasimar',
                    'Aboleth',
                    'Aboleth',
                    'Aboleth Mage',
                    'Abomination',
                    'Abyssal Greater Basilisk',
                    'Achaierai',
                    'Acolyte (Creature)',
                    'Adamantine Golem',
                    'Adult Arrowhawk',
                    'Adult Black Dragon',
                    'Adult Blue Dragon',
                ]);
            },
        ];

        if (!array_key_exists($tag->getTitle(), $fakes)) {
            return false;
        }
        $fake = $fakes[$tag->getTitle()];

        $info->setContent($fake($faker));

        return true;
    }
}