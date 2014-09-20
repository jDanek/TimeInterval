<?php

namespace Jdanek\Utils;

/**
 * Trida pro zpracovani casovych intervalu na slovni podobu
 *
 * Pouziti: TimeInterval::toString($timestamp);
 *
 * @author jDanek <jdanek.eu>
 */
class TimeInterval
{
    const PAST = 'past', FUTURE = 'future';
    const SECOND = 'second', MINUTE = 'minute', HOUR = 'hour', DAY = 'day', WEEK = 'week', MONTH = 'month', YEAR = 'year';

    /** @var array */
    private $config = array(
        'units'  => array(self::SECOND, self::MINUTE, self::HOUR, self::DAY, self::WEEK, self::MONTH, self::YEAR),
        'length' => array(60, 60, 24, 7, 4.35, 12),
    );

    /** @var array */
    public $trans = array(
        'cs' => array(
            self::SECOND => array(
                'singular' => array(1 => 'sekunda', 4 => 'sekundu', 7 => 'sekundou'),
                'plural'   => array(2 => 'sekund', 4 => 'sekundy', 7 => 'sekundami'),
            ),
            self::MINUTE => array(
                'singular' => array(1 => 'minuta', 4 => 'minutu', 7 => 'minutou'),
                'plural'   => array(2 => 'minut', 4 => 'minuty', 7 => 'minutami'),
            ),
            self::HOUR   => array(
                'singular' => array(1 => 'hodina', 4 => 'hodinu', 7 => 'hodinou'),
                'plural'   => array(2 => 'hodin', 4 => 'hodiny', 7 => 'hodinami'),
            ),
            self::DAY    => array(
                'singular' => array(1 => 'den', 4 => 'den', 7 => 'dnem'),
                'plural'   => array(2 => 'dnů', 4 => 'dny', 7 => 'dny'),
            ),
            self::WEEK   => array(
                'singular' => array(1 => 'týden', 4 => 'týden', 7 => 'týdnem'),
                'plural'   => array(2 => 'týdnů', 4 => 'týdny', 7 => 'týdny'),
            ),
            self::MONTH  => array(
                'singular' => array(1 => 'měsíc', 4 => 'měsíc', 7 => 'měsícem'),
                'plural'   => array(2 => 'měsíců', 4 => 'měsíce', 7 => 'měsíci'),
            ),
            self::YEAR   => array(
                'singular' => array(1 => 'rok', 4 => 'rok', 7 => 'rokem'),
                'plural'   => array(2 => 'roků', 4 => 'roky', 7 => 'roky'),
            ),
            'time'       => array(
                self::PAST   => array(
                    'preposition' => 'před', // en help: "before"
                    'day'         => 'včera', // en help: "yesterday"
                    'adjectives'  => 'minulý', // en help: "last"
                ),
                self::FUTURE => array(
                    'preposition' => 'za', // en help: "after"
                    'day'         => 'zítra', // en help: "tomorrow"
                    'adjectives'  => 'příští', // en help: "next"
                ),
            ),
        ),
    );

    /** @var string */
    private $lang = 'cs';

    /** @var string */
    private $status;

    /** @var int */
    private $difference;

    /** @var int|string */
    private $quantity;

    /** @var string */
    private $unit;

    /**
     * Kontruktor
     *
     * @param int    $timestamp
     * @param string $lang
     * @throws \InvalidArgumentException
     */
    public function __construct($timestamp, $lang = 'cs')
    {
        if (!is_numeric($timestamp))
        {
            throw new \InvalidArgumentException("Expected timestamp");
        }

        // nastaveni lokalizace
        $this->setLang($lang);

        // vypocet rozdilu casu
        $time = time();
        $this->status = ($time > $timestamp ? self::PAST : self::FUTURE);
        $this->difference = ($time > $timestamp ? ($time - $timestamp) : ($timestamp - $time));

        // ziskani jednotky
        $counter = count($this->config['length']) - 1;
        for ($i = 0; $this->difference >= $this->config['length'][$i] && $i < $counter; $i++)
        {
            $this->difference = $this->difference / $this->config['length'][$i];
        }

        // priprava pro zpracovani
        $this->quantity = round($this->difference);
        $this->unit = $this->config['units'][$i];
    }

    /**
     * [STATIC] Ziskani zpracovaneho timestampu
     *
     * @param int    $timestamp
     * @param string $lang
     * @return \self
     */
    public static function toString($timestamp, $lang = 'cs')
    {
        $instance = new self($timestamp, $lang);
        return $instance->process();
    }

    /**
     * Pridani prekladu
     *
     * @example units: second, minute, hour, day, week, month, year
     * @example array(
     *      'en'=>array(
     *              'second'=>array(
     *                  'singular'=>array(1=>'second', ...),
     *                  'plural'=>'array(2=>'seconds', ...),
     *              ),
     *              ...
     *            ));
     * @param array $trans
     */
    public function addTranslate(array $trans)
    {
        $this->trans = array_merge($this->trans, $trans);
    }

    /**
     * Nastaveni langcode pouziteho prekladu (ex.: CS / EN / DE ...)
     *
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = mb_strtolower($lang);
    }

    /**
     * Zpracovani timestampu na slovni vyjadreni
     */
    public function process()
    {
        // kontrola lokalizace
        if (!isset($this->trans[$this->lang]))
        {
            $this->setLang('cs');
        }

        // priprava
        $result = array('s' => $this->status, 'q' => $this->quantity, 'u' => '');

        // rozhodovaci struktura
        if (1 == $this->quantity)
        {
            $pad = (self::PAST === $this->status ? 7 : 4);
            $result['u'] = $this->trans[$this->lang][$this->unit]['singular'][$pad];

            switch ($this->unit) {
                case self::DAY:
                    $result['s'] = null;
                    $result['q'] = '';
                    $result['u'] = $this->trans[$this->lang]['time'][$this->status]['day'];
                    break;
                case self::WEEK:
                    $result['q'] = '';
                default:
                    break;
            }
        }
        elseif (2 == $this->difference)
        {
            $pad = (self::PAST === $this->status ? 7 : 4);
            $result['u'] = $this->trans[$this->lang][$this->unit]['plural'][$pad];

            switch ($this->unit) {
                case self::WEEK:
                    $result['s'] = null;
                    $result['q'] = $this->trans[$this->lang]['time'][$this->status]['adjectives'];
                    $result['u'] = $this->trans[$this->lang][$this->unit]['singular'][1];
                    break;

                default:
                    break;
            }
        }
        elseif (2 <= $this->quantity && 4 >= $this->quantity)
        {
            $pad = (self::PAST === $this->status ? 7 : 4);
            $result['u'] = $this->trans[$this->lang][$this->unit]['plural'][$pad];
        }
        elseif (12 == $this->quantity && self::MONTH === $this->unit)
        {
            $pad = (self::PAST === $this->status ? 7 : 1);

            $result['s'] = null;
            $result['q'] = $this->trans[$this->lang]['time'][$this->status]['preposition'];
            $result['u'] = $this->trans[$this->lang][self::YEAR]['singular'][$pad];
        }
        elseif (5 <= $this->quantity)
        {
            $pad = (self::PAST === $this->status ? 7 : 2);
            $result['u'] = $this->trans[$this->lang][$this->unit]['plural'][$pad];
        }

        // vystup
        $s = (null === $result['s'] ? '' : $this->trans[$this->lang]['time'][$result['s']]['preposition']);
        return "{$s} {$result['q']} {$result['u']}";
    }
}