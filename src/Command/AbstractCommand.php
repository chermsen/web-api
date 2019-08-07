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
    /**
     *
     */
    const OPERATION_CREATE = 'create';
    /**
     *
     */
    const OPERATION_DELETE = 'delete';
    /**
     *
     */
    const OPERATION_LIST = 'list';
    /**
     *
     */
    const OPERATION_UPDATE = 'update';

    /**
     * @var array
     */
    static $operations = [
        self::OPERATION_UPDATE,
        self::OPERATION_CREATE,
        self::OPERATION_DELETE,
        self::OPERATION_LIST,
    ];

    /** @var WebApi */
    protected $webapi;

    /**
     *
     */
    protected function configure()
    {
        $this->addOption('operation', 'o', InputOption::VALUE_REQUIRED, '', self::OPERATION_LIST);
        $this->addOption('page', null, InputOption::VALUE_REQUIRED, 'Page to show when listing objects.', 1);
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'Id to Update/Delete');
        $this->addOption('apiKey', 'k', InputOption::VALUE_REQUIRED, 'Api key to authenticate against Myra API.', null);
        $this->addOption('secret', 's', InputOption::VALUE_REQUIRED, 'Secret to authenticate against Myra API.', null);
        $this->addOption('endpoint', 'ep', InputOption::VALUE_OPTIONAL, 'API endpoint.', 'app.myracloud.com');
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $options = $this->resolveOptions($input, $output);

            if (!in_array($options['operation'], self::$operations)) {
                $output->writeln('<fg=red;options=bold>Error:</> --operation must be one of ' . implode(',', self::$operations));

                return;
            }
            switch ($options['operation']) {
                case self::OPERATION_LIST:
                    $this->OpList($options, $output);
                    break;
                case self::OPERATION_CREATE:
                    $this->OpCreate($options, $output);
                    break;
                case self::OPERATION_UPDATE:
                    $this->OpUpdate($options, $output);
                    break;
                case self::OPERATION_DELETE:
                    $this->OpDelete($options, $output);
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
     * @param array           $options
     * @param OutputInterface $output
     */
    protected function OpList(array $options, OutputInterface $output)
    {
        $endpoint = $this->getEndpoint();
        $return   = $endpoint->getList($options['fqdn'], $options['page']);
        $this->checkResult($return, $output);
        $this->writeTable($return['list'], $output);
        if ($output->isVerbose()) {
            print_r($return);
        }
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
     * @param                 $data
     * @param OutputInterface $output
     */
    abstract protected function writeTable($data, OutputInterface $output);

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @return mixed
     */
    abstract protected function OpCreate(array $options, OutputInterface $output);

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @return mixed
     */
    abstract protected function OpUpdate(array $options, OutputInterface $output);

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function OpDelete(array $options, OutputInterface $output)
    {
        if ($options['id'] == null) {
            throw new \RuntimeException('You need to define the id of the object to delete via --id');
        }

        $endpoint = $this->getEndpoint();
        $existing = $this->findById($options);

        $return = $endpoint->delete($options['fqdn'], $options['id'], new \DateTime($existing['modified']));
        $this->handleDeleteReturn($return, $output);
    }

    /**
     * @param array $options
     * @return array
     */
    protected function findById(array $options)
    {
        if ($options['id'] == null) {
            throw new \RuntimeException('You need to define the id of the object via --id');
        }
        $endpoint = $this->getEndpoint();
        $return   = $endpoint->getList($options['fqdn'], $options['page']);
        foreach ($return['list'] as $item) {
            if ($item['id'] == $options['id']) {
                return $item;
            }
        }
        throw new \RuntimeException('Could not find an object with the passed id.');
    }

    /**
     * @param                 $return
     * @param OutputInterface $output
     */
    protected function handleDeleteReturn($return, OutputInterface $output): void
    {
        $this->checkResult($return, $output);
        $this->writeTable($return['targetObject'], $output);

        if (count($return['targetObject']) == 0) {
            $output->writeln('<fg=yellow;options=bold>Notice:</> No objects where deleted. Did you pass a valid Id?');
        }

        if ($output->isVerbose()) {
            print_r($return);
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

    /**
     * @param                 $return
     * @param OutputInterface $output
     */
    protected function handleTableReturn($return, OutputInterface $output): void
    {
        $this->checkResult($return, $output);
        $this->writeTable($return['targetObject'], $output);
        if ($output->isVerbose()) {
            print_r($return);
        }
    }
}