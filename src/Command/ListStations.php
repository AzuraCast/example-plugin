<?php
namespace Plugin\ExamplePlugin\Command;

use Azura\Console\Command\CommandAbstract;

class ListStations extends CommandAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('example:list-stations')
            ->setDescription('An example function to list stations in a table view.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $io = new \Symfony\Component\Console\Style\SymfonyStyle($input, $output);
        $io->title('Example Plugin: Stations');

        $headers = [
            'ID',
            'Name',
            'Frontend',
            'Backend',
            'Remotes',
        ];

        $rows = [];

        /** @var \App\Radio\Adapters $adapters */
        $adapters = $this->get(\App\Radio\Adapters::class);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get(\Doctrine\ORM\EntityManager::class);

        $stations = $em
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


        $table = (new \Symfony\Component\Console\Helper\Table($output))
            ->setHeaders($headers)
            ->setRows($rows);

        $table->render();

        return 0;
    }
}
