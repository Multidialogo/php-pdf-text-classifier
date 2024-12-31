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

class TrainDocumentModelCommandTest extends TestCase
{
    use ProphecyTrait;

    public function testExecute()
    {
        // Create a prophecy for DocumentModelTrainer
        $trainerProphecy = $this->prophesize(DocumentModelTrainer::class);

        // Predict the train method will be called once with specific argument types
        $trainerProphecy->train(
            $this->isType('array'), // Structured pages
            $this->isType('array'), // Labels
            $this->isType('int')    // Mode
        )->shouldBeCalled();

        // Mock DocumentStructureExtractor
        $extractorProphecy = $this->prophesize(DocumentStructureExtractor::class);
        $extractorProphecy::getStructuredPages('/path/to/document.pdf')
            ->willReturn(['dummy_page_structure']);

        // Initialize the command with the trainer mock
        $trainerMock = $trainerProphecy->reveal();
        $command = new TrainDocumentModelCommand($trainerMock);

        // Create a Symfony Console Application and add the command
        $application = new Application();
        $application->add($command);

        // Get the command tester for the TrainDocumentModelCommand
        $commandTester = new CommandTester($application->find('document:train-model'));

        // Mock the question helper for user input
        $helperProphecy = $this->prophesize(QuestionHelper::class);
        $helperProphecy->ask(
            $this->any(),
            $this->any(),
            $this->any()
        )->willReturn('/path/to/document.pdf', 'classification', 'n', 'label', 'done');

        $command->getHelperSet()->set($helperProphecy->reveal(), 'question');

        // Execute the command
        $commandTester->execute([
            'resourcesPath' => '/path/to/resources',
            'lang' => 'en',
            'modelName' => 'test_model',
            'modelVersion' => '1',
        ]);

        // Assert command output
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Document added: Classification - classification', $output);
        $this->assertStringContainsString('Model training complete!', $output);
        $this->assertEquals(0, $commandTester->getStatusCode()); // Ensure the command exits successfully
    }
}
