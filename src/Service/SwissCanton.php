<?php

namespace App\Service;

/**
 * Finds the canton of a Swiss postal code (NPA).
 *
 * Swiss NPAs are allocated by region, so ranges give the canton for the vast majority of
 * addresses (exact for Suisse romande, approximate at a few cantonal borders).
 * Good enough for a map on the admin dashboard; not meant for invoicing.
 */
final class SwissCanton
{
    /** Canton code => French name. */
    public const NAMES = [
        'AG' => 'Argovie', 'AI' => 'Appenzell Rh.-Int.', 'AR' => 'Appenzell Rh.-Ext.', 'BE' => 'Berne',
        'BL' => 'Bâle-Campagne', 'BS' => 'Bâle-Ville', 'FR' => 'Fribourg', 'GE' => 'Genève', 'GL' => 'Glaris',
        'GR' => 'Grisons', 'JU' => 'Jura', 'LU' => 'Lucerne', 'NE' => 'Neuchâtel', 'NW' => 'Nidwald',
        'OW' => 'Obwald', 'SG' => 'Saint-Gall', 'SH' => 'Schaffhouse', 'SO' => 'Soleure', 'SZ' => 'Schwyz',
        'TG' => 'Thurgovie', 'TI' => 'Tessin', 'UR' => 'Uri', 'VD' => 'Vaud', 'VS' => 'Valais', 'ZG' => 'Zoug',
        'ZH' => 'Zurich',
    ];

    /** [from, to, canton], checked in order: the more specific ranges come first. */
    private const RANGES = [
        [1200, 1299, 'GE'],
        [1630, 1699, 'FR'], [1700, 1799, 'FR'], [1470, 1489, 'FR'], [1530, 1599, 'FR'],
        [1860, 1899, 'VS'], [1900, 1999, 'VS'], [3900, 3999, 'VS'],
        [1000, 1199, 'VD'], [1300, 1469, 'VD'], [1490, 1529, 'VD'], [1600, 1629, 'VD'], [1800, 1859, 'VD'],
        [2000, 2199, 'NE'], [2300, 2399, 'NE'],
        [2340, 2364, 'JU'], [2800, 2999, 'JU'],
        [2200, 2299, 'NE'], [2400, 2799, 'BE'], [3000, 3899, 'BE'],
        [4000, 4059, 'BS'], [4100, 4499, 'BL'], [4500, 4699, 'SO'], [4700, 4799, 'SO'],
        [4800, 4999, 'AG'], [5000, 5799, 'AG'], [8900, 8999, 'AG'],
        [6000, 6299, 'LU'], [6300, 6349, 'ZG'], [6350, 6399, 'LU'], [6400, 6449, 'SZ'], [6450, 6499, 'UR'],
        [6060, 6078, 'OW'], [6360, 6389, 'NW'],
        [6500, 6999, 'TI'], [7000, 7799, 'GR'],
        [8000, 8199, 'ZH'], [8300, 8499, 'ZH'], [8600, 8699, 'ZH'], [8700, 8799, 'ZH'],
        [8200, 8299, 'SH'], [8500, 8599, 'TG'], [8750, 8799, 'GL'], [8800, 8899, 'SZ'],
        [9000, 9099, 'SG'], [9100, 9199, 'AR'], [9050, 9059, 'AI'], [9200, 9699, 'SG'], [9700, 9799, 'SG'],
    ];

    public static function fromPostalCode(?string $npa): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $npa);
        if (strlen($digits) !== 4) {
            return null;
        }
        $n = (int) $digits;
        // A few narrow ranges override the broad ones above.
        foreach ([[2340, 2364, 'JU'], [6060, 6078, 'OW'], [6360, 6389, 'NW'], [9050, 9059, 'AI'], [8750, 8799, 'GL']] as [$from, $to, $canton]) {
            if ($n >= $from && $n <= $to) {
                return $canton;
            }
        }
        foreach (self::RANGES as [$from, $to, $canton]) {
            if ($n >= $from && $n <= $to) {
                return $canton;
            }
        }

        return null;
    }
}
