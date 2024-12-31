<?php

namespace Multidialogo\PdfTextClassifier;

use InvalidArgumentException;

class DocumentClassifier
{
    private Model $model;

    private TextProcessor $textProcessor;

    public function __construct(string $resourcesPath, string $lang, string $modelName, int $modelVersion)
    {
        $modelFilePath = "{$resourcesPath}/{$lang}/models/{$modelName}.v{$modelVersion}.json";
        if (!is_file($modelFilePath)) {
            throw new InvalidArgumentException("Missing model file {$modelFilePath}");
        }

        // Load the saved model from the JSON file
        if (!$this->model = ModelProvider::loadModel($modelFilePath)) {
            throw new InvalidArgumentException("Missing model file {$modelFilePath}");
        }

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
        $this->model->getVectorizer()->transform($texts);

        // Apply TF-IDF transformation
        $this->model->getTransformer()->transform($texts);

        // Return the predictions
        return $this->model->getClassifier()->predict($texts);
    }
}