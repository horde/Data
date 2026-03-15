<?php

/**
 * Copyright 1999-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Jan Schneider <jan@horde.org>
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL
 * @package  Data
 */

/**
 * Abstract class to handle different kinds of Data formats and to
 * help data exchange between Horde applications and external sources.
 *
 * @author    Jan Schneider <jan@horde.org>
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @category  Horde
 * @copyright 1999-2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL
 * @package   Data
 */
class Horde_Data
{
    /**
     * Import already mapped csv data.
     */
    public const IMPORT_MAPPED = 1;

    /**
     * Map date and time entries of csv data.
     */
    public const IMPORT_DATETIME =  2;

    /**
     * Import generic CSV data.
     */
    public const IMPORT_CSV = 3;

    /**
     * Import MS Outlook data.
     */
    public const IMPORT_OUTLOOK = 4;

    /**
     * Import vCalendar/iCalendar data.
     */
    public const IMPORT_ICALENDAR = 5;

    /**
     * Import vCards.
     */
    public const IMPORT_VCARD = 6;

    /**
     * Import generic tsv data.
     */
    public const IMPORT_TSV = 7;

    /**
     * Import Mulberry address book data.
     */
    public const IMPORT_MULBERRY = 8;

    /**
     * Import Pine address book data.
     */
    public const IMPORT_PINE = 9;

    /**
     * Import file.
     */
    public const IMPORT_FILE = 11;

    /**
     * Import data.
     */
    public const IMPORT_DATA = 12;

    /**
     * Import URL.
     *
     * @since Horde_Data 2.1.0
     */
    public const IMPORT_URL = 13;


    /**
     * Export generic CSV data.
     */
    public const EXPORT_CSV = 100;

    /**
     * Export iCalendar data.
     */
    public const EXPORT_ICALENDAR = 101;

    /**
     * Export vCards.
     */
    public const EXPORT_VCARD = 102;

    /**
     * Export TSV data.
     */
    public const EXPORT_TSV = 103;

    /**
     * Export Outlook CSV data.
     */
    public const EXPORT_OUTLOOKCSV = 104;

}
