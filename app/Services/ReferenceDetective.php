<?php

namespace App\Services;

use App\Models\Paper;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;
use ZipArchive;

class ReferenceDetective
{
    /**
     * @return array{
     *     status: string,
     *     unused: list<string>,
     *     missing: list<string>,
     *     warnings: list<string>,
     *     scanned_at: string|null
     * }
     */
    public function scan(Paper $paper): array
    {
        if (! $paper->hasLocalFile()) {
            return $this->result('unavailable', [], [], [
                'Upload a PDF (or DOCX) to run Reference Detective. Drive-only papers cannot be scanned yet.',
            ]);
        }

        try {
            $text = $this->extractText($paper);
        } catch (Throwable $e) {
            return $this->result('error', [], [], [
                'Could not read this file: '.$e->getMessage(),
            ]);
        }

        if (blank($text) || strlen(trim($text)) < 80) {
            return $this->result('error', [], [], [
                'Not enough extractable text. Prefer a text-based PDF (not a scan/image).',
            ]);
        }

        return $this->analyze($text);
    }

    /**
     * @return array{
     *     status: string,
     *     unused: list<string>,
     *     missing: list<string>,
     *     warnings: list<string>,
     *     scanned_at: string|null
     * }
     */
    public function analyze(string $text): array
    {
        $normalized = $this->normalize($text);
        [$body, $refsBlock, $warning] = $this->splitBodyAndReferences($normalized);

        $warnings = [];
        if ($warning) {
            $warnings[] = $warning;
        }

        $references = $this->parseReferenceLines($refsBlock);
        $citations = $this->parseCitations($body);

        if ($references === []) {
            $warnings[] = 'No bibliography entries detected after a References heading. Check that the list is labeled References / Bibliography / Works Cited.';
        }

        $unused = [];
        foreach ($references as $reference) {
            if (! $this->referenceAppearsInBody($reference, $body)) {
                $unused[] = $reference['raw'];
            }
        }

        $missing = [];
        foreach ($citations as $citation) {
            if (! $this->citationCoveredByReferences($citation, $references)) {
                $missing[] = $citation['display'];
            }
        }

        $missing = array_values(array_unique($missing));

        return $this->result('ok', $unused, $missing, $warnings);
    }

    public function extractText(Paper $paper): string
    {
        $path = $paper->file_path;
        $name = strtolower((string) ($paper->original_filename ?: $path));
        $binary = Storage::disk('papers')->get($path);

        if (str_ends_with($name, '.pdf')) {
            return $this->extractPdf($binary);
        }

        if (str_ends_with($name, '.docx')) {
            return $this->extractDocx($binary);
        }

        if (str_ends_with($name, '.doc')) {
            throw new \RuntimeException('Legacy .doc files are not supported. Upload PDF or DOCX.');
        }

        throw new \RuntimeException('Unsupported file type for scanning.');
    }

