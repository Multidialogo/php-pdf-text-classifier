<?php

namespace Test\Multidialogo\PdfTextClassifier;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Multidialogo\PdfTextClassifier\DocumentStructureExtractor;
use Smalot\PdfParser\Parser;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\Element\TextElement;
use Multidialogo\PdfTextClassifier\StructuredText;
use Multidialogo\PdfTextClassifier\StructuredPage;

class DocumentStructureExtractorTest extends TestCase
{
    use ProphecyTrait;

    public function testGetStructuredPages()
    {
        // Mock the StructuredText class using Prophecy
        $mockStructuredText = $this->prophesize(StructuredText::class);
        $mockStructuredText->addTitle(Argument::type('string'))->shouldBeCalledTimes(1);
        $mockStructuredText->addNote(Argument::type('string'))->shouldBeCalledTimes(1);
        $mockStructuredText->addText(Argument::type('string'))->shouldBeCalledTimes(1);
        $mockStructuredText->addTable(Argument::type('string'))->shouldBeCalledTimes(1);

        // Mock the Parser class using Prophecy
        $mockParser = $this->prophesize(Parser::class);

        // Mock the Page class and set up the elements on the page
        $mockPage = $this->prophesize(Page::class);
        $mockPage->getText()->willReturn("Sample Text from Page");

        // Mocking the PDF with a single page
        $mockParser->parseFile(Argument::type('string'))->willReturn($mockParser);
        $mockParser->getPages()->willReturn([1 => $mockPage]);

        // Mock the TextElement class (elements in the page)
        $mockTextElement = $this->prophesize(TextElement::class);
        $mockTextElement->getText()->willReturn('Some text content');
        $mockTextElement->getFontSize()->willReturn(16); // Font size for title

        $mockTextElement2 = $this->prophesize(TextElement::class);
        $mockTextElement2->getText()->willReturn('Note content');
        $mockTextElement2->getFontSize()->willReturn(8); // Font size for note

        $mockTextElement3 = $this->prophesize(TextElement::class);
        $mockTextElement3->getText()->willReturn('Table data');
        $mockTextElement3->getFontSize()->willReturn(12); // Regular font size
        $mockTextElement3->getPosition()->willReturn(['x' => 50, 'y' => 10]); // Position for table data

        // Set up the elements for the page
        $mockPage->getElements()->willReturn([$mockTextElement, $mockTextElement2, $mockTextElement3]);

        // Call the method to test
        $structuredPages = DocumentStructureExtractor::getStructuredPages('path/to/pdf');

        // Assert the expected behavior
        $this->assertCount(1, $structuredPages); // Expecting 1 structured page

        // Retrieve the structured page
        $structuredPage = $structuredPages[0];
        $this->assertInstanceOf(StructuredPage::class, $structuredPage);
        $this->assertEquals(1, $structuredPage->getPageNumber());

        // Verify that the methods on StructuredText
    }

    public function testGetStructuredPagesWithRealPdf()
    {
        $pdfFilePath = __DIR__ . '/fixtures/testGetStructuredPagesWithRealPdf.01.pdf';

        // Call the method to test with a real PDF file
        $structuredPages = DocumentStructureExtractor::getStructuredPages($pdfFilePath);

        // Check that the structured pages array is not empty
        $this->assertNotEmpty($structuredPages);

        // Retrieve the structured page from the result
        $structuredPage = $structuredPages[0];

        // Ensure the page is an instance of StructuredPage
        $this->assertInstanceOf(StructuredPage::class, $structuredPage);

        // Validate that the position of the structured page is correct
        $this->assertEquals(1, $structuredPage->getPosition());

        // Retrieve the StructuredText object for this page
        $structuredText = $structuredPage->getText();

        // Ensure the StructuredText instance is valid
        $this->assertInstanceOf(StructuredText::class, $structuredText);

        // Check if the raw text from the page is not empty
        $this->assertNotEmpty($structuredText->getRaw());

        // Verify that the extracted titles match the expected content
        $titles = $structuredText->getTitles();
        $this->assertCount(1, $titles);
        $this->assertEquals('Convocazione Assemblea Condominiale', $titles[0]);

        // Verify that no notes are extracted (as none were in the PDF)
        $this->assertCount(0, $structuredText->getNotes());

        // Verify that the table extraction matches the expected content
        $tables = $structuredText->getTables();
        $this->assertCount(1, $tables);
        $table = $tables[0];
        $this->assertEquals(['Voce di spesa', 'Importo (€)', 'Data di scadenza', 'Stato'], $table->getHeaders());
        $this->assertCount(4, $table->getRows());

        // Verify that the extracted texts match the expected paragraphs
        $texts = $structuredText->getTexts();
        $this->assertGreaterThanOrEqual(1, count($texts));
        $this->assertStringContainsString('convoca l’assemblea condominiale per il giorno 15 gennaio 2024 alle ore 18:00.', $texts[0]);
    }
}
