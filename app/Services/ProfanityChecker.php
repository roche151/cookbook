<?php

namespace App\Services;

class ProfanityChecker
{
    /**
     * Expanded multilingual blocklist (ASCII-only to avoid encoding issues in runtime regexes).
     * These are lowercased canonical forms; accent/leet normalization runs before matching.
     */
    private const BLOCKLIST = [
        // English / common internet
        'ass','bastard','bitch','bollock','clit','cock','cuck','cunt','damn','dick','dyke','fag','fuck','jizz','kunt',
        'motherfucker','penis','piss','poop','pussy','shit','slut','spic','twat','whore','wank','wanker','faggot',
        // Spanish / Portuguese / Italian / French / German and common transliterations
        'puta','puto','putain','puttana','pendejo','pendeja','cabron','cabrona','cojon','cojones','verga','mierda',
        'merda','porra','caralho','chingar','chingada','chingado','chingon','gilipollas','gilipolla','cazz0','cazzo',
        'stronzo','suca','sucio','mamada','hijo de puta','hijo-de-puta','hijo_de_puta','hijo puta','conchatumadre',
        'concha','culo','coño','cono','putain','connard','connasse','salope','merde','scheisse','scheisse','arschloch',
        'wichser','hurensohn','arsch','arschloch','kacke',
        // Misc / transliterated slurs
        'chink','gook','wetback','beaner','kike','spade','tranny','retard','idiot','moron','imbecil','imbecile',
        'paki','pakki','raghead','towelhead','camel jockey','camel-jockey','cameljockey','gaandu','gandu','bhenchod',
    ];

    /**
     * Known safe words that might superficially contain blocklist fragments after normalization.
     */
    private const ALLOWLIST = [
        'scunthorpe','assassin','bassoon','passage','passover','classic','classification','analysis'
    ];

    /**
     * Basic leetspeak substitutions to catch obfuscations like f@ck or sh1t.
     */
    private const LEET_MAP = [
        '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '7' => 't', '8' => 'b', '9' => 'g',
        '$' => 's', '@' => 'a', '!' => 'i', '|' => 'l'
    ];

    public static function hasProfanity(?string $text): bool
    {
        if (!is_string($text) || trim($text) === '') {
            return false;
        }

        $normalized = self::normalize($text);
        if ($normalized === '') {
            return false;
        }

        foreach (self::BLOCKLIST as $word) {
            $pattern = self::wordPattern($word);
            if (preg_match($pattern, $normalized, $match)) {
                $hit = strtolower(preg_replace('/[^a-z0-9]/', '', $match[0] ?? ''));
                if ($hit !== '' && in_array($hit, self::ALLOWLIST, true)) {
                    continue; // avoid Scunthorpe-type false positives
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize text: strip accents, apply leetspeak substitutions, lowercase.
     */
    private static function normalize(string $text): string
    {
        $text = self::stripAccents($text);
        $text = strtr($text, self::LEET_MAP);
        $text = strtolower($text);
        return $text;
    }

    /**
     * Build a regex that allows non-alphanumeric separators between characters and enforces word boundaries.
     * This catches f.u.c.k, f u c k, but not Scunthorpe (due to boundary guards).
     */
    private static function wordPattern(string $word): string
    {
        $parts = array_map(fn($c) => preg_quote($c, '/'), str_split($word));
        $body = implode('[^a-z0-9]*', $parts);
        return '/(?<![a-z0-9])' . $body . '(?![a-z0-9])/i';
    }

    private static function stripAccents(string $text): string
    {
        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        return $transliterated !== false ? $transliterated : $text;
    }
}
