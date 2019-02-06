<?php
/**
 * This file is part of the Yasumi package.
 *
 * Copyright (c) 2015 - 2019 AzuyaLabs
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Sacha Telgenhof <me@sachatelgenhof.com>
 */

namespace Yasumi\Provider;

use DateInterval;
use DateTime;
use DateTimeZone;
use Yasumi\Holiday;

/**
 * Provider for all holidays in the USA.
 */
class Canada extends AbstractProvider
{
    use CommonHolidays, ChristianHolidays;

    /**
     * Code to identify this Holiday Provider. Typically this is the ISO3166 code corresponding to the respective
     * country or sub-region.
     */
    public const ID = 'CA';

    /**
     * Initialize holidays for the USA.
     *
     * @throws \Yasumi\Exception\InvalidDateException
     * @throws \InvalidArgumentException
     * @throws \Yasumi\Exception\UnknownLocaleException
     * @throws \Exception
     */
    public function initialize(): void
    {
        $this->timezone = 'America/Toronto';

        // Add common holidays
        $this->addHoliday($this->newYearsDay($this->year, $this->timezone, $this->locale));

        // Add Christian holidays
        $this->addHoliday($this->christmasDay($this->year, $this->timezone, $this->locale));

        // Calculate other holidays
        $this->calculateCanadaDay();
        $this->calculateLabourDay();
        $this->calculateVictoriaDay();
        $this->calculateRemembranceDay();
        $this->calculateThanksgivingDay();
        $this->calculateBoxingDay();

        $this->addHoliday($this->goodFriday($this->year, $this->timezone, $this->locale));
        $this->addHoliday($this->easterMonday($this->year, $this->timezone, $this->locale));

        $this->calculateSubstituteHolidays();
    }

    /**
     *
     * @link http://en.wikipedia.org/wiki/Martin_Luther_King,_Jr._Day
     *
     * @throws \Exception
     */
    private function calculateCanadaDay(): void
    {
        $this->addHoliday(new Holiday('canadaDay', [
            'en_US' => 'Canada Day',
        ], new DateTime("$this->year-7-01", new DateTimeZone($this->timezone)), $this->locale));
    }

    /**
     * Labour Day.
     *
     * Labor Day in the United States is a holiday celebrated on the first Monday in September. It is a celebration
     * of the American labor movement and is dedicated to the social and economic achievements of workers.
     *
     * @link http://en.wikipedia.org/wiki/Labor_Day
     *
     * @throws \Exception
     */
    private function calculateLabourDay(): void
    {
        if ($this->year >= 1887) {
            $this->addHoliday(new Holiday(
                'labourDay',
                [
                    'en_US' => 'Labour Day',
                ],
                new DateTime("first monday of september $this->year", new DateTimeZone($this->timezone)),
                $this->locale
            ));
        }
    }

    public function calculateVictoriaDay() {

    	$victoriaDay = new \DateTime("$this->year-05-24", new DateTimeZone($this->timezone));
	    if($victoriaDay->format('D') !== 'Mon'){
	        $victoriaDay->modify('previous monday');
	    }

    	$this->addHoliday(new Holiday(
            'victoriaDay',
            [
                'en_US' => 'Victoria Day',
            ], $victoriaDay,
            $this->locale
        ));
    }

	public function calculateRemembranceDay() {
		$this->addHoliday(new Holiday(
            'remembranceDay',
            [
                'en_US' => 'Remembrance Day',
            ],
            new DateTime("$this->year-11-11", new DateTimeZone($this->timezone)),
            $this->locale
        ));
	}
    
    public function calculateThanksgivingDay() {

        $this->addHoliday(new Holiday(
            'thanksgivingDay',
            [
                'en_US' => 'Thanksgiving Day',
            ],
            new DateTime("second monday of october $this->year", new DateTimeZone($this->timezone)),
            $this->locale
        ));
    }

    public function calculateBoxingDay() {
    	$this->addHoliday(new Holiday(
            'boxingDay',
            [
                'en_US' => 'Boxing Day',
            ],
            new DateTime("$this->year-12-26", new DateTimeZone($this->timezone)),
            $this->locale
        ));
    }

    /**
     * Calculate substitute holidays.
     *
     * When New Year's Day, Independence Day, or Christmas Day falls on a Saturday, the previous day is also a holiday.
     * When one of these holidays fall on a Sunday, the next day is also a holiday.
     *
     * @throws \Yasumi\Exception\InvalidDateException
     * @throws \InvalidArgumentException
     * @throws \Yasumi\Exception\UnknownLocaleException
     * @throws \Exception
     */
    private function calculateSubstituteHolidays(): void
    {
        $datesIterator     = $this->getIterator();
        $substituteHoliday = null;

        // Loop through all defined holidays
        while ($datesIterator->valid()) {

            // Only process New Year's Day, Independence Day, or Christmas Day
            if (\in_array(
                $datesIterator->current()->shortName,
                ['newYearsDay', 'christmasDay'],
                true
            )) {

                // Substitute holiday is on a Monday in case the holiday falls on a Sunday
                if (0 === (int)$datesIterator->current()->format('w')) {
                    $substituteHoliday = clone $datesIterator->current();
                    $substituteHoliday->add(new DateInterval('P1D'));
                }

                // Substitute holiday is on a Friday in case the holiday falls on a Saturday
                if (6 === (int)$datesIterator->current()->format('w')) {
                    $substituteHoliday = clone $datesIterator->current();
                    $substituteHoliday->sub(new DateInterval('P1D'));
                }

                // Add substitute holiday
                if (null !== $substituteHoliday) {
                    $this->addHoliday(new Holiday('substituteHoliday:' . $substituteHoliday->shortName, [
                        'en_US' => $substituteHoliday->getName() . ' observed',
                    ], $substituteHoliday, $this->locale));
                }
            }
            $datesIterator->next();
        }
    }
}
