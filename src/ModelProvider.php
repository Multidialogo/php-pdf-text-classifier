<?php

namespace Multidialogo\PdfTextClassifier;


use InvalidArgumentException;

class ModelProvider
{
    /**
     * Load a previously saved model from a JSON file.
     *
     * @param string $modelFilePath
     * @return array
     */
    public static function loadModel(string $modelFilePath): ?Model
    {
        if ($modelData = static::loadModelData($modelFilePath)) {
            // Deserialize and return the model with objects instead of raw data
            return new Model(
                static::deserializeVectorizer($modelData['vectorizer']),
                static::deserializeTransformer($modelData['transformer']),
                static::deserializeClassifier($modelData['classifier'])
            );
        }

        return null;
    }

    public static function loadModelData(string $modelFilePath): array
    {
        $modelJson = file_get_contents($modelFilePath);
        $modelData = json_decode($modelJson, true);

        if (!static::modelExists($modelFilePath)) {
            return [];
        }

        // Return the model if it exists
        if (isset($modelData['vectorizer'], $modelData['transformer'], $modelData['classifier'])) {
            return $modelData;
        }

        throw new InvalidArgumentException("Invalid model file {$modelFilePath}");
    }

    public static function modelExists(string $modelFilePath): bool
    {
        return file_exists($modelFilePath);
    }

    /**
     * Deserialize the vectorizer data back into an object.
     *
     * @param array $data
     * @return TokenCountVectorizer
     */
    private static function deserializeVectorizer(array $data): TokenCountVectorizer
    {
        // You might need to customize this based on how your vectorizer is structured.
        return new TokenCountVectorizer($data['options']); // Assuming you store options for the vectorizer.
    }

    /**
     * Deserialize the transformer data back into an object.
     *
     * @param array $data
     * @return TfIdfTransformer
     */
    private static function deserializeTransformer(array $data): TfIdfTransformer
    {
        // Deserialize the transformer here if necessary
        return new TfIdfTransformer($data['options']); // Assuming you store options for the transformer.
    }

    /**
     * Deserialize the classifier data back into an object.
     *
     * @param array $data
     * @return KNearestNeighbors
     */
    private static function deserializeClassifier(array $data): KNearestNeighbors
    {
        // Deserialize the classifier (this might be complex depending on how KNN is serialized)
        return new KNearestNeighbors($data['k']); // Assuming 'k' is stored for KNN.
    }
}