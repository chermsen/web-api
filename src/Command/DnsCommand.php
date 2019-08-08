<?php
declare(strict_types=1);


namespace Myracloud\WebApi\Command;

use Myracloud\WebApi\Endpoint\AbstractEndpoint;
use Myracloud\WebApi\Endpoint\DnsRecord;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DnsCommand
 *
 * @package Myracloud\API\Command
 */
class DnsCommand extends AbstractCrudCommand
{
    static $dnsTypes = [
        AbstractEndpoint::DNS_TYPE_A,
        AbstractEndpoint::DNS_TYPE_AAAA,
        AbstractEndpoint::DNS_TYPE_MX,
        AbstractEndpoint::DNS_TYPE_CNAME,
        AbstractEndpoint::DNS_TYPE_TXT,
        AbstractEndpoint::DNS_TYPE_NS,
        AbstractEndpoint::DNS_TYPE_SRV,
        AbstractEndpoint::DNS_TYPE_CAA,
    ];

    /**
     *
     */
    protected function configure()
    {
        $this->setName('myracloud:api:dns');
        $this->addOption('ttl', null, InputOption::VALUE_REQUIRED, 'time to live', null);
        $this->addOption('sub', null, InputOption::VALUE_REQUIRED, 'subdomain', null);
        $this->addOption('ip', null, InputOption::VALUE_REQUIRED, 'IpAddress', null);
        $this->addOption('sslcert', null, InputOption::VALUE_REQUIRED, 'Path to a SslCert', null);
        $this->addOption('type', null, InputOption::VALUE_REQUIRED, 'Type of match (' . implode(',', self::$dnsTypes) . ')', null);

        $this->setDescription('Dns commands allow you to edit DNS Records.');
        $this->setHelp(sprintf(<<<'TAG'
Only passing fqdn without additional options will list all Dns entries.

<fg=yellow>Example Listing all Dns entries:</>
bin/console myracloud:api:dns <fqdn>

<fg=yellow>Example creating a new dns entry:</>
bin/console myracloud:api:dns <fqdn> -o create --sub <name> --ttl <ttl> --type <type> --ip <ipaddress/value>

Please note, additional rules for the format of the ipaddress/value apply depending on the entry type.

<fg=yellow>Example updating a existing Dns entry:</>
bin/console myracloud:api:dns <fqdn> -o update --id <id-from-list> <any-param>

Update an existing record, use the 'update' operation with an existing id. You can add any of the create params (ttl,type,ip,sub) so overwrite the existing value.

<fg=yellow>Example Deleting a existing Dns entry:</>
bin/console myracloud:api:dns <fqdn> -o delete --id <id-from-list>

valid types are %s

TAG
            , implode(',', self::$dnsTypes)));
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

        if (empty($options['ttl'])) {
            throw new \RuntimeException('You need to define a time to live via --ttl');
        }
        if (empty($options['type'])) {
            throw new \RuntimeException('You need to define Matching type via --type');
        } elseif (!in_array($options['type'], self::$dnsTypes)) {
            throw new \RuntimeException('--type has to be one of ' . implode(',', self::$dnsTypes));
        }
        if (empty($options['sub'])) {
            throw new \RuntimeException('You need to define a subdomain via --sub');
        }
        if (empty($options['ip'])) {
            throw new \RuntimeException('You need to define a IpAddress via --ip');
        }
        if ($options['sslcert'] !== null && !is_readable(realpath($options['sslcert']))) {
            throw new \RuntimeException(sprintf('Could not find given file "%s".', $options['sslcert']));
        }
        /** @var DnsRecord $endpoint */
        $endpoint = $this->getEndpoint();

        $return = $endpoint->create(
            $options['fqdn'],
            $options['sub'],
            $options['ip'],
            $options['ttl'],
            $options['type'],
            true,
            $options['sslcert'] ? file_get_contents(realpath($options['sslcert'])) : null
        );
        $this->handleTableReturn($return, $output);
    }

    /**
     * @return AbstractEndpoint
     */
    protected function getEndpoint(): AbstractEndpoint
    {
        return $this->webapi->getDnsRecordEndpoint();
    }

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function OpUpdate(array $options, OutputInterface $output)
    {
        /** @var DnsRecord $endpoint */
        $endpoint = $this->getEndpoint();
        $existing = $this->findById($options);

        if (empty($options['ttl'])) {
            $options['ttl'] = $existing['ttl'];
        }
        if (empty($options['type'])) {
            $options['type'] = $existing['recordType'];
        }
        if (!in_array($options['type'], self::$dnsTypes)) {
            throw new \RuntimeException('--type has to be one of ' . implode(',', self::$dnsTypes));
        }
        if (empty($options['sub'])) {
            $options['sub'] = $existing['name'];
        }
        if (empty($options['ip'])) {
            $options['ip'] = $existing['value'];
        }
        if ($options['sslcert'] !== null && !is_readable(realpath($options['sslcert']))) {
            throw new \RuntimeException(sprintf('Could not find given file "%s".', $options['sslcert']));
        }


        $return = $endpoint->update(
            $options['fqdn'],
            $options['id'],
            new \DateTime($existing['modified']),
            $options['sub'],
            $options['ip'],
            $options['ttl'],
            $options['type'],
            $existing['active'],
            $options['sslcert'] ? file_get_contents(realpath($options['sslcert'])) : null
        );
        $this->handleTableReturn($return, $output);
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
            'Value',
            'Priority',
            'ttl',
            'Type',
            'Enabled',
            'Paused',
            'Alternative N.',
            'caaFlags',
        ]);

        foreach ($data as $item) {
            $table->addRow([
                array_key_exists('id', $item) ? $item['id'] : null,
                $item['created'],
                $item['modified'],
                $item['name'],
                $item['value'],
                $item['priority'],
                $item['ttl'],
                $item['recordType'],
                $item['enabled'] ?: 0,
                @$item['paused'] ?: 0,
                @$item['alternativeCname'],
                $item['caaFlags'],
            ]);
        }
        $table->render();
    }
}