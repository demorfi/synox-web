<?php declare(strict_types=1);

namespace App\Package\Search\Interfaces;

use DateTime;
use JsonSerializable;
use Stringable;

interface Item extends Stringable, JsonSerializable
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void;

    /**
     * @return ?string
     */
    public function getDate(): ?string;

    /**
     * @param int|DateTime $date
     * @return void
     */
    public function setDate(int|DateTime $date): void;

    /**
     * @return float
     */
    public function getSize(): float;

    /**
     * @return string
     */
    public function getWeight(): string;

    /**
     * @param string|float $size
     * @return void
     */
    public function setSize(string|float $size): void;

    /**
     * @return string
     */
    public function getFetchId(): string;

    /**
     * @param string $id
     * @return void
     */
    public function setFetchId(string $id): void;

    /**
     * @return string
     */
    public function getPageUrl(): string;

    /**
     * @param string $url
     * @return void
     */
    public function setPageUrl(string $url): void;
}