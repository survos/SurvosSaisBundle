<?php
declare(strict_types=1);

namespace Survos\SaisBundle\Util;

/**
 * Storage sharding helpers for ~80M assets.
 *
 * Default layout:
 *   - 2 shards × 2 hex chars each → e.g. ab/cd/
 *   - Leaves ~80,000,000 / 65,536 ≈ ~1,220 files per leaf dir (comfortable).
 *
 * Examples:
 *   originalKey('e3b0c44298fc1c149afbf4c8996fb924', 'jpg')
 *     => 'o/e3/b0/e3b0c44298fc1c149afbf4c8996fb924.jpg'
 *
 *   variantKey('e3b0c44298fc1c149afbf4c8996fb924', 'medium', 'webp')
 *     => 'v/medium/e3/b0/e3b0c44298fc1c149afbf4c8996fb924.webp'
 */
final class ShardedKey
{
    /**
     * @param string $hex 16
     * @return string 32-char lowercase hex
     */
    public static function normalizeHex(string $hex): string
    {
        $h = strtolower(trim($hex));
        $h = preg_replace('/[^0-9a-f]/', '', $h) ?? '';
        if ($h === '') {
            throw new \InvalidArgumentException('Empty/invalid hex hash.');
        }
        // Accept 16 or 32; left-pad to 32 so sharding is consistent.
        if (\strlen($h) === 16) {
//            $h = str_pad($h, 32, '0', STR_PAD_LEFT);
        } elseif (\strlen($h) !== 32) {
            throw new \InvalidArgumentException('Hash must be 16 or 32 hex characters.');
        }
        return $h;
    }

    /**
     * Build a shard path like "ab/cd" from the (normalized) 32-hex hash.
     * @param string $hex 16 or 32 hex chars
     * @param int $shards number of directories (default 2)
     * @param int $shardLen hex chars per directory (default 2)
     * @return array{shardPath:string, hex:string}
     */
    public static function shard(string $hex, int $shards = 1, int $shardLen = 3): array
    {
        $hex = self::normalizeHex($hex);
        $parts = [];
        $pos = 0;
        for ($i = 0; $i < $shards; $i++) {
            $parts[] = substr($hex, $pos, $shardLen);
            $pos += $shardLen;
        }
        return ['shardPath' => implode('/', $parts), 'hex' => $hex];
    }

    /**
     * Key for ORIGINAL binary (you choose the bucket/prefix outside).
     * Layout: abc/<hex>[.<ext>]
     */
    public static function originalKey(string $hex, ?string $ext = null): string
    {
        ['shardPath' => $p, 'hex' => $h] = self::shard($hex);
        $suffix = $ext ? ('.' . ltrim($ext, '.')) : '';
        return "$p/$h$suffix";
    }

    /**
     * Key for a VARIANT (e.g., liip preset).
     * Layout: v/<preset>/ab/cd/<hex>.<format>
     */
    public static function variantKey(string $hex, string $preset, string $format): string
    {
        ['shardPath' => $p, 'hex' => $h] = self::shard($hex);
        $preset = trim($preset);
        $format = ltrim($format, '.');
        return "v/$preset/$p/$h.$format";
    }

    /**
     * Optional: a temp/inflight key (helpful during downloads).
     * Layout: tmp/ab/cd/<hex>/<random>.part
     */
    public static function tempKey(string $hex): string
    {
        ['shardPath' => $p, 'hex' => $h] = self::shard($hex);
        $rand = bin2hex(random_bytes(4));
        return "tmp/$p/$h/$rand.part";
    }
}
