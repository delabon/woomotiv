<?php

namespace Woomotiv\Framework;

class Helper
{
    /**
     * Return a sanitized url
     */
    static function sanitize(string $url): string
    {
        $url = str_replace(array('_', '-'), '', $url);
        $url = trim($url, '/');
        $url = str_replace('/', '_', $url);

        return preg_replace('`[^A-Za-z0-9_]`', '', $url);
    }

    /**
     * Sanitize path
     */
    static function sanitizedPath(string $path): string
    {
        $name = sanitize($path);

        if (empty($name)) {
            $name = 'index';
        }

        return $name;
    }

    /**
     * is user logged in from cookies
     */
    static function is_loggedin(): bool
    {
        foreach ($_COOKIE as $cookie => $value) {
            if (strpos($cookie, '_logged_in_') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return excluded array from string
     */
    static function excludedListToArray($value): array
    {
        if (empty($value)) {
            return array();
        }

        $value = preg_replace('/\s?\n/', ',', $value);
        $value = str_replace(' ', ',', $value);
        $value = trim(preg_replace('/,,/', ',', $value), ',');

        if (strpos($value, ',') !== false) {
            return explode(',', $value);
        }

        return array($value);
    }

    /**
     * is asset expluded ?
     * @param string $link
     * @param array|string $list
     */
    static function isExcluded($link, $list, $ignoreQuery = false): bool
    {
        if (!is_array($list)) {
            $list = self::excludedListToArray($list);
        }

        $link = preg_replace('/.*?https?:\/\//', '', $link);

        if ($ignoreQuery && strpos($link, '?') !== false) {
            return false;
        }

        foreach ($list as $excluded) {
            $excluded_link = preg_replace('/.*?https?:\/\//', '', $excluded);

            if (strpos($excluded_link, '*') !== false) {
                $excluded_parts = explode('*', $excluded_link);

                if (strpos($link, $excluded_parts[0]) !== false) {
                    return true;
                }
            } elseif ($link === $excluded_link) {
                return true;
            }
        }

        return false;
    }
}
