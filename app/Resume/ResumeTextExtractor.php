<?php

namespace App\Resume;

use Smalot\PdfParser\Parser;
use Throwable;

class ResumeTextExtractor
{
    public const MAX_CHARS = 20000;

    /**
     * @throws ResumeException
     */
    public function extract(string $path): string
    {
        try {
            $text = (new Parser())->parseFile($path)->getText();
        } catch (Throwable) {
            throw new ResumeException('unreadable_pdf');
        }

        $text = trim(preg_replace("/[ \t]+/", ' ', preg_replace("/\R{3,}/", "\n\n", $text)));

        if (mb_strlen($text) < 30) {
            throw new ResumeException('unreadable_pdf');
        }

        return mb_substr($text, 0, self::MAX_CHARS);
    }
}
