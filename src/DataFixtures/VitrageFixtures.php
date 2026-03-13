<?php

namespace App\DataFixtures;

use App\Entity\Vitrage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VitrageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $vitrages = [
            ['type' => 'Simple', 'rw' => null, 'epaisseur' => '4 mm'],
            ['type' => 'Double', 'rw' => '32 dB', 'epaisseur' => '4-6-4 mm'],
            ['type' => 'Double', 'rw' => '36 dB', 'epaisseur' => '4-12-4 mm'],
            ['type' => 'Triple', 'rw' => '40 dB', 'epaisseur' => '4-6-4-6-4 mm'],
            ['type' => 'Feuilleté', 'rw' => null, 'epaisseur' => '6 mm'],
            ['type' => 'Feuilleté renforcé', 'rw' => null, 'epaisseur' => '8 mm'],
            ['type' => 'Teinté', 'rw' => null, 'epaisseur' => '6 mm'],
        ];

        foreach ($vitrages as $data) {
            $vitrage = new Vitrage();
            $vitrage->setType($data['type']);
            $vitrage->setRw($data['rw']);
            $vitrage->setEpaisseur($data['epaisseur']);
            $manager->persist($vitrage);
        }

        $manager->flush();
    }
}
