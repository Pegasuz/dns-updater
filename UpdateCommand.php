<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateCommand
 */
class UpdateCommand extends Command
{
    /**
     * @var null
     */
    protected $connection = null;
    /**
     * @var null
     */
    protected $config = null;

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('updater')
            ->setDescription('Update dns record')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Which record do you want to update/add?'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasArgument('name') && !empty($input->getArgument('name'))) {
            $names = [$input->getArgument('name')];
        } else {
            $names = $this->getConfig('dns_records');
        }

        $externalIp = $this->getExternalIp();
        $output->writeln("<info>External IP queried: " . $externalIp . '</info>');

        foreach ($names as $name) {
            if (!empty($name)) {
                $response = $this->removeDnsRecord($name);
                if ($response) {
                    $output->writeln('<info>Removed dns record: ' . $name . '</info>');

                    $this->addDnsRecord($name, $externalIp);
                    if ($response) {
                        $output->writeln('<info>Added dns record: ' . $name . ' with ip ' . $externalIp . '</info>');
                    } else {
                        $output->writeln('<error>Error while removing dns record: ' . $name . '</error>');
                    }
                } else {
                    $output->writeln('<error>Error while removing dns record: ' . $name . '</error>');
                }
            }
        }
    }

    /**
     * @param null $value
     * @return array|null
     */
    protected function getConfig($value = null)
    {
        if (is_null($this->config)) {
            $this->config = \Symfony\Component\Yaml\Yaml::parse(__DIR__ . '/config.yml');
        }
        if ($value) {
            return $this->config[$value];
        }
        return $this->config;
    }

    /**
     * @return \DirectAdmin\DirectAdmin|null
     */
    protected function getConnection()
    {
        if (is_null($this->connection)) {
            $this->connection = new \DirectAdmin\DirectAdmin();
            $this->connection->connect($this->getConfig('da_hostname'), $this->getConfig('da_port'));
            $this->connection->set_login($this->getConfig('da_username'), $this->getConfig('da_password'));
            $this->connection->set_method('get');
        }
        return $this->connection;
    }

    /**
     * @return string
     */
    protected function getExternalIp()
    {
        return file_get_contents($this->getConfig('external_ip_check_url'));
    }

    /**
     * @param $name
     * @return bool
     */
    protected function removeDnsRecord($name)
    {
        $this->getConnection()->query(sprintf('/CMD_API_DNS_CONTROL?domain=%s&action=select&arecs0=',
                $this->getConfig('domain')) . urlencode('name=' . $name));
        $response = $this->getConnection()->fetch_body();
        parse_str($response, $output);
        return !boolval($output['error']);
    }

    /**
     * @param $name
     * @param $ip
     * @return bool
     */
    protected function addDnsRecord($name, $ip)
    {
        $this->getConnection()->query(sprintf('/CMD_API_DNS_CONTROL?domain=%s&action=add&type=A&name=',
                $this->getConfig('domain')) . $name . '&value=' . $ip);
        $response = $this->getConnection()->fetch_body();
        parse_str($response, $output);
        return !boolval($output['error']);
    }
}
