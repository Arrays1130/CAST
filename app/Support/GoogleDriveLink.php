<?php

namespace App\Support;

class GoogleDriveLink
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $original,
    ) {}

    public static function parse(?string $url): ?self
    {
        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host) ?: $host;

        $allowed = [
            'drive.google.com',
            'docs.google.com',
            'sheets.google.com',
            'slides.google.com',
        ];

        if (! in_array($host, $allowed, true)) {
            return null;
        }

        if (preg_match('#/file/d/([a-zA-Z0-9_-]+)#', $url, $match)) {
            return new self($match[1], 'file', $url);
        }

        if (preg_match('#/document/d/([a-zA-Z0-9_-]+)#', $url, $match)) {
            return new self($match[1], 'document', $url);
        }

        if (preg_match('#/presentation/d/([a-zA-Z0-9_-]+)#', $url, $match)) {
            return new self($match[1], 'presentation', $url);
        }

        if (preg_match('#/spreadsheets/d/([a-zA-Z0-9_-]+)#', $url, $match)) {
            return new self($match[1], 'spreadsheet', $url);
        }

        if (preg_match('#/folders/([a-zA-Z0-9_-]+)#', $url, $match)) {
            return new self($match[1], 'folder', $url);
        }

        if (preg_match('#[?&]id=([a-zA-Z0-9_-]+)#', $url, $match)) {
            return new self($match[1], 'file', $url);
        }

        return null;
    }

    public function openUrl(): string
    {
        return match ($this->type) {
            'folder' => 'https://drive.google.com/drive/folders/'.$this->id,
            'document' => 'https://docs.google.com/document/d/'.$this->id.'/edit',
            'presentation' => 'https://docs.google.com/presentation/d/'.$this->id.'/edit',
            'spreadsheet' => 'https://docs.google.com/spreadsheets/d/'.$this->id.'/edit',
            default => 'https://drive.google.com/file/d/'.$this->id.'/view',
        };
    }

    public function previewUrl(): ?string
    {
        return match ($this->type) {
            'folder' => null,
            'document' => 'https://docs.google.com/document/d/'.$this->id.'/preview',
            'presentation' => 'https://docs.google.com/presentation/d/'.$this->id.'/preview',
            'spreadsheet' => 'https://docs.google.com/spreadsheets/d/'.$this->id.'/preview',
            default => 'https://drive.google.com/file/d/'.$this->id.'/preview',
        };
    }

    public function label(): string
    {
        return match ($this->type) {
            'folder' => 'Google Drive folder',
            'document' => 'Google Doc',
            'presentation' => 'Google Slides',
            'spreadsheet' => 'Google Sheet',
            default => 'Google Drive file',
        };
    }
}
