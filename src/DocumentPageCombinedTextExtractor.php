<?php

namespace Multidialogo\PdfTextClassifier;

class DocumentPageCombinedTextExtractor
{
    // Define weights for each text category
    private const int TITLE_WEIGHT = 3;

    private const int TEXT_WEIGHT = 2;

    private const int NOTE_WEIGHT = 1;

    private const int TABLE_WEIGHT = 1;

    /**
     * Extract all types of text (titles, notes, tables, and normal text) from the StructuredPage
     *
     * @param StructuredPage $page
     * @param int $collectionSize
     * @return string
     */
    public static function getCombinedText(StructuredPage $page, int $collectionSize): string
    {
        $pageWeight = max(1, $collectionSize - $page->getPosition());

        $structuredText = $page->getText();

        $combinedText = "";

        // Get Titles (with extra weight)
        foreach ($structuredText->getTitles() as $title) {
            $combinedText .= str_repeat(" ", self::TITLE_WEIGHT * $pageWeight) . $title;
        }

        // Get Notes (with normal weight)
        foreach ($structuredText->getNotes() as $note) {
            $combinedText .= str_repeat(" ", self::NOTE_WEIGHT * $pageWeight) . $note;
        }

        // Get Normal Text (with higher weight)
        foreach ($structuredText->getTexts() as $text) {
            $combinedText .= str_repeat(" ", self::TEXT_WEIGHT * $pageWeight) . $text;
        }

        // Get Tables (with lower weight)
        foreach ($structuredText->getTables() as $table) {
            $combinedText .= str_repeat(" ", self::TABLE_WEIGHT * $pageWeight) . $table;
        }

        return $combinedText;
    }
}