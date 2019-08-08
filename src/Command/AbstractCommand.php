<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Command;

use GuzzleHttp\Exception\TransferException;
use Myracloud\WebApi\Endpoint\AbstractEndpoint;
use Myracloud\WebApi\WebApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCommand
 *
 * @package Myracloud\WebApi\Command
 */
abstract class AbstractCommand extends Command
{
    /** @var WebApi */
    protected $webapi;

    /**
     *
     */
    protected function configure()
    {
        $this->addOption('apiKey', 'k', InputOption::VALUE_REQUIRED, 'Api key to authenticate against Myra API.', null);
        $this->addOption('secret', 's', InputOption::VALUE_REQUIRED, 'Secret to authenticate against Myra API.', null);
        $this->addOption('endpoint', 'ep', InputOption::VALUE_OPTIONAL, 'API endpoint.', 'app.myracloud.com');
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
            if (empty($input->getOption('apiKey')) && array_key_exists('apikey', $config)) {
                $input->setOption('apiKey', $config['apikey']);
            }
            if (empty($input->getOption('secret')) && array_key_exists('secret', $config)) {
                $input->setOption('secret', $config['secret']);
            }
            if (empty($input->getOption('endpoint')) && array_key_exists('endpoint', $config)) {
                $input->setOption('endpoint', $config['endpoint']);
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
        $this->webapi = new WebApi($options['apiKey'], $options['secret'], $options['endpoint']);

        return $options;
    }


    /**
     * @return AbstractEndpoint
     */
    abstract protected function getEndpoint(): AbstractEndpoint;

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
                    $output->writeln('<fg=red;options=bold>API Error:</> ' . (array_key_exists('propertyPath', $violation) ? ($violation['propertyPath'] . ' ') : '') . $violation['message']);
                }
            } else {
                $output->writeln('<fg=green;options=bold>Request Successful</> ');
            }
        }
        if ($output->isVerbose()) {
            print_r($data);
        }
    }


    /**
     * @param TransferException $e
     * @param OutputInterface   $output
     */
    protected function handleTransferException(TransferException $e, OutputInterface $output)
    {
        $output->writeln('<fg=red;options=bold>Error:</> ' . $e->getMessage());
        $output->writeln('<fg=red;options=bold>Error:</> Are you using the correct key/secret?');
        $output->writeln('<fg=red;options=bold>Error:</> Is the domain attached to the account associated with this key/secret combination?');
    }


}