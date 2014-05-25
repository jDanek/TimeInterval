<?php

namespace Jdanek;

/**
 * Trida pro zpracovani casovych intervalu na slovni podobu
 * 
 * Pouziti: TimeInterval::toString($timestamp);
 * 
 * @author jDanek <jdanek.eu>
 */
class TimeInterval
{

    const LAST_TIME = "před", FUTURE_TIME = "za";

    /** @var int */
    private static $index = 0;

    /** @var array */
    private static $units = array("sekundy", "minuty", "hodiny", "dny", "týdny", "měsíce", "roky");

    /** @var array */
    private static $length = array("60", "60", "24", "7", "4.35", "12");

    /** @var array */
    private static $finalString = array('status' => '', 'quantity' => '', 'unit' => '');

    /**
     * Vraci casovy usek ve slovni podobe
     * 
     * @example "za 3 hodiny"
     * @param int $timestamp
     * @return string
     * @throws \RuntimeException
     */
    public static function toString($timestamp)
    {
        if (!is_numeric($timestamp))
        {
            throw new \RuntimeException("Neplatný vstup timestamp");
        }

        // zjisteni minuleho/budouciho casu
        $result = self::pastOrFuture($timestamp);

        // zjisteni jednotky
        $counter = count(self::$length) - 1;
        for (self::$index = 0; $result['difference'] >= self::$length[self::$index] && self::$index < $counter; self::$index++)
        {
            $result['difference'] = $result['difference'] / self::$length[self::$index];
        }

        // zaokrouhleni
        $result['difference'] = round($result['difference']);

        // rozhodovani
        switch (self::$units[self::$index]) {
            case "sekundy":
                self::seconds($result['string'], $result['difference']);
                break;

            case "minuty":
                self::minutes($result['string'], $result['difference']);
                break;

            case "hodiny":
                self::hours($result['string'], $result['difference']);
                break;

            case "dny":
                self::days($result['string'], $result['difference']);
                break;

            case "týdny":
                self::weeks($result['string'], $result['difference']);
                break;

            case "měsíce":
                self::months($result['string'], $result['difference']);
                break;

            case "roky":
                self::years($result['string'], $result['difference']);
                break;
            default:
                break;
        }

        // navrat stringu
        return implode(" ", self::$finalString);
    }

    /**
     * Ziskani informace o casu, zda je minuly / budouci
     * 
     * @param int $timestamp
     * @return array pole s rozdilem casu a slovesnou formou
     */
    private static function pastOrFuture($timestamp)
    {
        $time = time();
        $result = array();

        if ($time >= $timestamp)
        {
            // cas minuly
            $result = array('difference' => ($time - $timestamp), 'string' => self::LAST_TIME);
        }
        else
        {
            // cas budouci
            $result = array('difference' => ($timestamp - $time), 'string' => self::FUTURE_TIME);
        }

        return $result;
    }

