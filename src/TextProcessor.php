<?php

namespace Multidialogo\PdfTextClassifier;

use InvalidArgumentException;

class TextProcessor
{
    private Dictionary $dictionary;

    public function __construct(string $resourcesPath)
    {
        $this->dictionary = new Dictionary("{$resourcesPath}/dictionary");
    }

    public function process(string $text): string
    {
        // Step 1: Convert text to lowercase
        $text = strtolower($text);

        // Step 2: Remove non-alphabetical characters (retain spaces and accented characters)
        $text = preg_replace('/[^a-zàèéìòùáéíóú ]/', ' ', $text);

        // Step 3: Remove extra spaces (e.g., multiple spaces between words)
        $text = preg_replace('/\s+/', ' ', $text);

        // Step 4: Remove leading and trailing spaces
        $text = trim($text);


        // Step 6: Remove stopwords
        $text = $this->removeStopwords($text, $this->dictionary->getStopWords());

        // Step 7: Lemmatization
        $text = $this->lemmatizeText($text);

        return $text;
    }

    private function removeStopwords(string $text, array $stopwords): string
    {
        // Split the text into words
        $words = explode(' ', $text);

        // Filter out the stopwords
        $filteredWords = array_filter($words, function($word) use ($stopwords) {
            return !in_array($word, $stopwords);
        });

        // Join the words back into a string
        return implode(' ', $filteredWords);
    }

    private function lemmatizeText(string $text): string
    {
        $lemmatizationDictionary = $this->dictionary->getLemmas();

        $words = explode(' ', $text);
        $lemmatizedWords = array_map(function($word) use ($lemmatizationDictionary) {
            return isset($lemmatizationDictionary[$word]) ? $lemmatizationDictionary[$word] : $word;
        }, $words);

        return implode(' ', $lemmatizedWords);
    }
}
