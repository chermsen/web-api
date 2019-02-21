<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Command;


use Myracloud\WebApi\Endpoint\AbstractEndpoint;
use Myracloud\WebApi\Endpoint\IpFilter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class IpfilterCommand
 *
 * @package Myracloud\WebApi\Command
 */
class IpfilterCommand extends AbstractCommand
{
    static $filterType = [
        AbstractEndpoint::IPFILTER_TYPE_WHITELIST,
        AbstractEndpoint::IPFILTER_TYPE_BLACKLIST,
        'wl',
        'bl',
    ];

    /**
     *
     */
    protected function configure()
    {
        $this->setName('myracloud:api:ipfilter');
        $this->addOption('value', null, InputOption::VALUE_REQUIRED, 'Filter pattern', null);
        $this->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Matching type', AbstractEndpoint::IPFILTER_TYPE_BLACKLIST);

        $this->setDescription('Domain commands allow you to edit Ip based filters.');
        parent::configure();
    }

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function OpCreate(array $options, OutputInterface $output)
    {
        if (empty($options['value'])) {
            throw new \RuntimeException('You need to define a filter pattern via --value');
        }
        if (empty($options['type'])) {
            throw new \RuntimeException('You need to define Matching type via --type');
        } elseif (!in_array($options['type'], self::$filterType)) {
            throw new \RuntimeException('--type has to be one of ' . implode(',', self::$filterType));
        }

        if ($options['type'] == 'wl') {
            $options['type'] = AbstractEndpoint::IPFILTER_TYPE_WHITELIST;
        }
        if ($options['type'] == 'bl') {
            $options['type'] = AbstractEndpoint::IPFILTER_TYPE_BLACKLIST;
        }


        /** @var IpFilter $endpoint */
        $endpoint = $this->getEndpoint();

        $return = $endpoint->create(
            $options['fqdn'],
            $options['type'],
            $options['value']
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
        return $this->webapi->getIpFilterEndpoint();
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
            'Value',
            'Type',
            'Enabled',
        ]);

        foreach ($data as $item) {
            $table->addRow([
                array_key_exists('id', $item) ? $item['id'] : null,
                $item['created'],
                $item['modified'],
                $item['value'],
                $item['type'],
                $item['enabled'] ?: 0,
            ]);
        }
        $table->render();
    }

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @return mixed
     * @throws \Exception
     */
    protected function OpUpdate(array $options, OutputInterface $output)
    {
        /** @var IpFilter $endpoint */
        $endpoint = $this->getEndpoint();
        $existing = $this->findById($options);

        if (empty($options['value'])) {
            $options['value'] = $existing['value'];
        }
        if (empty($options['type'])) {
            throw new \RuntimeException('You need to define Matching type via --type');
        }
        if ($options['type'] == 'wl') {
            $options['type'] = $existing['type'];
        }
        if ($options['type'] == 'bl') {
            $options['type'] = AbstractEndpoint::IPFILTER_TYPE_BLACKLIST;
        }
        if (!in_array($options['type'], self::$filterType)) {
            throw new \RuntimeException('--type has to be one of ' . implode(',', self::$filterType));
        }

        $return = $endpoint->update(
            $options['fqdn'],
            $options['id'],
            new \DateTime($existing['modified']),
            $options['type'],
            $options['value']
        );
        $this->checkResult($return, $output);
        $this->writeTable($return['targetObject'], $output);
        if ($output->isVerbose()) {
            print_r($return);
        }
    }
}