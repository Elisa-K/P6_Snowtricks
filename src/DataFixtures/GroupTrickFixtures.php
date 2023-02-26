<?php

namespace App\DataFixtures;

use App\Entity\GroupTrick;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;


class GroupTrickFixtures extends Fixture
{
	public function load(ObjectManager $manager): void
	{
		$groups = ['butters', 'grabs', 'spins', 'rails'];

		foreach ($groups as $group) {
			$groupTrick = new GroupTrick();
			$groupTrick->setName($group);
			$this->addReference($group, $groupTrick);
			$manager->persist($groupTrick);

		}

		$manager->flush();
	}
}