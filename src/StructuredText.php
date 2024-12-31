<?php

namespace Multidialogo\PdfTextClassifier;

class StructuredText
{
    private string $raw;

    private array $titles;

    private array $texts;

    private array $notes;

    private array $tables;

    public function __construct(string $text)
    {
        $this->raw = $text;
    }

    public function getRaw(): string
    {
        return $this->raw;
    }

    public function getTitles(): array
    {
        return $this->titles;
    }

    public function getTexts(): array
    {
        return $this->texts;
    }

    public function getNotes(): array
    {
        return $this->notes;
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    public function addTitle(string $text): self
    {
        $this->titles[] = $text;

        return $this;
    }

    public function addText(string $text): self
    {
        $this->texts[] = $text;

        return $this;
    }

    public function addNote(string $text): self
    {
        $this->notes[] = $text;

        return $this;
    }

    public function addTable(string $text): self
    {
        $this->tables[] = $text;

        return $this;
    }
}