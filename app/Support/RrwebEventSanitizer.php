<?php

namespace App\Support;

/**
 * rrweb's replayer calls document.createTextNode(n.textContent) without coercing null.
 * JSON null → JS null → visible "null" text. Same for some mutation.value / input.text fields.
 */
final class RrwebEventSanitizer
{
    /**
     * @param  list<array<string, mixed>>  $events
     * @return list<array<string, mixed>>
     */
    public static function sanitizeEvents(array $events): array
    {
        return array_map(fn (mixed $ev) => self::sanitizeNode($ev, false), $events);
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    public static function sanitizeSingleEvent(array $event): array
    {
        $out = self::sanitizeNode($event, false);

        return is_array($out) ? $out : $event;
    }

    private static function sanitizeNode(mixed $node, bool $inAttributes): mixed
    {
        if (! is_array($node)) {
            return $node;
        }

        if (self::isList($node)) {
            return array_map(fn (mixed $item) => self::sanitizeNode($item, false), $node);
        }

        /** @var array<string, mixed> $node */
        if ($inAttributes) {
            foreach ($node as $k => $v) {
                if (is_array($v)) {
                    $node[$k] = self::sanitizeNode($v, false);
                }
            }

            return $node;
        }

        foreach ($node as $k => $v) {
            if ($v === null && in_array($k, ['textContent', 'text', 'value'], true)) {
                $node[$k] = '';
            } elseif (is_array($v)) {
                $node[$k] = self::sanitizeNode($v, $k === 'attributes');
            }
        }

        return $node;
    }

    private static function isList(array $arr): bool
    {
        if ($arr === []) {
            return true;
        }

        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
