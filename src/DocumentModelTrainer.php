<?php

namespace Multidialogo\PdfTextClassifier;

use InvalidArgumentException;

use Phpml\ModelManager;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\Classification\KNearestNeighbors;

class DocumentModelTrainer
{
    public const string OVERRIDE_MODE = 'OVERRIDE';

    public const string MERGE_MODE = 'MERGE';

    private string $resourcesPath;

    private string $modelFilePath;

    private TokenCountVectorizer $vectorizer;

    private TfIdfTransformer $transformer;

    private KNearestNeighbors $classifier;

    private TextProcessor $textProcessor;

    public function __construct(string $resourcesPath, string $lang, string $modelName, int $modelVersion)
    {
        $this->resourcesPath = "{$resourcesPath}/{$lang}";
        $this->modelFilePath = "{$this->resourcesPath}/models/{$modelName}.v{$modelVersion}";

        // Initialize vectorizer and transformer
        $this->vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
        $this->transformer = new TfIdfTransformer();

        // Initialize classifier (KNN as an example)
        $this->classifier = new KNearestNeighbors();

        $this->textProcessor = new TextProcessor($this->resourcesPath);
    }

    /**
     * Train the classifier with StructuredPage data and store the model in a JSON file.
     *
     * @param array $structuredPages
     * @param array $labels
     * @param bool $mode, if self::OVERRIDE_MODE the classifier will be rewritten, instead will be extended
     */
    public function train(array $structuredPages, array $labels, bool $mode = self::OVERRIDE_MODE)
    {
        $modelFilePath = "{$this->resourcesPath}/{$this->modelFilePath}.json";

        $modelManager = new ModelManager();

        if (self::MERGE_MODE === $mode) {
            $this->classifier = $modelManager->restoreFromFile($modelFilePath);
        }

        $texts = [];
        $collectionSize = count($structuredPages);
        foreach ($structuredPages as $pageIndex => $page) {
            if (!$page instanceof StructuredPage) {
                throw new InvalidArgumentException("Element at position {$pageIndex} is not of type: " . StructuredPage::class);
            }
            // Extract and combine different types of text from StructuredText
            $combinedText = DocumentPageCombinedTextExtractor::getCombinedText($page, $collectionSize);
            $texts[] = $this->textProcessor->process($combinedText);
        }

        // Vectorize the text
        $this->vectorizer->fit($texts);
        $this->vectorizer->transform($texts);

        // Apply TF-IDF transformation
        $this->transformer->fit($texts);
        $this->transformer->transform($texts);

        // Train the classifier
        $this->classifier->train($texts, $labels);

        $modelManager->saveToFile($this->classifier, $modelFilePath);
    }
}
