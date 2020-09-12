<?php

declare(strict_types=1);

namespace Plugin\ExamplePlugin\Command;

use App\Console\Command\CommandAbstract;
use App\Radio\Adapters;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListStations extends CommandAbstract
{
    public function __invoke(
        SymfonyStyle $io,
        Adapters $adapters,
        EntityManagerInterface $entityManager
    ) {
        $io->title('Example Plugin: Stations');

        $headers = [
            'ID',
            'Name',
            'Frontend',
            'Backend',
            'Remotes',
        ];

        $rows = [];

        $stations = $entityManager
            ->getRepository(\App\Entity\Station::class)
            ->findAll();

        foreach($stations as $station) {
            /** @var \App\Entity\Station $station */

            $backend = $adapters->getBackendAdapter($station);
            $frontend = $adapters->getFrontendAdapter($station);

            $rows[] = [
                $station->getId(),
                $station->getName(),
                ucfirst($station->getBackendType()).' ('.($backend->isRunning($station) ? 'Running' : 'Stopped').')',
                ucfirst($station->getFrontendType()).' ('.($frontend->isRunning($station) ? 'Running' : 'Stopped').')',
                $station->getRemotes()->count(),
            ];
        }

        $io->table($headers, $rows);

        return 0;
    }
}
