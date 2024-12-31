<?php

namespace Multidialogo\PdfTextClassifier;

class ModelProvider
{
    /**
     * Load a previously saved model from a JSON file.
     *
     * @param string $modelFilePath
     * @return array
     */
    public static function loadModel(string $modelFilePath): array
    {
        if (file_exists($modelFilePath)) {
            $modelJson = file_get_contents($modelFilePath);
            $model = json_decode($modelJson, true);

            // Return the model if it exists
            if ($model && isset($model['vectorizer'], $model['transformer'], $model['classifier'])) {
                return $model;
            }

            throw new RuntimeException("Invalid model file at {$modelFilePath}");
        }

        return [];
    }
}