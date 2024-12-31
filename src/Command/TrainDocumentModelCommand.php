<?php

namespace Multidialogo\PdfTextClassifier\Command;

use Multidialogo\PdfTextClassifier\DocumentModelTrainer;
use Multidialogo\PdfTextClassifier\DocumentStructureExtractor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;

class TrainDocumentModelCommand extends Command
{
    private DocumentModelTrainer $trainer;

    public function __construct(DocumentModelTrainer $trainer)
    {
        parent::__construct();
        $this->trainer = $trainer;
    }

    protected function configure()
    {
        $this
            ->setName('document:train-model')
            ->setDescription('Interactively train a document classification model.')
            ->addArgument('resourcesPath', InputArgument::REQUIRED, 'Path to resources directory')
            ->addArgument('lang', InputArgument::REQUIRED, 'Language for the model')
            ->addArgument('modelName', InputArgument::REQUIRED, 'The name of the model')
            ->addArgument('modelVersion', InputArgument::REQUIRED, 'Version number of the model');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get the basic arguments
        $resourcesPath = $input->getArgument('resourcesPath');
        $lang = $input->getArgument('lang');
        $modelName = $input->getArgument('modelName');
        $modelVersion = (int)$input->getArgument('modelVersion');

        // Create an instance of DocumentModelTrainer
        $trainer = new DocumentModelTrainer($resourcesPath, $lang, $modelName, $modelVersion);

        // Initialize the question helper to prompt the user
        $questionHelper = $this->getHelper('question');

        $labels = [];

        // Loop to input multiple documents
        while (true) {
            // Get the file path from the user
            $question = new Question('Please enter the file path (or type "done" to finish): ');
            $filePath = $questionHelper->ask($input, $output, $question);
            if ($filePath === 'done') {
                break;
            }

            // Get the title and resume for the document
            $question = new Question('Enter the classification of the document: ');
            $classification = $questionHelper->ask($input, $output, $question);

            // Ask for the label (you can modify this based on your model's needs)
            $question = new Question('Enter the label for this document: ');
            $label = $questionHelper->ask($input, $output, $question);
            $labels[] = $label;

            // Ask for the label (you can modify this based on your model's needs)
            $question = new Question('Enter the 2nd label for this document: ');
            $label = $questionHelper->ask($input, $output, $question);
            $labels[] = $label;

            $trainer->train(DocumentStructureExtractor::getStructuredPages($filePath), $labels, DocumentModelTrainer::MERGE_MODE);

            $output->writeln("Document added: Classification - {$classification}");
        }

        // Now train the model with the gathered data

        $output->writeln('<info>Model training complete!</info>');

        return Command::SUCCESS;
    }
}
