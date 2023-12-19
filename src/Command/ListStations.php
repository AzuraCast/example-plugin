<?php

declare(strict_types=1);

namespace Plugin\ExamplePlugin\Command;

use App\Console\Command\CommandAbstract;
use App\Container\EntityManagerAwareTrait;
use App\Entity\Station;
use App\Radio\Adapters;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'example:list-stations',
    description: 'An example function to list stations in a table view.',
)]
final class ListStations extends CommandAbstract
{
    use EntityManagerAwareTrait;

    public function __construct(
        private readonly Adapters $adapters
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Example Plugin: Stations');

        $headers = [
            'ID',
            'Name',
            'Frontend',
            'Backend',
            'Remotes',
        ];

        $rows = [];

        $stations = $this->em->getRepository(Station::class)->findAll();

        /** @var Station $station */
        foreach($stations as $station) {
            $backend = $this->adapters->getBackendAdapter($station);
            $frontend = $this->adapters->getFrontendAdapter($station);

            $rows[] = [
                $station->getId(),
                $station->getName(),
                $station->getBackendType()
                    ->getName() . ' (' . ($backend->isRunning($station) ? 'Running' : 'Stopped') . ')',
                $station->getFrontendType()
                    ->getName() . ' (' . ($frontend->isRunning($station) ? 'Running' : 'Stopped') . ')',
                $station->getRemotes()->count(),
            ];
        }

        $io->table($headers, $rows);

        return 0;
    }
}
