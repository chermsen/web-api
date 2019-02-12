<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MaintenanceCommand
 *
 * @package Myracloud\API\Command
 */
class MaintenanceCommand extends AbstractCommand
{
    const OPERATION_CREATE = 'create';
    const OPERATION_DELETE = 'delete';
    const OPERATION_LIST   = 'list';
    const OPERATION_UPDATE = 'update';

    /**
     *
     */
    protected function configure()
    {
        $this->setName('myracloud:api:maintenance');
        $this->addOption('operation', 'o', InputOption::VALUE_REQUIRED, '', self::OPERATION_CREATE);
        $this->addOption('contentFile', 'f', InputOption::VALUE_REQUIRED, 'HTML file that contains the maintenance page.');
        $this->addOption('start', 'a', InputOption::VALUE_REQUIRED, 'Time to start the maintenance from.', date('Y-m-d H:i:s'));
        $this->addOption('end', 'b', InputOption::VALUE_REQUIRED, 'Time to end the maintenance.', date('Y-m-d H:i:s'));
        $this->addOption('page', 'p', InputOption::VALUE_REQUIRED, 'Page to show when listing maintenance objects.', 1);
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'Id to Update/Delete');


        $this->setDescription('The maintenance command allows you to list, create, update, and delete maintenance pages.');
        $year = date('Y');
        $this->setHelp(sprintf(<<<EOF
The maintenance command allows you to list, create, update, and delete maintenace pages.
The options "start" and "end" are used to identify the maintenance that should be updated or deleted.
In case of an update (that changes the start and / or end date) you need also to set nStart and / or nEnd to the new dates. 
<fg=green>Valid operations are: %s.</>
<fg=yellow>Example usage to list maintenance pages:</>
bin/console myracloud:api:maintenance -o list <fqdn>
<fg=yellow>Example usage of maintenance to enqueue a new maintenance page:</>
bin/console myracloud:api:maintenance -f file.html -a "$year-03-30 00:00:00" -b "$year-04-01 00:00:00"  <fqdn>
<fg=yellow>Example usage to remove a existing maintenance:</>
bin/console myracloud:api:maintenance -o delete --id 1234"  <fqdn>
EOF
                , implode(', ', [
                    self::OPERATION_LIST,
                    self::OPERATION_CREATE,
                    self::OPERATION_UPDATE,
                    self::OPERATION_DELETE,
                ]))
        );

        parent::configure();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $options = $this->resolveOptions($input, $output);

        $endpoint = $this->webapi->getMaintenanceEndpoint();

        $start = new \DateTime($options['start']);
        $end   = new \DateTime($options['end']);

        switch ($options['operation']) {
            default:
            case self::OPERATION_CREATE:
                if (!is_readable($options['contentFile'])) {
                    throw new \RuntimeException(sprintf('Could not find given file "%s".', $options['contentFile']));
                }
                $return = $endpoint->create($options['fqdn'], $start, $end, file_get_contents($options['contentFile']));
                $this->checkError($return, $output);
                $output->writeln('<fg=green;options=bold>Success</>');
                break;
            case self::OPERATION_LIST:
                $return = $endpoint->getList($options['fqdn'], $options['page']);
                $this->checkError($return, $output);
                $table = new Table($output);
                $table->setHeaders(['Id', 'Created', 'Modified', 'Fqdn', 'Start', 'End', 'Active']);

                foreach ($return['list'] as $item) {
                    $table->addRow([
                        $item['id'],
                        $item['created'],
                        $item['modified'],
                        $item['fqdn'],
                        $item['start'],
                        $item['end'],
                        $item['active'] ?: 0,
                    ]);
                }
                $table->render();
                $output->writeln('<fg=green;options=bold>Success</>');
                break;
            case self::OPERATION_DELETE:
                $return = $endpoint->delete($options['fqdn'], $options['id'], new \DateTime());
                $output->writeln('<fg=green;options=bold>Success</>');
                break;
        }


        if ($output->isVerbose()) {
            print_r($return);
        }
    }

    /**
     * @param                 $data
     * @param OutputInterface $output
     */
    protected function checkError($data, OutputInterface $output)
    {
        if (is_array($data) && array_key_exists('error', $data) && $data['error'] == true) {
            foreach ($data['violationList'] as $violation) {
                $output->writeln('API Error: ' . $violation['message']);
            }
        }
    }
}