<?php

namespace blacksenator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    use ConfigTrait;

    protected function configure()
    {
        $this->setName('run')
            ->setDescription('perpetual')
            ->addOption('1', '1', InputOption::VALUE_NONE, 'test kids filter')
            ->addOption('2', '2', InputOption::VALUE_NONE, 'test get MAC adress at LAN port')
            ->addOption('3', '3', InputOption::VALUE_NONE, 'test get call list')
            ->addOption('4', '4', InputOption::VALUE_NONE, 'test get call list (SOAP)')
            ->addOption('5', '5', InputOption::VALUE_NONE, 'test get file link list (SOAP)')
            ->addOption('6', '6', InputOption::VALUE_NONE, 'test get ftp availability');

        $this->addConfig();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig($input);
        error_log('Starting FRITZ!Box access...');

        // test: setting kids filters (uncomment for testing access to /internet/kids_userlist.lua)
        if ($input->getOption('1')) {
            setKidsFilter($this->config);
        }

        // test: getting MAC from device connected to designated LAN port
        if ($input->getOption('2')) {
            echo getMeshList($this->config);
        }

        // test: get the call list (in, out, rejected, fail or all)
        if ($input->getOption('3')) {
            print_r(getCallList_LUA($this->config, 'in'));
        }

        // test: get the call list (SOAP)
        if ($input->getOption('4')) {
            getCallList($this->config);
        }

        // test: getting file link list (SOAP)
        if ($input->getOption('5')) {
            getFileLinkList($this->config);
        }

        // test: ftp availability
        if ($input->getOption('6')) {
            getStorageInfo($this->config);
        }

        // test: get TelTarif CallByCall image
        // getCallByCall();

        // getVoipInfo($this->config);

        // assambleClasses($this->config);
    }
}
