<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Command;

use Myracloud\WebApi\Endpoint\AbstractEndpoint;
use Myracloud\WebApi\Endpoint\Maintenance;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MaintenanceCommand
 *
 * @package Myracloud\API\Command
 */
class MaintenanceCommand extends AbstractCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('myracloud:api:maintenance');
        $this->addOption('operation', 'o', InputOption::VALUE_REQUIRED, '', self::OPERATION_LIST);
        $this->addOption('contentFile', 'f', InputOption::VALUE_REQUIRED, 'HTML file that contains the maintenance page.');
        $this->addOption('start', 'a', InputOption::VALUE_REQUIRED, 'Time to start the maintenance from.', null);
        $this->addOption('end', 'b', InputOption::VALUE_REQUIRED, 'Time to end the maintenance.', null);
        $this->addOption('page', 'p', InputOption::VALUE_REQUIRED, 'Page to show when listing maintenance objects.', 1);
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'Id to Update/Delete');


        $this->setDescription('The maintenance command allows you to list, create, update, and delete maintenance pages.');
        $year = date('Y');
        $this->setHelp(sprintf(<<<EOF
The maintenance command allows you to list, create, update, and delete Maintenance pages.
To delete a Maintenance, please provide the Id visible via list.

<fg=green>Valid operations are: %s.</>

<fg=yellow>Example usage to list maintenance pages:</>
bin/console myracloud:api:maintenance -o list <fqdn>

<fg=yellow>Example usage of maintenance to enqueue a new maintenance page:</>
bin/console myracloud:api:maintenance -f file.html -a "$year-03-30 00:00:00" -b "$year-04-01 00:00:00" <fqdn>

<fg=yellow>Example usage to remove a existing maintenance:</>
bin/console myracloud:api:maintenance -o delete --id 1234" <fqdn>
EOF
                , implode(', ', [
                    self::OPERATION_LIST,
                    self::OPERATION_CREATE,
                    self::OPERATION_DELETE,
                ]))
        );

        parent::configure();
    }


    /**
     * @param array           $options
     * @param OutputInterface $output
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function OpCreate(array $options, OutputInterface $output)
    {
        if ($options['contentFile'] == null) {
            throw new \RuntimeException(sprintf('You need to define the maintenance page to display by passing a file via --contentFile'));
        } elseif (!is_readable(realpath($options['contentFile']))) {
            throw new \RuntimeException(sprintf('Could not find given file "%s".', $options['contentFile']));
        }

        if (empty($options['start'])) {
            throw new \RuntimeException('You need to define a Start time via --start');
        } else {
            $start = new \DateTime($options['start']);
        }
        if (empty($options['end'])) {
            throw new \RuntimeException('You need to define a End time via --end');
        } else {
            $end = new \DateTime($options['end']);
        }
        $endpoint = $this->getEndpoint();
        $return   = $endpoint->create($options['fqdn'], $start, $end, file_get_contents($options['contentFile']));
        $this->checkResult($return, $output);
        $this->writeTable($return['targetObject'], $output);
        if ($output->isVerbose()) {
            print_r($return);
        }
    }

    /**
     * @return Maintenance
     */
    protected function getEndpoint(): AbstractEndpoint
    {
        return $this->webapi->getMaintenanceEndpoint();
    }

    /**
     * @param                 $data
     * @param OutputInterface $output
     */
    protected function writeTable($data, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(['Id', 'Created', 'Modified', 'Fqdn', 'Start', 'End', 'Active']);

        foreach ($data as $item) {
            $table->addRow([
                array_key_exists('id', $item) ? $item['id'] : null,
                $item['created'],
                $item['modified'],
                $item['fqdn'],
                $item['start'],
                $item['end'],
                $item['active'] ?: 0,
            ]);
        }
        $table->render();

    }

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function OpUpdate(array $options, OutputInterface $output)
    {
        /**@var $endpoint Maintenance */

        $existing = $this->findById($options);

        if (empty($options['start'])) {
            $startDate = new \DateTime($existing['start']);
        } else {
            $startDate = new \DateTime($options['start']);
        }
        if (empty($options['end'])) {
            $endDate = new \DateTime($existing['end']);
        } else {
            $endDate = new \DateTime($options['end']);
        }

        if (empty($options['contentFile'])) {
            $content = $existing['content'];
        } elseif (!is_readable(realpath($options['contentFile']))) {
            throw new \RuntimeException(sprintf('Could not find given file "%s".', $options['contentFile']));
        } else {
            $content = file_get_contents($options['contentFile']);
        }

        $endpoint = $this->getEndpoint();
        $return   = $endpoint->update(
            $options['fqdn'],
            $existing['id'],
            new \DateTime($existing['modified']),
            $startDate,
            $endDate,
            $content
        );
        $this->checkResult($return, $output);
        $this->writeTable($return['targetObject'], $output);
        if ($output->isVerbose()) {
            print_r($return);
        }
    }
}