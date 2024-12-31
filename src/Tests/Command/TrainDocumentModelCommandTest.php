<?php

namespace Test\Multidialogo\PdfTextClassifier\Command;

use Multidialogo\PdfTextClassifier\Command\TrainDocumentModelCommand;
use Multidialogo\PdfTextClassifier\DocumentModelTrainer;
use Multidialogo\PdfTextClassifier\DocumentStructureExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Helper\QuestionHelper;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class TrainDocumentModelCommandTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $trainer;
    private ObjectProphecy $documentStructureExtractor;

    protected function setUp(): void
    {
        parent::setUp();

        // Prophecies for the dependencies
        $this->trainer = $this->prophesize(DocumentModelTrainer::class);
        $this->documentStructureExtractor = $this->prophesize(DocumentStructureExtractor::class);
    }

    public function testExecute()
    {
        $resourcesPath = '/path/to/resources';
        $lang = 'en';
        $modelName = 'testModel';
        $modelVersion = 1;

        // Create an application and add the command
        $application = new Application();
        $command = new TrainDocumentModelCommand($this->trainer->reveal());
        $application->add($command);

        // Create CommandTester to test the command execution
        $commandTester = new CommandTester($command);

        // Mock user input using QuestionHelper
        $questionHelper = $this->createMock(QuestionHelper::class);
        $command->getHelperSet()->set($questionHelper, 'question');
        $questionHelper->method('ask')
            ->willReturnOnConsecutiveCalls(
                '/path/to/document1.pdf', // File path
                'Document Classification 1', // Classification
                'Label1',  // First label
                'Label2',  // Second label
                'done'     // End loop
            );

        // Mock the static method behavior of DocumentStructureExtractor::getStructuredPages
        $this->documentStructureExtractor->getStructuredPages('/path/to/document1.pdf')
            ->willReturn(['mocked_page_data'])
            ->shouldBeCalledOnce();

        // Mock the train method
        $this->trainer->train(['mocked_page_data'], ['Label1', 'Label2'], DocumentModelTrainer::MERGE_MODE)
            ->shouldBeCalledOnce();

        // Execute the command
        $commandTester->execute([
            'command' => 'document:train-model',
            'resourcesPath' => $resourcesPath,
            'lang' => $lang,
            'modelName' => $modelName,
            'modelVersion' => $modelVersion,
        ]);

        // Get the command output
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Document added: Classification - Document Classification 1', $output);
        $this->assertStringContainsString('Model training complete!', $output);
    }
}
