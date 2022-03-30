<?php

declare(strict_types=1);

namespace IOL\Clerk\v1\Entity;

use IOL\Generic\v1\DataSource\Database;
use IOL\Generic\v1\DataType\UUID;
use IOL\Generic\v1\Exceptions\InvalidValueException;
use IOL\Generic\v1\Exceptions\IOLException;
use IOL\Generic\v1\Exceptions\NotFoundException;
use IOL\Generic\v1\Request\APIResponse;
use JetBrains\PhpStorm\Pure;

class ClerkOrderItem
{
    public const DB_TABLE = 'order_items';

    private string $id;
    private string $orderId;
    private Product $product;
    private int $amount;
    private int $sort;

    public function __construct(?string $id = null)
    {
        if (!is_null($id)) {
            if (!UUID::isValid($id)) {
                throw new InvalidValueException('Invalid Order Item ID');
            }
            $this->loadData(Database::getRow('id', $id, self::DB_TABLE));
        }
    }

    public function loadData(array|false $values): void
    {

        if (!$values || count($values) === 0) {
            throw new NotFoundException('Order Item could not be loaded');
        }

        $this->id = $values['id'];
        $this->orderId = $values['order_id'];
        $this->product = new Product($values['product_id']);
        $this->amount = $values['amount'];
        $this->sort = $values['sort'];
    }

    public function createNew(string $orderId, array $item, int $sort)
    {
        try {
            $product = new Product($item['id']);
        } catch (IOLException $e) {
            APIResponse::getInstance()->addError(602001)->render();
        }

        $this->id = UUID::newId(self::DB_TABLE);
        $this->orderId = $orderId;
        $this->product = $product;
        $this->amount = (int)$item['qty'];
        $this->sort = $sort*10;

        $database = Database::getInstance();
        $database->insert(self::DB_TABLE, [
            'id'         => $this->id,
            'order_id'   => $this->orderId,
            'product_id' => $this->product->getId(),
            'amount'     => $this->amount,
            'sort'       => $this->sort
        ]);
    }

    #[Pure]
    public function getPrice(): int
    {
        return ($this->getProduct()->getPrice() * $this->getAmount());
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }



}