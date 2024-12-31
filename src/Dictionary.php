<?php

namespace Multidialogo\PdfTextClassifier;

use InvalidArgumentException;

class Dictionary
{
    private array $stopWords;

    private array $lemmas;

    public function __construct(string $resourcesPath)
    {
        if (!is_dir($resourcesPath)) {
            throw new InvalidArgumentException("Invalid resources path: {$resourcesPath}");
        }

        $lemmasFile = "{$resourcesPath}/lemmas.txt";
        if (!is_file($lemmasFile)) {
            throw new InvalidArgumentException("Missing lemmas file {$lemmasFile}");
        }

        $stopWordsFile = "{$resourcesPath}/stopwords.txt";
        if (!is_file($stopWordsFile)) {
            throw new InvalidArgumentException("Missing stop words file {$stopWordsFile}");
        }

        $this->lemmas = $this->loadLemmasFromFile($lemmasFile);
        $this->stopWords = $this->loadWordsFromFile($stopWordsFile);
    }

    public function getStopWords(): array
    {
        return $this->stopWords;
    }

    public function getLemmas(): array
    {
        return $this->lemmas;
    }

    // Helper function to load words (stopwords) from a file
    private function loadWordsFromFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $words = explode("\n", trim($content));  // Assuming each word is on a new line
        return $words;
    }

    // Helper function to load lemmas from a file
    private function loadLemmasFromFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $lines = explode("\n", trim($content));

        $lemmas = [];
        foreach ($lines as $line) {
            list($word, $lemma) = explode(" => ", $line);
            $lemmas[$word] = $lemma;
        }

        return $lemmas;
    }
}