<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Command;

use GuzzleHttp\Exception\TransferException;
use Myracloud\WebApi\Endpoint\AbstractEndpoint;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CacheClearCommand
 *
 * @package Myracloud\API\Command
 */
class CacheClearCommand extends AbstractCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('myracloud:api:cacheClear');
        $this->addOption('cleanupRule', 'c', InputOption::VALUE_REQUIRED, 'Rule that describes which files should be removed from the cache.', '*');
        $this->addOption('recursive', 'r', InputOption::VALUE_NONE, 'Should the rule applied recursively.');
        $this->addOption('apiKey', 'k', InputOption::VALUE_REQUIRED, 'Api key to authenticate against Myra API.', null);
        $this->addOption('secret', 's', InputOption::VALUE_REQUIRED, 'Secret to authenticate against Myra API.', null);
        $this->addArgument('fqdn', InputArgument::REQUIRED, 'Domain that should be used.');
        $this->setDescription('CacheClear commands allows you to do a cache clear via Myra API.');
        $this->getName();
        $this->setHelp(<<<'TAG'
<fg=yellow>Example usage:</>
bin/console myracloud:api:cacheClear <fqdn>

<fg=yellow>Example Clearing all jpg files recursively:</>
bin/console myracloud:api:cacheClear <fqdn> -r -c *.jpg 
TAG
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $options = $this->resolveOptions($input, $output);

            $endpoint = $this->getEndpoint();
            $return   = $endpoint->clear($options['fqdn'], $options['fqdn'], $options['cleanupRule'], $options['recursive']);
        } catch (TransferException $e) {
            $output->writeln('<fg=red;options=bold>Error:</> ' . $e->getMessage());
            $output->writeln('<fg=red;options=bold>Error:</> Are you using the correct key/secret?');
            $output->writeln('<fg=red;options=bold>Error:</> Is the domain attached to the account associated with this key/secret combination?');

            return;
        } catch (\Exception $e) {
            $output->writeln('<fg=red;options=bold>Error:</>' . $e->getMessage());

            return;
        }
        $this->checkResult($return, $output);
    }

    /**
     * @return AbstractEndpoint
     */
    protected function getEndpoint(): AbstractEndpoint
    {
        return $this->webapi->getCacheClearEndpoint();
    }

    /**
     * @param                 $data
     * @param OutputInterface $output
     */
    protected function writeTable($data, OutputInterface $output)
    {
    }

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @return mixed
     */
    protected function OpCreate(array $options, OutputInterface $output)
    {
    }

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @return mixed
     */
    protected function OpUpdate(array $options, OutputInterface $output)
    {
    }
}