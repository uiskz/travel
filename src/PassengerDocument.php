<?php
declare(strict_types=1);

namespace Uiskz\Travel;

use DateTimeInterface;

/**
 * Base passenger document class
 * @author Dmitriy Gritsenko <dg@uis.kz>
 * @package Uiskz\Travel
 * @version 1.0.0
 */
class PassengerDocument
{
    /**
     * National internal identity card
     */
    const int TYPE_NATIONAL_IDENTITY = 1;

    /**
     * National internal passport
     */
    const int TYPE_PASSPORT = 2;

    const int TYPE_BIRTH_CERTIFICATE = 3;

    const int TYPE_INTERNAL_PASSPORT = 4;

    const int TYPE_DIPLOMATIC_PASSPORT = 5;

    const int TYPE_PERMANENT_RESIDENCE = 6;

    /**
     * 2-letter ISO code of the country issuer
     * @var string
     */
    public string $countryCode2;

    /**
     * 3-letter ISO code of the country issuer
     * @var string
     */
    public string $countryCode3;

    /**
     * Document type based on constants available
     * @var int
     */
    public int $type;

    public string $number;

    /**
     * Document issue date
     * @var DateTimeInterface|null
     */
    public ?DateTimeInterface $issueDate = null;

    /**
     * Document expire date
     * @var DateTimeInterface|null
     */
    public ?DateTimeInterface $expireDate = null;
}