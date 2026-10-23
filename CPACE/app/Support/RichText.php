<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\HtmlString;

/**
 * Formatted text for question stems and explanations, edited in the Word-style
 * editor (fonts, sizes, colours, highlight, alignment, indent, spacing, lists,
 * tables and pictures).
 *
 * Content is stored as a whitelisted HTML subset. Older rows are plain text, so
 * every method accepts both: render() escapes plain text and keeps its line
 * breaks, and passes HTML through sanitize() first.
 */
class RichText
{
    private const ALLOWED_TAGS = [
        'p', 'div', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'del', 'sub', 'sup', 'span', 'font',
        'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'blockquote', 'hr',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'img',
    ];

    /** Elements dropped together with everything inside them. */
    private const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'noscript', 'template', 'svg', 'math', 'form', 'textarea', 'select'];

    private const BLOCK_CLOSERS = 'p|div|li|tr|td|th|h[1-4]|blockquote';

    /** Applied to every rendered element of these kinds so pages with CSS resets still show them properly. */
    private const FORCED_STYLE = [
        'ul' => 'margin:.3em 0 .3em 1.5em;padding:0',
        'ol' => 'margin:.3em 0 .3em 1.5em;padding:0',
        'h1' => 'font-size:1.5em;font-weight:700;margin:.4em 0',
        'h2' => 'font-size:1.3em;font-weight:700;margin:.4em 0',
        'h3' => 'font-size:1.15em;font-weight:700;margin:.4em 0',
        'h4' => 'font-size:1em;font-weight:700;margin:.4em 0',
        'blockquote' => 'margin:.4em 0 .4em 1.5em;padding:0',
        'hr' => 'border:0;border-top:1px solid #bbb;margin:.6em 0',
    ];

    public static function looksLikeHtml(?string $value): bool
    {
        return $value !== null
            && preg_match('/<\/?(p|div|br|b|strong|i|em|u|s|strike|del|sub|sup|ul|ol|li|span|font|h[1-4]|blockquote|hr|table|thead|tbody|tfoot|tr|td|th|img)\b[^>]*>/i', $value) === 1;
    }

    /** Reduce user-supplied markup to the whitelist. Plain text is returned unchanged. */
    public static function sanitize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim(str_replace("\u{200B}", '', $value));
        if ($value === '' || ! self::looksLikeHtml($value)) {
            return $value;
        }

        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><body>' . $value . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $doc->getElementsByTagName('body')->item(0);
        if (! $body) {
            return trim(strip_tags($value));
        }

        self::clean($body);

        $html = '';
        foreach ($body->childNodes as $child) {
            $html .= $doc->saveHTML($child);
        }
        $html = trim($html);

        // An emptied editor leaves behind markers like <div><br></div>; a picture, table or rule is content.
        return self::plain($html) === '' && ! preg_match('/<(img|table|hr)\b|display:\s*inline-block/i', $html) ? '' : $html;
    }

    /** HTML that is safe to print with {{ }} (or {!! !!}). */
    public static function render(?string $value): HtmlString
    {
        $value = (string) $value;
        if ($value === '') {
            return new HtmlString('');
        }
        if (self::looksLikeHtml($value)) {
            $html = self::withUrls((string) self::sanitize($value));
            // A table wider than the screen scrolls sideways instead of breaking the page layout.
            $html = preg_replace('#<table(?= |>)#i', '<div style="overflow-x:auto;max-width:100%"><table', $html);
            $html = str_ireplace('</table>', '</table></div>', $html);

            // Pictures, tables and shapes adapt to the width they are shown in (phone included).
            if (preg_match('/<(table|img)\b|inline-block/i', $html)) {
                $html = '<style>' . self::fitCss() . '</style><div class="rt-fit">' . $html . '</div>';
            }

            return new HtmlString($html);
        }

        return new HtmlString(nl2br(e($value)));
    }

    /**
     * Rules that make rich content fit the space it is shown in. They key off the width of the
     * wrapper (container query), so they apply on a real phone and inside the editor's phone preview alike.
     */
    public static function fitCss(): string
    {
        return '.rt-fit{container-type:inline-size;max-width:100%;overflow-wrap:anywhere}'
            . '.rt-fit img{max-width:100%!important;height:auto!important}'
            . '.rt-fit [style*="inline-block"]{max-width:100%}'
            . '@container (max-width:560px){'
            . '.rt-fit>div[style*="overflow-x"]{overflow-x:visible!important}'
            . '.rt-fit table{width:100%!important;max-width:100%!important;table-layout:auto!important}'
            . '.rt-fit td,.rt-fit th{width:auto!important;min-width:0!important;padding:4px 5px!important;font-size:.92em;overflow-wrap:anywhere;word-break:break-word}'
            . '.rt-fit [style*="margin-left"]{margin-left:min(1.2em,6%)!important}'
            . '.rt-fit [style*="inline-block"]{height:auto!important}'
            . '}';
    }

    /** Unformatted text, for table cells, search and truncation. */
    public static function plain(?string $value): string
    {
        $value = (string) $value;
        if (self::looksLikeHtml($value)) {
            $value = preg_replace('#</(' . self::BLOCK_CLOSERS . ')>|<br\s*/?>#i', ' ', $value);
            $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return trim(preg_replace('/\s+/u', ' ', str_replace(["\xC2\xA0", "\u{200B}"], ' ', $value)));
    }

    private static function clean(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                if ($child->nodeType !== XML_TEXT_NODE) {
                    $node->removeChild($child); // comments, processing instructions, ...
                }
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $node->removeChild($child);
                continue;
            }

            self::clean($child);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                // Unknown wrapper (links, ...): keep its already-cleaned children in its place.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            if (! self::cleanAttributes($child, $tag)) {
                $node->removeChild($child);
            }
        }
    }

    /** @return bool false when the element must be dropped altogether */
    private static function cleanAttributes(DOMElement $el, string $tag): bool
    {
        $color = $el->getAttribute('color'); // legacy <font color="...">
        $style = $el->getAttribute('style');
        $src = $el->getAttribute('src');
        $alt = $el->getAttribute('alt');
        $colspan = (int) $el->getAttribute('colspan');
        $rowspan = (int) $el->getAttribute('rowspan');

        foreach (iterator_to_array($el->attributes) as $attr) {
            $el->removeAttribute($attr->name);
        }

        $kept = [];
        foreach (explode(';', $style) as $rule) {
            [$prop, $val] = array_pad(explode(':', $rule, 2), 2, '');
            $prop = strtolower(trim($prop));
            $val = trim(preg_replace('/\s+/', ' ', $val));
            if ($prop !== '' && ($clean = self::styleValue($prop, $val)) !== null) {
                $kept[$prop] = $clean;
            }
        }
        if ($tag === 'font' && $color !== '' && self::validColor($color)) {
            $kept['color'] = $color;
        }

        if ($tag === 'img') {
            $file = self::imageFile($src);
            if ($file === null) {
                return false;
            }
            // Stored host-independent; render() / the editor expand it to a real URL.
            $el->setAttribute('src', QuestionExhibit::DIRECTORY . '/' . $file);
            if ($alt !== '') {
                $el->setAttribute('alt', mb_substr($alt, 0, 200));
            }
            $kept['max-width'] = '100%';
            $kept['height'] = $kept['height'] ?? 'auto';
        }

        if (in_array($tag, ['td', 'th'], true)) {
            if ($colspan > 1) {
                $el->setAttribute('colspan', (string) min($colspan, 20));
            }
            if ($rowspan > 1) {
                $el->setAttribute('rowspan', (string) min($rowspan, 20));
            }
        }

        // A shape keeps its proportions when the screen is narrower than it (see fitCss()).
        if (($kept['display'] ?? '') === 'inline-block' && isset($kept['width'], $kept['height'])
            && preg_match('/^([\d.]+)px$/', $kept['width'], $w) && preg_match('/^([\d.]+)px$/', $kept['height'], $h) && (float) $h[1] > 0) {
            $kept['aspect-ratio'] = $w[1] . ' / ' . $h[1];
        }

        if ($tag === 'table') {
            $kept['border-collapse'] = 'collapse';
            $kept['max-width'] = '100%';
        }

        if (isset(self::FORCED_STYLE[$tag])) {
            foreach (explode(';', self::FORCED_STYLE[$tag]) as $rule) {
                [$p, $v] = explode(':', $rule, 2);
                if ($tag === 'hr' && $p === 'border-top' && isset($kept[$p])) {
                    continue; // a line the author styled keeps its own type, weight and colour
                }
                $kept[$p] = $v;
            }
        }

        if ($tag === 'hr' && isset($kept['border-top'])) {
            $line = $kept['border-top']; // must come after the border:0 reset
            unset($kept['border-top']);
            $kept['border-top'] = $line;
        }

        if ($kept) {
            $el->setAttribute('style', implode(';', array_map(fn ($k, $v) => "$k:$v", array_keys($kept), $kept)));
        }

        return true;
    }

    /** One validated CSS declaration value, or null to drop it. */
    private static function styleValue(string $prop, string $val): ?string
    {
        $lower = strtolower($val);

        switch ($prop) {
            case 'color':
            case 'background-color':
                return self::validColor($val) ? $val : null;
            case 'text-align':
                return in_array($lower, ['left', 'center', 'right', 'justify'], true) ? $lower : null;
            case 'vertical-align':
                return in_array($lower, ['top', 'middle', 'bottom', 'baseline', 'sub', 'super'], true) ? $lower : null;
            case 'font-weight':
                return preg_match('/^(bold|normal|bolder|lighter|[1-9]00)$/', $lower) ? $lower : null;
            case 'font-style':
                return in_array($lower, ['italic', 'normal'], true) ? $lower : null;
            case 'text-decoration':
            case 'text-decoration-line':
                return preg_match('/^(underline|line-through|none)( (underline|line-through))?$/', $lower) ? $lower : null;
            case 'font-family':
                return preg_match('/^[A-Za-z0-9 ,\'"\-]{1,100}$/', $val) ? $val : null;
            case 'font-size':
                return self::boundedLength($val, ['px' => 400, 'pt' => 300, 'em' => 10, 'rem' => 10, '%' => 500]);
            case 'line-height':
                return preg_match('/^(\d(\.\d{1,2})?|\d{2,3}%)$/', $lower) ? $lower : self::boundedLength($val, ['px' => 200, 'pt' => 150]);
            case 'margin-left':
            case 'margin-right':
            case 'margin-top':
            case 'margin-bottom':
            case 'padding-left':
            case 'padding-right':
            case 'padding-top':
            case 'padding-bottom':
            case 'text-indent':
                return $lower === '0' ? '0' : self::boundedLength($val, ['px' => 400, 'pt' => 300]);
            case 'padding':
                return preg_match('/^\d{1,3}px( \d{1,3}px){0,3}$/', $lower) ? $lower : null;
            case 'margin':
                return preg_match('/^(0|-?\d{1,3}px)( (0|-?\d{1,3}px)){0,3}$/', $lower) ? $lower : null;
            case 'width':
            case 'height':
                return $lower === 'auto' ? 'auto' : self::boundedLength($val, ['px' => 2000, '%' => 100]);
            case 'border':
            case 'border-top':
            case 'border-bottom':
            case 'border-left':
            case 'border-right':
                if ($lower === 'none' || $lower === '0') {
                    return 'none';
                }
                if (preg_match('/^(\d{1,2})px (solid|dashed|dotted|double) (.+)$/i', $val, $m) && (int) $m[1] <= 12 && self::validColor($m[3])) {
                    return $val;
                }

                return null;
            case 'display':
                return in_array($lower, ['inline-block', 'block', 'inline'], true) ? $lower : null;
            case 'border-radius':
                return preg_match('/^\d{1,3}(px|%)( \d{1,3}(px|%)){0,3}$/', $lower) ? $lower : null;
            case 'clip-path':
                return preg_match('/^polygon\(\s*\d{1,3}(\.\d+)?%\s+\d{1,3}(\.\d+)?%(\s*,\s*\d{1,3}(\.\d+)?%\s+\d{1,3}(\.\d+)?%){2,15}\s*\)$/', $lower) ? $lower : null;
            case 'table-layout':
                return in_array($lower, ['fixed', 'auto'], true) ? $lower : null;
            case 'box-sizing':
                return in_array($lower, ['border-box', 'content-box'], true) ? $lower : null;
            case 'border-collapse':
                return in_array($lower, ['collapse', 'separate'], true) ? $lower : null;
        }

        return null;
    }

    /** @param array<string,int> $max unit => largest allowed number */
    private static function boundedLength(string $val, array $max): ?string
    {
        if (! preg_match('/^(-?\d{1,4}(?:\.\d{1,2})?)(px|pt|em|rem|%)$/i', $val, $m)) {
            return null;
        }
        $unit = strtolower($m[2]);

        return isset($max[$unit]) && abs((float) $m[1]) <= $max[$unit] ? $m[1] . $unit : null;
    }

    /** The stored file name of a picture this feature saved itself (any URL form), or null. */
    private static function imageFile(string $src): ?string
    {
        return preg_match('#^(?:https?://[^/\s"\'<>]+)?(?:/[A-Za-z0-9._\-/]*?)?/?(?:storage/)?' . QuestionExhibit::DIRECTORY . '/([A-Za-z0-9._-]+)$#', $src, $m) === 1 ? $m[1] : null;
    }

    /** Swap stored picture paths for URLs that load on this server. */
    public static function withUrls(string $html): string
    {
        return preg_replace_callback(
            '#(<img\b[^>]*?\ssrc=")' . QuestionExhibit::DIRECTORY . '/([A-Za-z0-9._-]+)(")#i',
            fn ($m) => $m[1] . e(QuestionExhibit::imageUrl(QuestionExhibit::DIRECTORY . '/' . $m[2])) . $m[3],
            $html
        );
    }

    private static function validColor(string $value): bool
    {
        return preg_match('/^(#[0-9a-f]{3,8}|rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*[\d.]+\s*)?\)|[a-z]{3,20})$/i', trim($value)) === 1;
    }
}
