<?php

namespace Multidialogo\PdfTextClassifier;

class StructuredPage
{
    private int $position;

    private StructuredText $text;

    public function __construct(int $position, StructuredText $text)
    {
        $this->position = $position;
        $this->text = $text;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getText(): StructuredText
    {
        return $this->text;
    }
}