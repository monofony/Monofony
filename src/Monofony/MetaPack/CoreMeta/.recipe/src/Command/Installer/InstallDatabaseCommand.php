<?php

declare(strict_types=1);

namespace App\Command\Installer;

use App\Command\Helper\CommandsRunner;
use App\Installer\Provider\DatabaseSetupCommandsProviderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:install:database',
    description: 'Install AppName database.',
)]
class InstallDatabaseCommand extends Command
{
    public function __construct(
        private DatabaseSetupCommandsProviderInterface $databaseSetupCommandsProvider,
        private CommandsRunner $commandsRunner,
        private string $environment
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command creates AppName database.
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln(sprintf(
            'Creating AppName database for environment <info>%s</info>.',
            $this->environment
        ));
        $commands = $this
            ->databaseSetupCommandsProvider
            ->getCommands($input, $output, $this->getHelper('question'))
        ;
        $this->commandsRunner->run($commands, $input, $output, $this->getApplication());
        $io->newLine();

        // Install Sample data command is available on monofony/fixtures-plugin
        if (class_exists(InstallSampleDataCommand::class)) {
            $commandExecutor = new CommandExecutor($input, $output, $this->getApplication());
            $commandExecutor->runCommand(InstallSampleDataCommand::getDefaultName(), [], $output);
        }

        return 0;
    }
}
