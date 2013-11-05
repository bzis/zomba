<?php

require_once __DIR__ . '/bootstrap.php.cache';

require_once __DIR__ . '/AppKernel.php';

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;

$kernel = new AppKernel('test', true);
$kernel->boot();

$application = new Application($kernel);
/** @var Doctrine\DBAL\Connection $connection */
$connection = $application->getKernel()->getContainer()->get('doctrine')->getConnection();

// проверяем, есть ли база
$result = $connection->fetchColumn("SHOW DATABASES LIKE '".$connection->getDatabase()."';");

if ($result !== false) {
    $command = new DropDatabaseDoctrineCommand();
    $application->add($command);
    $input = new ArrayInput(array(
                                 'command' => 'doctrine:database:drop',
                                 '--force' => true,
                            ));
    $command->run($input, new ConsoleOutput());
    if ($connection->isConnected()) {
        $connection->close();
    }
}

$command = new CreateDatabaseDoctrineCommand();
$application->add($command);
$input = new ArrayInput(array(
                             'command' => 'doctrine:database:create',
                        ));
$command->run($input, new ConsoleOutput());

$command = new CreateSchemaDoctrineCommand();
$application->add($command);
$input = new ArrayInput(array(
                             'command' => 'doctrine:schema:create',
                        ));
$command->run($input, new ConsoleOutput());

$command = new LoadDataFixturesDoctrineCommand();
$application->add($command);
$input = new ArrayInput(array(
                             'command' => 'doctrine:fixtures:load',
                        ));
$input->setInteractive(false);
$command->run($input, new ConsoleOutput());