<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Command;

use GuzzleHttp\Exception\TransferException;
use IPTools\IP;
use IPTools\Network;
use Myracloud\WebApi\Endpoint\AbstractEndpoint;
use Myracloud\WebApi\WebApi;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WhitelistToolCommand extends AbstractCommand
{
    const FORMAT_IPTABLES  = 'iptables';
    const FORMAT_IP6TABLES = 'ip6tables';
    const FORMAT_IPSET     = 'ipset';
    const FORMAT_NFTABLES  = 'nftables';
    /**
     * @var array
     */
    protected $formats = [
        self::FORMAT_IPTABLES,
        self::FORMAT_IP6TABLES,
        self::FORMAT_IPSET,
        self::FORMAT_NFTABLES,
    ];

    /** @var WebApi */
    protected $webapi;

    /**
     *
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('myracloud:tools:gen-whitelist');
        $this->setDescription('Export Firewall rules for an Origin Host, allowing access by the current Myracloud Hosts.');

        $this->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Format for export.', self::FORMAT_IPTABLES);

        $this->setHelp(<<<'TAG'
Will generate a Firewall Ruleset to whitelist all currently active Myracloud Hosts on the Origin server.

<fg=yellow>Example usage:</>
bin/console myracloud:tools:gen-whitelist -f iptables

<fg=yellow>Supported Formats:</>

iptables (only Ipv4)
ip6tables (only Ipv6)
ipset (Ipv4 and Ipv6)
nftables (Ipv4 and Ipv6)
TAG
        );

    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $options  = $this->resolveOptions($input, $output);
            $endpoint = $this->getEndpoint();
            $data     = $endpoint->getList(null);
            $output->writeln(
                [
                    '######################################################',
                    '# Format: ' . $options['format'],
                    '######################################################',
                ]
            );
            switch ($options['format']) {
                case self::FORMAT_IPTABLES:
                    $output->writeln($this->renderIpTables($data['list']));
                    break;
                case self::FORMAT_IP6TABLES:
                    $output->writeln($this->renderIp6Tables($data['list']));
                    break;
                case self::FORMAT_IPSET:
                    $output->writeln($this->renderIpset($data['list']));
                    break;
                case self::FORMAT_NFTABLES:
                    $output->writeln($this->renderNFTSet($data['list']));
                    break;
            }

        } catch (TransferException $e) {
            $this->handleTransferException($e, $output);

            return;
        } catch (\Exception $e) {
            $output->writeln('<fg=red;options=bold>Error:</>' . $e->getMessage());

            return;
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return array
     * @throws \Exception
     */
    protected function resolveOptions(InputInterface $input, OutputInterface $output)
    {
        $options = parent::resolveOptions($input, $output);
        if (!in_array($options['format'], $this->formats)) {
            throw new \Exception('--format must be one of: ' . implode(',', $this->formats));
        }

        return $options;
    }

    /**
     * @return AbstractEndpoint
     */
    protected function getEndpoint(): AbstractEndpoint
    {
        return $this->webapi->getNetworksEndpoint();
    }

    /**
     * @param array $data
     * @return array
     */
    private function renderIpTables(array $data)
    {
        $lines = ['iptables -N myrawhite4'];
        foreach ($data as $entry) {
            $net = Network::parse($entry['network']);
            if ($entry['enabled'] && $net->getIP()->getVersion() == IP::IP_V4) {
                $lines[] = 'iptables -A myrawhite4 -s ' . $entry['network'] . ' -j ACCEPT';
            }
        }
        $lines[] = 'iptables -A myrawhite4 -j RETURN';
        $lines[] = ' ';

        return $lines;
    }

    /**
     * @param array $data
     * @return array
     */
    private function renderIp6Tables(array $data)
    {
        $lines = ['ip6tables -N myrawhite6'];
        foreach ($data as $entry) {
            $net = Network::parse($entry['network']);
            if ($entry['enabled'] && $net->getIP()->getVersion() == IP::IP_V6) {
                $lines[] = 'ip6tables -A myrawhite6 -s ' . $entry['network'] . ' -j ACCEPT';
            }
        }
        $lines[] = 'ip6tables -A myrawhite6 -j RETURN';
        $lines[] = ' ';

        return $lines;
    }

    /**
     * @param array $data
     * @return array
     */
    private function renderIpSet(array $data)
    {
        $v4 = ['create -exist myrawhite4 hash:net family inet hashsize 1024 maxelem 65536 comment'];
        $v6 = ['create -exist myrawhite6 hash:net family inet6 hashsize 1024 maxelem 65536 comment'];
        foreach ($data as $entry) {
            $net = Network::parse($entry['network']);
            if ($entry['enabled']) {
                if ($net->getIP()->getVersion() == IP::IP_V4) {
                    $v4[] = 'add myrawhite4 ' . $entry['network'];
                }
                if ($net->getIP()->getVersion() == IP::IP_V6) {
                    $v6[] = 'add myrawhite6 ' . $entry['network'];
                }
            }
        }

        $lines = array_merge($v4, [''], $v6);

        return $lines;
    }

    private function renderNFTSet(array $data)
    {

        $v4   = ['nft add table ip filter'];
        $v4[] = 'nft add chain ip filter myrawhite4';
        $v6   = ['nft add table ip6 filter'];
        $v6[] = 'nft add chain ip6 filter myrawhite6';
        foreach ($data as $entry) {
            $net = Network::parse($entry['network']);
            if ($entry['enabled']) {
                if ($net->getIP()->getVersion() == IP::IP_V4) {
                    $v4[] = 'nft add rule ip filter myrawhite4 ip saddr ' . $entry['network'] . ' counter accept';
                }
                if ($net->getIP()->getVersion() == IP::IP_V6) {
                    $v6[] = 'nft add rule ip6 filter myrawhite6 ip6 saddr ' . $entry['network'] . ' counter accept';
                }
            }
        }

        $v4[] = 'nft add rule ip filter myrawhite4 counter return';
        $v6[] = 'nft add rule ip6 filter myrawhite6 counter return';

        $lines = array_merge($v4, [''], $v6);

        return $lines;
    }
}