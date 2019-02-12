<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CacheClear
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
        $this->addOption('cleanupRule', 'cr', InputOption::VALUE_REQUIRED, 'Rule that describes which files should be removed from the cache.', '*');
        $this->addOption('recursive', 'r', InputOption::VALUE_NONE, 'Should the rule applied recursively.');
        $this->setDescription('CacheClear commands allows you to do a cache clear via Myra API.');
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

        $endpoint = $this->webapi->getCacheClearEndpoint();

        $return = $endpoint->clear($options['fqdn'], $options['fqdn'], $options['cleanupRule'], $options['recursive']);

        if (is_array($return) && array_key_exists('error', $return) && $return['error'] == true) {
            foreach ($return['violationList'] as $violation) {
                $output->writeln('API Error: ' . $violation['message']);
            }
        }

        if ($output->isVerbose()) {
            print_r($return);
        }
    }


}