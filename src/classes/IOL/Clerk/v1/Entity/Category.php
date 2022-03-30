<?php

declare(strict_types=1);

namespace IOL\Clerk\v1\Entity;

use IOL\Generic\v1\DataSource\Database;
use IOL\Generic\v1\DataType\Date;
use IOL\Generic\v1\DataType\UUID;
use IOL\Generic\v1\Exceptions\InvalidValueException;
use IOL\Generic\v1\Exceptions\NotFoundException;

class Category
{
    public const DB_TABLE = 'categories';

    private int $id;
    private string $title;
    private ?string $description;

    private array $products = [];

    public function __construct(?int $id = null)
    {
        if (!is_null($id)) {
            if (!is_int($id)) {
                throw new InvalidValueException('Invalid Category-ID');
            }
            $this->loadData(Database::getRow('id', $id, self::DB_TABLE));
        }
    }

    private function loadData(array|false $values)
    {

        if (!$values || count($values) === 0) {
            throw new NotFoundException('Category could not be loaded');
        }

        $this->id = $values['id'];
        $this->title = $values['title'];
        $this->description = $values['description'];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

}
