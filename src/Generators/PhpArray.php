<?php

namespace Gettext\Generators;

use Gettext\Translations;

class PhpArray extends Generator implements GeneratorInterface
{
    public static $includeHeaders = true;

    /**
     * {@inheritdoc}
     */
    public static function toString(Translations $translations)
    {
        $array = self::toArray($translations);

        return '<?php return '.var_export($array, true).';';
    }

    /**
     * Generates an array with the translations.
     *
     * @param Translations $translations
     *
     * @return array
     */
    public static function toArray(Translations $translations)
    {
        return [
            'domain' => $translations->getDomain(),
            'plural-forms' => $translations->getHeader('Plural-Forms'),
            'messages' => self::buildMessages($translations),
        ];
    }

    /**
     * Generates an array with all translations.
     * 
     * @param Translations $translations
     *
     * @return array
     */
    private static function buildMessages(Translations $translations)
    {
        $pluralForm = $translations->getPluralForms();
        $pluralLimit = is_array($pluralForm) ? ($pluralForm[0] - 1) : null;
        $messages = [];

        if (static::$includeHeaders) {
            $headers = '';

            foreach ($translations->getHeaders() as $name => $value) {
                $headers .= "{$name}: {$value}\n";
            }

            if ($headers !== '') {
                $messages[''] = ['' => [$headers]];
            }
        }

        foreach ($translations as $translation) {
            $context = (string) $translation->getContext();

            if (!isset($messages[$context])) {
                $messages[$context] = [];
            }

            if ($translation->hasPluralTranslations()) {
                $message = $translation->getPluralTranslations($pluralLimit);
                array_unshift($message, $translation->getTranslation());
            } else {
                $message = [$translation->getTranslation()];
            }

            $messages[$context][$translation->getOriginal()] = $message;
        }

        return $messages;
    }
}