    /**
     * Sestaveni vyjadreni pro sekundy
     * 
     * @param string $status     minuly / budouci cas
     * @param int    $difference rozdil casu mezi zadanym a aktualnim
     */
    private static function seconds($status, $difference)
    {
        self::$finalString['status'] = (self::LAST_TIME === $status ? self::LAST_TIME : self::FUTURE_TIME);
        self::$finalString['quantity'] = (self::LAST_TIME === $status ? $difference : $difference);

        if (1 == $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "sekundou" : "sekundu");
            self::$finalString['quantity'] = (self::LAST_TIME === $status ? "jednou" : "jednu");
        }
        else
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "sekundami" : "sekund");
        }
    }

    /**
     * Sestaveni vyjadreni pro minuty
     * 
     * @param string $status     minuly / budouci cas
     * @param int    $difference rozdil casu mezi zadanym a aktualnim
     */
    private static function minutes($status, $difference)
    {
        self::$finalString['status'] = (self::LAST_TIME === $status ? self::LAST_TIME : self::FUTURE_TIME);
        self::$finalString['quantity'] = (self::LAST_TIME === $status ? $difference : $difference);

        if (1 == $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "minutou" : "minutu");
            self::$finalString['quantity'] = (self::LAST_TIME === $status ? "jednou" : "jednu");
        }
        else
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "minutami" : "minut");
        }
    }

    /**
     * Sestaveni vyjadreni pro hodiny
     * 
     * @param string $status     minuly / budouci cas
     * @param int    $difference rozdil casu mezi zadanym a aktualnim
     */
    private static function hours($status, $difference)
    {
        self::$finalString['status'] = (self::LAST_TIME === $status ? self::LAST_TIME : self::FUTURE_TIME);
        self::$finalString['quantity'] = (self::LAST_TIME === $status ? $difference : $difference);

        if (1 == $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "hodinou" : "hodinu");
            self::$finalString['quantity'] = (self::LAST_TIME === $status ? "" : "");
        }
        elseif (2 <= $difference && 5 > $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "hodinami" : "hodiny");
        }
        elseif ($difference >= 5)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "hodinami" : "hodin");
        }
    }

    /**
     * Sestaveni vyjadreni pro dny
     * 
     * @param string $status     minuly / budouci cas
     * @param int    $difference rozdil casu mezi zadanym a aktualnim
     */
    private static function days($status, $difference)
    {
        self::$finalString['status'] = (self::LAST_TIME === $status ? self::LAST_TIME : self::FUTURE_TIME);
        self::$finalString['quantity'] = (self::LAST_TIME === $status ? $difference : $difference);

        if (1 == $difference)
        {
            self::$finalString['status'] = (self::LAST_TIME === $status ? "" : "");
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "včera" : "zítra");
            self::$finalString['quantity'] = (self::LAST_TIME === $status ? "" : "");
        }
        else
        {
            if (1 < $difference && 5 > $difference)
            {
                self::$finalString['unit'] = (self::LAST_TIME === $status ? "dny" : "dny");
            }
            else
            {
                self::$finalString['unit'] = (self::LAST_TIME === $status ? "dny" : "dní");
            }
        }
    }

    /**
     * Sestaveni vyjadreni pro tydny
     * 
     * @param string $status     minuly / budouci cas
     * @param int    $difference rozdil casu mezi zadanym a aktualnim
     */
    private static function weeks($status, $difference)
    {
        self::$finalString['status'] = (self::LAST_TIME === $status ? self::LAST_TIME : self::FUTURE_TIME);
        self::$finalString['quantity'] = (self::LAST_TIME === $status ? $difference : $difference);

        if (1 == $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "týdnem" : "týden");
            self::$finalString['quantity'] = (self::LAST_TIME === $status ? "" : "");
        }
        elseif (2 == $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "týdny" : "týden");
            self::$finalString['quantity'] = (self::LAST_TIME === $status ? $difference : "příští");
        }
        elseif (2 < $difference && 5 > $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "týdny" : "týdny");
        }
        elseif (5 <= $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "týdny" : "týdnů");
        }
    }

    /**
     * Sestaveni vyjadreni pro mesice
     * 
     * @param string $status     minuly / budouci cas
     * @param int    $difference rozdil casu mezi zadanym a aktualnim
     */
    private static function months($status, $difference)
    {
        self::$finalString['status'] = (self::LAST_TIME === $status ? self::LAST_TIME : self::FUTURE_TIME);
        self::$finalString['quantity'] = (self::LAST_TIME === $status ? $difference : $difference);

        if (1 == $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "měsicem" : "měsíc");
            self::$finalString['quantity'] = (self::LAST_TIME === $status ? "" : "");
        }
        elseif (12 == $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "rokem" : "rok");
            self::$finalString['quantity'] = (self::LAST_TIME === $status ? "" : "příští");
        }
        elseif (2 <= $difference && 5 > $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "měsíci" : "měsíce");
        }
        elseif (5 <= $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "měsíci" : "měsíců");
        }
    }

    /**
     * Sestaveni vyjadreni pro roky
     * 
     * @param string $status     minuly / budouci cas
     * @param int    $difference rozdil casu mezi zadanym a aktualnim
     */
    private static function years($status, $difference)
    {
        self::$finalString['status'] = (self::LAST_TIME === $status ? self::LAST_TIME : self::FUTURE_TIME);
        self::$finalString['quantity'] = (self::LAST_TIME === $status ? $difference : $difference);

        if (1 == $difference)
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "rokem" : "rok");
            self::$finalString['quantity'] = (self::LAST_TIME === $status ? "" : "");
        }
        else
        {
            self::$finalString['unit'] = (self::LAST_TIME === $status ? "roky" : "roky");
        }
    }

}
