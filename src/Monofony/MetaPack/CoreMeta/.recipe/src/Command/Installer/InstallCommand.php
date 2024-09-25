<?php

declare(strict_types=1);

namespace App\Command\Installer;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\RuntimeException;

#[AsCommand(
    name: 'app:install',
    description: 'Installs AppName in your preferred environment.',
)]
class InstallCommand extends Command
{
    private ?CommandExecutor $commandExecutor = null;

    /**
     * @var string[][]
     *
     * @psalm-var array{0: array{command: string, message: string}, 1: array{command: string, message: string}, 2: array{command: string, message: string}}
     */
    private array $commands = [
        [
            'command' => 'database',
            'message' => 'Setting up the database.',
        ],
        [
            'command' => 'setup',
            'message' => 'Website configuration.',
        ],
        [
            'command' => 'assets',
            'message' => 'Installing assets.',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command installs AppName.
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->commandExecutor = new CommandExecutor($input, $output, $this->getApplication());
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Installing AppName...');
        $io->writeln($this->getLogo());

        $errored = false;
        foreach ($this->commands as $step => $command) {
            try {
                $io->newLine();
                $io->section(sprintf(
                    'Step %d of %d. <info>%s</info>',
                    $step + 1,
                    count($this->commands),
                    $command['message']
                ));
                $this->commandExecutor->runCommand('app:install:'.$command['command'], [], $output);
                $output->writeln('');
            } catch (RuntimeException $exception) {
                $errored = true;
            }
        }

        $io->newLine(2);
        $io->success($this->getProperFinalMessage($errored));
        $io->info('You can now open your website at the following path under the website root: /');

        return 0;
    }

    private function getProperFinalMessage(bool $errored): string
    {
        if ($errored) {
            return 'AppName has been installed, but some error occurred.';
        }

        return 'AppName has been successfully installed.';
    }

    private function getLogo(): string
    {
        return '
        
$$\      $$\                                $$$$$$\                               
$$$\    $$$ |                              $$  __$$\                              
$$$$\  $$$$ | $$$$$$\  $$$$$$$\   $$$$$$\  $$ /  \__|$$$$$$\  $$$$$$$\  $$\   $$\ 
$$\$$\$$ $$ |$$  __$$\ $$  __$$\ $$  __$$\ $$$$\    $$  __$$\ $$  __$$\ $$ |  $$ |
$$ \$$$  $$ |$$ /  $$ |$$ |  $$ |$$ /  $$ |$$  _|   $$ /  $$ |$$ |  $$ |$$ |  $$ |
$$ |\$  /$$ |$$ |  $$ |$$ |  $$ |$$ |  $$ |$$ |     $$ |  $$ |$$ |  $$ |$$ |  $$ |
$$ | \_/ $$ |\$$$$$$  |$$ |  $$ |\$$$$$$  |$$ |     \$$$$$$  |$$ |  $$ |\$$$$$$$ |
\__|     \__| \______/ \__|  \__| \______/ \__|      \______/ \__|  \__| \____$$ |
                                                                        $$\   $$ |
                                                                        \$$$$$$  |
                                                                         \______/ 
                                                                         
        ';
    }
}
