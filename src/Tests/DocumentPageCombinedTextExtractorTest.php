<?php

namespace Multidialogo\PdfTextClassifier\Tests;

use Multidialogo\PdfTextClassifier\DocumentPageCombinedTextExtractor;
use Multidialogo\PdfTextClassifier\StructuredPage;
use Multidialogo\PdfTextClassifier\StructuredText;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DocumentPageCombinedTextExtractorTest extends TestCase
{
    use ProphecyTrait;

    public function testGetCombinedText()
    {
        // Prophecy for StructuredText
        $structuredText = $this->prophesize(StructuredText::class);
        $structuredText->getTitles()->willReturn(['Title1', 'Title2']);
        $structuredText->getNotes()->willReturn(['Note1', 'Note2']);
        $structuredText->getTexts()->willReturn(['Text1', 'Text2']);
        $structuredText->getTables()->willReturn(['Table1', 'Table2']);

        // Prophecy for StructuredPage
        $structuredPage = $this->prophesize(StructuredPage::class);
        $structuredPage->getPosition()->willReturn(1);
        $structuredPage->getText()->willReturn($structuredText->reveal());

        // Define the collection size
        $collectionSize = 5;

        // Calculate expected output
        $pageWeight = max(1, $collectionSize - $structuredPage->reveal()->getPosition()); // 4
        $expectedText = str_repeat(" ", 3 * $pageWeight) . 'Title1' .
            str_repeat(" ", 3 * $pageWeight) . 'Title2' .
            str_repeat(" ", 1 * $pageWeight) . 'Note1' .
            str_repeat(" ", 1 * $pageWeight) . 'Note2' .
            str_repeat(" ", 2 * $pageWeight) . 'Text1' .
            str_repeat(" ", 2 * $pageWeight) . 'Text2' .
            str_repeat(" ", 1 * $pageWeight) . 'Table1' .
            str_repeat(" ", 1 * $pageWeight) . 'Table2';

        // Call the method
        $combinedText = DocumentPageCombinedTextExtractor::getCombinedText($structuredPage->reveal(), $collectionSize);

        // Assert the result
        $this->assertEquals($expectedText, $combinedText);
    }
}
