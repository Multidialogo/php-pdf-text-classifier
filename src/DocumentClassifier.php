<?php

namespace Multidialogo\PdfTextClassifier;

use InvalidArgumentException;

use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Classification\KNearestNeighbors;

class DocumentClassifier
{
    private TokenCountVectorizer $vectorizer;

    private TfIdfTransformer $transformer;

    private KNearestNeighbors $classifier;

    private TextProcessor $textProcessor;

    public function __construct(string $resourcesPath, string $lang, string $modelName, int $modelVersion)
    {
        $modelFilePath = "{$resourcesPath}/{$lang}/models/{$modelName}.v{$modelVersion}.json";
        if (!is_file($modelFilePath)) {
            throw new InvalidArgumentException("Missing model file {$modelFilePath}");
        }

        // Load the saved model from the JSON file
        $modelData = json_decode(file_get_contents($modelFilePath), true);

        if (!$modelData) {
            throw new InvalidArgumentException("Invalid model file format.");
        }

        // Load the components from the saved model
        $this->vectorizer = $modelData['vectorizer'];
        $this->transformer = $modelData['transformer'];
        $this->classifier = $modelData['classifier'];

        $this->textProcessor = new TextProcessor("{$resourcesPath}/{$lang}");

    }

    public function classify(array $structuredPages)
    {
        $texts = [];
        $collectionSize = count($structuredPages);

        foreach ($structuredPages as $pageIndex => $page) {
            if (!$page instanceof StructuredPage) {
                throw new InvalidArgumentException("Element at position {$pageIndex} is not of type: " . StructuredPage::class);
            }

            // Extract and combine different types of text from StructuredText
            $combinedText = DocumentPageCombinedTextExtractor::getCombinedText($page, $collectionSize);
            $processedText = $this->textProcessor->process($combinedText);
            $texts[] = $processedText;
        }

        // Vectorize the text
        $this->vectorizer->transform($texts);

        // Apply TF-IDF transformation
        $this->transformer->transform($texts);

        // Return the predictions (titles and summaries)
        return $this->classifier->predict($texts);
    }
}