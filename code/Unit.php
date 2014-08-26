<?php
namespace Punic;

/**
 * Units helper stuff
 */
class Unit
{

    /**
     * Format a unit string
     * @param int|float|string $number The unit amount
     * @param string $unit The unit identifier (eg 'duration/millisecond' or 'millisecond')
     * @param string $width = 'abbreviated' The format name; it can be 'long' (eg '3 milliseconds'), 'short' (eg '3 ms') or 'narrow' (eg '3ms')
     * @param string $locale = '' The locale to use. If empty we'll use the default locale set in \Punic\Data
     */
    public static function format($number, $unit, $width = 'short', $locale = '')
    {
        $data = \Punic\Data::get('units', $locale);
        if ((strpos($width, '_') === 0) || (!array_key_exists($width, $data))) {
            $widths = array();
            foreach (array_keys($data) as $w) {
                if (strpos($w, '_') !== 0) {
                    $widths[] = $w;
                }
            }
            throw new Exception\ValueNotInList($width, $widths);
        }
        $data = $data[$width];
        if (strpos($unit, '/') === false) {
            $unitCategory = null;
            foreach (array_keys($data) as $c) {
                if (strpos($c, '_') === false) {
                    if (array_key_exists($unit, $data[$c])) {
                        if (is_null($unitCategory)) {
                            $unitCategory = $c;
                            $unitID = $unit;
                        } else {
                            $unitCategory = null;
                            break;
                        }
                    }
                }
            }
        } else {
            list($unitCategory, $unitID) = explode('/', $unit, 2);
        }
        $rules = null;
        if ((strpos($unit, '_') === false) && (!is_null($unitCategory)) && array_key_exists($unitCategory, $data) && array_key_exists($unitID, $data[$unitCategory])) {
            $rules = $data[$unitCategory][$unitID];
        }
        if (is_null($rules)) {
            $units = array();
            foreach ($data as $c => $us) {
                if (strpos($c, '_') === false) {
                    foreach (array_keys($us) as $u) {
                        if (strpos($c, '_') === false) {
                            $units[] = "$c/$u";
                        }
                    }
                }
            }
            throw new \Punic\Exception\ValueNotInList($unit, $units);
        }
        $pluralRule = \Punic\Plural::getRule($number, $locale);
        if (($pluralRule !== 'other') && (!array_key_exists($pluralRule, $rules))) {
            $pluralRule = 'other';
        }
        if (!array_key_exists($pluralRule, $rules)) {
            $format = array_values($rules);
            $format = $format[0];
        } else {
            $format = $rules[$pluralRule];
        }

        return sprintf($format, $number);
    }
}