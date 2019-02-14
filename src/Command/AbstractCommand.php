<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Command;

use Myracloud\WebApi\WebApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractCommand
 *
 * @package Myracloud\WebApi\Command
 */
class AbstractCommand extends Command
{
    /** @var WebApi */
    protected $webapi;

    /**
     *
     */
    protected function configure()
    {
        parent::configure();
        $this->addOption('apiKey', 'k', InputOption::VALUE_REQUIRED, 'Api key to authenticate against Myra API.', null);
        $this->addOption('secret', 's', InputOption::VALUE_REQUIRED, 'Secret to authenticate against Myra API.', null);
        $this->addArgument('fqdn', InputArgument::REQUIRED, 'Domain that should be used.');

    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $confPath = ROOTDIR . DIRECTORY_SEPARATOR . 'config.php';
        $config   = [];
        if (file_exists($confPath)) {
            include $confPath;
            if (array_key_exists('apikey', $config)) {
                $input->setOption('apiKey', $config['apikey']);
            }
            if (array_key_exists('apikey', $config)) {
                $input->setOption('secret', $config['secret']);
            }
        }
    }

    /**
     * Resolve given options
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return array
     * @throws \Exception
     */
    protected function resolveOptions(InputInterface $input, OutputInterface $output)
    {
        $options = array_merge($input->getArguments(), $input->getOptions());
        if (empty($options['apiKey']) || empty($options['secret'])) {
            throw new \Exception('apiKey and secret have to be provided either by parameter or config file.');
        }

        $this->webapi = new WebApi($options['apiKey'], $options['secret'], 'beta.myracloud.com');

        return $options;
    }

    /**
     * @param                 $data
     * @param OutputInterface $output
     */
    protected function checkResult($data, OutputInterface $output)
    {
        if (is_array($data) && array_key_exists('error', $data)) {
            if ($data['error'] == true) {
                if (array_key_exists('exception', $data)) {
                    $output->writeln('<fg=red;options=bold>API Exception:</> ' . $data['exception']['type'] . ' ' . $data['exception']['message']);
                }
                foreach ($data['violationList'] as $violation) {
                    $output->writeln('<fg=red;options=bold>API Error:</> ' . $violation['message']);
                }
            } else {
                $output->writeln('<fg=green;options=bold>Success</> ');
            }
        }
        if ($output->isVerbose()) {
            print_r($data);
        }
    }
}