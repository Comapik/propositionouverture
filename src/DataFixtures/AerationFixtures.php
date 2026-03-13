<?php

namespace App\DataFixtures;

use App\Entity\Aeration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AerationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $aerations = [
            'Aérateur discret (haut)',
            'Aérateur standard (haut)',
            'Aérateur hygroréglable (haut)',
            'Aérateur discret (bas)',
            'Aérateur standard (bas)',
            'Aérateur latéral',
            'Aérateur d\'angle',
        ];

        foreach ($aerations as $modele) {
            $aeration = new Aeration();
            $aeration->setModele($modele);
            $manager->persist($aeration);
        }

        $manager->flush();
    }
}
