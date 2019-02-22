<?php
declare(strict_types=1);

namespace Myracloud\WebApi\Command;


use GuzzleHttp\Exception\TransferException;
use Myracloud\WebApi\Endpoint\AbstractEndpoint;
use Myracloud\WebApi\Endpoint\SubdomainSetting;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SubdomainCommand
 *
 * @package Myracloud\WebApi\Command
 */
class SubdomainCommand extends AbstractCommand
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('myracloud:api:subdomain');
        $this->addOption('apiKey', 'k', InputOption::VALUE_REQUIRED, 'Api key to authenticate against Myra API.', null);
        $this->addOption('secret', 's', InputOption::VALUE_REQUIRED, 'Secret to authenticate against Myra API.', null);
        $this->addArgument('fqdn', InputArgument::REQUIRED, 'Subdomain that should be used.');
        $this->addArgument('key', InputArgument::OPTIONAL, 'Setting to write');
        $this->addArgument('value', InputArgument::OPTIONAL, 'Value to write');
        $this->setDescription('The Subdomain command allows you to list, and update the settings for a Subdomain.');
        $this->setHelp(
            <<<EOF
Subdomains have to be created and configured via the Dns Command.
fqdn must be a configured subdomain.
Only passing fqdn without additional options will display all known settings.

<fg=yellow>Example usage listing Settings:</>
bin/console myracloud:api:subdomain <fqdn>

<fg=yellow>Example usage to update a Settings value:</>
bin/console myracloud:api:subdomain <fqdn> <setting_name> <value>

For boolen values, please use true and false.
Passing the value null will revert the setting to it's default.

EOF
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
            $key     = null;
            $options = $this->resolveOptions($input, $output);
            if (array_key_exists('key', $options) && !empty($options['key'])) {
                $key = $options['key'];
                if (array_key_exists('value', $options) && !empty($options['value'])) {
                    $this->OpUpdate($options, $output);
                }
            }
            $this->OpList($options, $output, $key);

        } catch (TransferException $e) {
            $output->writeln('<fg=red;options=bold>Error:</> ' . $e->getMessage());
            $output->writeln('<fg=red;options=bold>Error:</> Are you using the correct key/secret?');
            $output->writeln('<fg=red;options=bold>Error:</> Is the domain attached to the account associated with this key/secret combination?');

            return;
        } catch (\Exception $e) {
            $output->writeln('<fg=red;options=bold>Error:</>' . $e->getMessage());

            return;
        }
    }

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function OpUpdate(array $options, OutputInterface $output)
    {
        /** @var SubdomainSetting $endpoint */
        $endpoint = $this->getEndpoint();

        switch (true) {
            case $options['value'] == 'true':
                $value = true;
                break;
            case $options['value'] == 'false':
                $value = false;
                break;
            case $options['value'] == 'null':
                $value = null;
                break;
            case is_numeric($options['value']):
                $value = floatval($options['value']);
                break;
            default:
                $value = $options['value'];
        }
        $data   = [
            $options['key'] => $value,
        ];
        $return = $endpoint->set($options['fqdn'], $data);
        $this->checkResult($return, $output);
    }

    /**
     * @return AbstractEndpoint
     */
    protected function getEndpoint(): AbstractEndpoint
    {
        return $this->webapi->getSubdomainSettingsEndpoint();
    }

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @param                 $keyName
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function OpList(array $options, OutputInterface $output, $keyName = null)
    {
        /** @var SubdomainSetting $endpoint */
        $endpoint = $this->getEndpoint();
        $return   = $endpoint->get($options['fqdn']);
        $this->checkResult($return, $output);
        $this->writeTable($return, $output, $keyName);
    }

    /**
     * @param                 $data
     * @param OutputInterface $output
     * @param null            $keyName
     */
    protected function writeTable($data, OutputInterface $output, $keyName = null)
    {
        $table = new Table($output);
        $table->setHeaders([
            'Setting',
            'Value',
            'Parent Value',
        ]);
        $current = [];
        foreach ($data['targetObject'] as $item) {
            $current = array_merge($current, $item);
        }

        $parent = $data['parent'];

        foreach ($current as $key => $value) {
            if ($keyName == null || $keyName == $key) {
                $value  = $this->formatCell($value);
                $value2 = null;
                if (array_key_exists($key, $parent)) {
                    $value2 = $this->formatCell($parent[$key]);
                }
                if ($value !== $value2) {
                    $value = '<fg=red;options=bold>' . $value . '</>';
                }
                $table->addRow([$key, $value, $value2]);
            }
        }
        $table->render();
    }

    private function formatCell($data)
    {
        switch (true) {
            case is_bool($data):
                if ($data === true) {
                    return 'true';
                } else {
                    return 'false';
                }
            case is_array($data):
                return implode(',', $data);
            default:
                return $data;
        }
    }

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function OpListSingle(array $options, OutputInterface $output, $keyName)
    {
        /** @var SubdomainSetting $endpoint */
        $endpoint = $this->getEndpoint();
        $return   = $endpoint->get($options['fqdn']);
        $this->checkResult($return, $output);
        $this->writeTable($return, $output);
    }

    /**
     * @param array           $options
     * @param OutputInterface $output
     * @return mixed
     */
    protected function OpCreate(array $options, OutputInterface $output)
    {
    }
}