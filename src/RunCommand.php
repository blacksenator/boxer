<?php

namespace blacksenator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    use ConfigTrait;

    protected function configure()
    {
        $this->setName('run')
            ->setDescription('perpetual');

        $this->addConfig();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig($input);
        error_log('Starting FRITZ!Box access...');

        // test: setting kids filters (uncomment for testing access to /internet/kids_userlist.lua)
        // setKidsFilter($this->config);

        // test: getting MAC from device connected to designated LAN port
        // echo getMeshList($this->config);

        // test: get the call list (in, out, rejected, fail or all)
        // print_r(getCallList_LUA($this->config, 'in'));

        // test: get the call list (SOAP)
        // getCallList($this->config);

        // test: getting MAC from device connected to designated LAN port (SOAP)
        // getFileLinkList($this->config);

        // test: ftp availability
        getStorageInfo($this->config);

        // test: get TelTarif CallByCall image
        // getCallByCall();

        // getVoipInfo($this->config);

        // assambleClasses($this->config);
    }
}
