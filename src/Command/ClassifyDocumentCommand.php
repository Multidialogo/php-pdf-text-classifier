<?php

namespace Multidialogo\PdfTextClassifier\Command;

use Multidialogo\PdfTextClassifier\DocumentClassifier;
use Multidialogo\PdfTextClassifier\DocumentStructureExtractor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Serializer\SerializerInterface;

class ClassifyDocumentCommand extends Command
{
    private DocumentClassifier $classifier;

    public function __construct(DocumentClassifier $classifier)
    {
        parent::__construct();

        // Injecting the DocumentClassifier dependency
        $this->classifier = $classifier;
    }

    protected function configure(): void
    {
        $this->setName('document:classify')
            ->setDescription('Classifies a document into title and resume categories')
            ->addArgument('resourcesPath', InputArgument::REQUIRED, 'Path to resources')
            ->addArgument('lang', InputArgument::REQUIRED, 'Language of the model')
            ->addArgument('modelName', InputArgument::REQUIRED, 'Name of the model')
            ->addArgument('modelVersion', InputArgument::REQUIRED, 'Version of the model')
            ->addArgument('documentFile', InputArgument::REQUIRED, 'Path to the structured document file')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file to save the result');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $resourcesPath = $input->getArgument('resourcesPath');
        $lang = $input->getArgument('lang');
        $modelName = $input->getArgument('modelName');
        $modelVersion = (int) $input->getArgument('modelVersion');
        $documentPath = $input->getArgument('documentPath');
        $outputFile = $input->getOption('output');


        // Initialize the DocumentClassifier with the provided model details
        $this->classifier = new DocumentClassifier($resourcesPath, $lang, $modelName, $modelVersion);

        // Classify the document
        $predictions = $this->classifier->classify(DocumentStructureExtractor::getStructuredPages($documentPath));

        // Output the result to the console
        foreach ($predictions as $prediction) {
            $output->writeln("Prediction: " . $prediction);
        }

        // If the --output option is set, save the predictions to the specified file
        if ($outputFile) {
            file_put_contents($outputFile, implode("\n", $predictions));
            $output->writeln("Predictions saved to {$outputFile}");
        }

        return Command::SUCCESS;
    }
}
