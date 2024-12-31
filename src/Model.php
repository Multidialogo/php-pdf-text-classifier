<?php

namespace Multidialogo\PdfTextClassifier;

use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Classification\KNearestNeighbors;

class Model
{
    private TokenCountVectorizer $vectorizer;

    private TfIdfTransformer $transformer;

    private KNearestNeighbors $classifier;

    /**
     * @param TokenCountVectorizer $vectorizer
     * @param TfIdfTransformer $transformer
     * @param KNearestNeighbors $classifier
     */
    public function __construct(TokenCountVectorizer $vectorizer, TfIdfTransformer $transformer, KNearestNeighbors $classifier)
    {
        $this->vectorizer = $vectorizer;
        $this->transformer = $transformer;
        $this->classifier = $classifier;
    }

    public function getVectorizer(): TokenCountVectorizer
    {
        return $this->vectorizer;
    }

    public function setVectorizer(TokenCountVectorizer $vectorizer): void
    {
        $this->vectorizer = $vectorizer;
    }

    public function getTransformer(): TfIdfTransformer
    {
        return $this->transformer;
    }

    public function setTransformer(TfIdfTransformer $transformer): void
    {
        $this->transformer = $transformer;
    }

    public function getClassifier(): KNearestNeighbors
    {
        return $this->classifier;
    }

    public function setClassifier(KNearestNeighbors $classifier): void
    {
        $this->classifier = $classifier;
    }


}