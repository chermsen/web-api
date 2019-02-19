<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Command;


use Myracloud\WebApi\Endpoint\AbstractEndpoint;
use Myracloud\WebApi\Endpoint\Domain;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DomainCommand
 *
 * @package Myracloud\WebApi\Command
 */
class DomainCommand extends AbstractCommand
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('myracloud:api:domain');
        $this->addOption('apiKey', 'k', InputOption::VALUE_REQUIRED, 'Api key to authenticate against Myra API.', null);
        $this->addOption('secret', 's', InputOption::VALUE_REQUIRED, 'Secret to authenticate against Myra API.', null);
        $this->addOption('operation', 'o', InputOption::VALUE_REQUIRED, '', self::OPERATION_LIST);
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'Id to Update/Delete');
        $this->addOption('page', 'p', InputOption::VALUE_REQUIRED, 'Page to show when listing objects.', 1);

        $this->addOption('autoupdate', null, InputOption::VALUE_REQUIRED, 'Auto update flag for the domain', null);
        $this->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name of the domain', null);

        $this->setDescription('Domain commands allow you to edit Domain entries.');
    }

    /**
     * @param array           $options
     * @param OutputInterface $output
     */
    protected function OpList(array $options, OutputInterface $output)
    {
        $options['fqdn'] = null;

        parent::OpList($options, $output);
    }

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function OpCreate(array $options, OutputInterface $output)
    {
        /** @var Domain $endpoint */
        $endpoint = $this->getEndpoint();

        if ($options['name'] == null) {
            throw new \RuntimeException('You need to define a domain name via --name');
        }
        if ($options['autoupdate'] == null) {
            $options['autoupdate'] = true;
        }

        $return = $endpoint->create(
            $options['name'],
            boolval($options['autoupdate'])

        );
        $this->checkResult($return, $output);
        $this->writeTable($return['targetObject'], $output);
        if ($output->isVerbose()) {
            print_r($return);
        }
    }

    /**
     * @return AbstractEndpoint
     */
    protected function getEndpoint(): AbstractEndpoint
    {
        return $this->webapi->getDomainEndpoint();
    }

    /**
     * @param                 $data
     * @param OutputInterface $output
     */
    protected function writeTable($data, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders([
            'Id',
            'Created',
            'Modified',
            'Name',
            'Autoupdate',
            'Maintenance',
            'Paused',
            'Owned',
            'Reversed',
            'Env.',
        ]);

        foreach ($data as $item) {
            $table->addRow([
                array_key_exists('id', $item) ? $item['id'] : null,
                $item['created'],
                $item['modified'],
                $item['name'],
                @$item['autoUpdate'] ?: 0,
                @$item['maintenance'] ?: 0,
                @$item['paused'] ?: 0,
                @$item['owned'] ?: 0,
                @$item['reversed'] ?: 0,
                @$item['environment'],
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
        $options['fqdn'] = null;
        /** @var Domain $endpoint */
        $endpoint = $this->getEndpoint();
        $existing = $this->findById($options);

        if ($options['autoupdate'] == null) {
            $options['autoupdate'] = $existing['autoUpdate'];
        }
        $return = $endpoint->update(
            $options['id'],
            new \DateTime($existing['modified']),
            boolval($options['autoupdate'])
        );

        $this->checkResult($return, $output);
        $this->writeTable($return['targetObject'], $output);
        if ($output->isVerbose()) {
            print_r($return);
        }
    }

    protected function OpDelete(array $options, OutputInterface $output)
    {
        $options['fqdn'] = null;
        if ($options['id'] == null) {
            throw new \RuntimeException('You need to define the id of the object to delete via --id');
        }
        $existing = $this->findById($options);

        $endpoint = $this->getEndpoint();
        $return   = $endpoint->delete($existing['name'], $options['id'], new \DateTime($existing['modified']));
        $this->checkResult($return, $output);
        $this->writeTable($return['targetObject'], $output);

        if (count($return['targetObject']) == 0) {
            $output->writeln('<fg=yellow;options=bold>Notice:</> No objects where deleted. Did you pass a valid Id?');
        }

        if ($output->isVerbose()) {
            print_r($return);
        }
    }
}