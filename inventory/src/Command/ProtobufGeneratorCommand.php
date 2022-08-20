<?php

namespace App\Command;

use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

#[AsCommand(
    name: 'protobuf:generate',
    description: 'Generate gRPC service interfaces and related protobuf classes, \n
                  depending on proto files located in proto directory.',
)]
class ProtobufGeneratorCommand extends Command
{
    private const TMP_PROTOBUF_DIR = 'var/tmp_protobuf';

    /** @var ContainerBagInterface */
    private $params;

    /** @var Filesystem */
    private $filesystem;

    /** @var SymfonyStyle */
    private $io;

    /**
     * @param Filesystem $filesystem
     * @param ContainerBagInterface $params
     */
    public function __construct(Filesystem $filesystem, ContainerBagInterface $params)
    {
        $this->params = $params;
        $this->filesystem = $filesystem;
        parent::__construct();
    }

    /**
     * @inheritDoc
    */
    protected function configure(): void
    {
        $this->addOption(
            'server_proto_file',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'The server(s) proto files names. If not specified all proto files will be used.',
            ['*']
        );
        $this->addOption(
            'client_proto_file',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'The client(s) proto files names. If not specified all proto files will be used.',
            ['*']
        );
    }

    /**
     * Set SymfonyStyle to command input and output.
     *
     * @param SymfonyStyle $io
     * @return void
     */
    protected function setIO(SymfonyStyle $io): void
    {
        $this->io = $io;
    }

    /**
     * Get protobuf directory path.
     *
     * @return string
     */
    protected function getProtobufDirectoryPath(): string
    {
        return $this->getProjectDir() . '/src/Protobuf';
    }

    /**
     * Get project root directory.
     *
     * @return string
     */
    protected function getProjectDir(): string
    {
        return $this->params->get('kernel.project_dir');
    }

    /**
     * Create protobuf directory.
     *
     * @return void
     */
    protected function createProtobufDirectory(): void
    {
        try {
            $this->filesystem->mkdir(
                Path::normalize($this->getProtobufDirectoryPath()),
            );
        } catch (IOExceptionInterface $exception) {
            $this->io->error("An error occurred while creating your directory at ".$exception->getPath());
        }
    }

    private function createTempDir(string $projectDir): void
    {
        $tmpProtobufDir = Path::normalize($projectDir . '/' . self::TMP_PROTOBUF_DIR);
        if ($this->filesystem->exists($tmpProtobufDir)) {
            $this->filesystem->remove($tmpProtobufDir);
        }
        $this->filesystem->mkdir($tmpProtobufDir);
    }

    /**
     * Generate protobuf PHP classes.
     *
     * @param array<string> $serversProtoFiles
     * @param array<string> $clientsProtoFiles
     * @return void
     */
    protected function generateProtobufFiles(array $serversProtoFiles, array $clientsProtoFiles): void
    {
        array_walk($serversProtoFiles, function (&$protoFile) {
            $protoFile = 'proto/servers/' . $protoFile . '.proto';
        });
        array_walk($clientsProtoFiles, function (&$protoFile) {
            $protoFile = 'proto/clients/' . $protoFile . '.proto';
        });
        $projectDir = $this->getProjectDir();
        $this->createTempDir($projectDir);
        $serversProcess = Process::fromShellCommandline(
            'protoc -I proto/servers '.
            ' --php_out=' . self::TMP_PROTOBUF_DIR.
            ' --php-grpc_out=' . self::TMP_PROTOBUF_DIR.
            ' '. implode(' ', $serversProtoFiles),
            $projectDir
        );
        $serversProcess->run();
        if (!$serversProcess->isSuccessful()) {
            throw new ProcessFailedException($serversProcess);
        }

        $clientsProcess = Process::fromShellCommandline(
            'protoc -I proto/clients '.
            ' --php_out=' . self::TMP_PROTOBUF_DIR.
            ' --grpc_out=' . self::TMP_PROTOBUF_DIR.
            ' --plugin=protoc-gen-grpc=/usr/bin/grpc_php_plugin '.
            implode(' ', $clientsProtoFiles),
            $projectDir
        );
        $clientsProcess->run();
        if (!$clientsProcess->isSuccessful()) {
            throw new ProcessFailedException($clientsProcess);
        }
        $this->filesystem->rename($projectDir . '/var/tmp_protobuf/App/Protobuf/Generated', $projectDir . '/src/Protobuf/Generated', true);
        $this->filesystem->remove(self::TMP_PROTOBUF_DIR);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setIO(new SymfonyStyle($input, $output));
        $this->createProtobufDirectory();
        $this->generateProtobufFiles($input->getOption('server_proto_file'), $input->getOption('client_proto_file'));
        return Command::SUCCESS;
    }
}
