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
    /** @var OptionsResolver */
    protected $resolver;
    /** @var WebApi */
    protected $webapi;

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
        if (file_exists('../config.php')) {
            include '../config.php';
            $input->setOption('apiKey', $config['apikey']);
            $input->setOption('secret', $config['secret']);
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
}