    protected function extractPdf(string $binary): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'cast-pdf-');
        if ($tmp === false) {
            throw new \RuntimeException('Could not create a temp file.');
        }

        try {
            file_put_contents($tmp, $binary);
            $parser = new PdfParser;
            $pdf = $parser->parseFile($tmp);

            return $pdf->getText();
        } finally {
            @unlink($tmp);
        }
    }

    protected function extractDocx(string $binary): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'cast-docx-');
        if ($tmp === false) {
            throw new \RuntimeException('Could not create a temp file.');
        }

        try {
            file_put_contents($tmp, $binary);
            $zip = new ZipArchive;
            if ($zip->open($tmp) !== true) {
                throw new \RuntimeException('Invalid DOCX archive.');
            }
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            if ($xml === false) {
                throw new \RuntimeException('DOCX has no document.xml.');
            }

            $xml = preg_replace('/<\/w:p>/', "\n", $xml) ?? $xml;
            $text = strip_tags($xml);

            return html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        } finally {
            @unlink($tmp);
        }
    }

    /**
     * @return array{0: string, 1: string, 2: ?string}
     */
    protected function splitBodyAndReferences(string $text): array
    {
        if (preg_match('/\n\s*(references|bibliography|works cited|list of references)\s*\n/i', $text, $match, PREG_OFFSET_CAPTURE)) {
            $pos = $match[0][1];
            $body = substr($text, 0, $pos);
            $refs = substr($text, $pos + strlen($match[0][0]));

            return [$body, $refs, null];
        }

        return [$text, '', 'Could not find a References / Bibliography heading. Scanning the whole document as body only.'];
    }

    /**
     * @return list<array{raw: string, author: ?string, year: ?string, needle: string}>
     */
    protected function parseReferenceLines(string $refsBlock): array
    {
        $lines = preg_split('/\n+/', $refsBlock) ?: [];
        $items = [];

        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line) ?? $line);
            if (strlen($line) < 20) {
                continue;
            }
            if (preg_match('/^(references|bibliography|works cited|list of references)$/i', $line)) {
                continue;
            }

            $year = null;
            if (preg_match('/\((\d{4}[a-z]?)\)/', $line, $m)) {
                $year = $m[1];
            } elseif (preg_match('/\b((?:19|20)\d{2}[a-z]?)\b/', $line, $m)) {
                $year = $m[1];
            }

            $author = null;
            if (preg_match('/^([A-Z][A-Za-z\'\-]+)(?:,|\s+&|\s+and\b)/', $line, $m)) {
                $author = $m[1];
            } elseif (preg_match('/^([A-Z][A-Za-z\'\-]+)/', $line, $m)) {
                $author = $m[1];
            }

            $needle = strtolower(substr($line, 0, 48));

            $items[] = [
                'raw' => $line,
                'author' => $author ? strtolower($author) : null,
                'year' => $year ? strtolower($year) : null,
                'needle' => $needle,
            ];
        }

        return $items;
    }

    /**
     * @return list<array{display: string, author: string, year: string}>
     */
    protected function parseCitations(string $body): array
    {
        $citations = [];

        if (preg_match_all('/\(([A-Z][A-Za-z\'\-]+)(?:\s+(?:et\s+al\.?|&|and)\s+[A-Z][A-Za-z\'\-]+)?,?\s*((?:19|20)\d{2}[a-z]?)\)/', $body, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $citations[] = [
                    'display' => $match[1].' ('.$match[2].')',
                    'author' => strtolower($match[1]),
                    'year' => strtolower($match[2]),
                ];
            }
        }

        if (preg_match_all('/\b([A-Z][A-Za-z\'\-]+)\s*\(((?:19|20)\d{2}[a-z]?)\)/', $body, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $citations[] = [
                    'display' => $match[1].' ('.$match[2].')',
                    'author' => strtolower($match[1]),
                    'year' => strtolower($match[2]),
                ];
            }
        }

        return $citations;
    }

    /**
     * @param  array{raw: string, author: ?string, year: ?string, needle: string}  $reference
     */
    protected function referenceAppearsInBody(array $reference, string $body): bool
    {
        $bodyLower = strtolower($body);

        if ($reference['author'] && $reference['year']) {
            if (str_contains($bodyLower, $reference['author']) && str_contains($bodyLower, $reference['year'])) {
                return true;
            }
        }

        if ($reference['author'] && str_contains($bodyLower, $reference['author'])) {
            return true;
        }

        // Fallback: first chunk of the reference line somewhere in body (rare for full APA lines)
        return false;
    }

    /**
     * @param  array{display: string, author: string, year: string}  $citation
     * @param  list<array{raw: string, author: ?string, year: ?string, needle: string}>  $references
     */
    protected function citationCoveredByReferences(array $citation, array $references): bool
    {
        if ($references === []) {
            return false;
        }

        foreach ($references as $reference) {
            $authorOk = $reference['author'] && str_contains($reference['author'], $citation['author'])
                || ($reference['author'] && str_contains($citation['author'], $reference['author']));
            $yearOk = $reference['year'] && str_starts_with($reference['year'], substr($citation['year'], 0, 4));

            if ($authorOk && $yearOk) {
                return true;
            }

            if ($authorOk && str_contains(strtolower($reference['raw']), $citation['year'])) {
                return true;
            }
        }

        return false;
    }

    protected function normalize(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }

    /**
     * @param  list<string>  $unused
     * @param  list<string>  $missing
     * @param  list<string>  $warnings
     * @return array{
     *     status: string,
     *     unused: list<string>,
     *     missing: list<string>,
     *     warnings: list<string>,
     *     scanned_at: string|null
     * }
     */
    protected function result(string $status, array $unused, array $missing, array $warnings): array
    {
        return [
            'status' => $status,
            'unused' => array_values($unused),
            'missing' => array_values($missing),
            'warnings' => array_values($warnings),
            'scanned_at' => now()->toIso8601String(),
        ];
    }
}
