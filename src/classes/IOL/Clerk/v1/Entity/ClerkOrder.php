<?php

declare(strict_types=1);

namespace IOL\Clerk\v1\Entity;

use IOL\Generic\v1\DataSource\Database;
use IOL\Generic\v1\DataType\Date;
use IOL\Generic\v1\DataType\UUID;
use IOL\Generic\v1\Exceptions\InvalidValueException;
use IOL\Generic\v1\Exceptions\NotFoundException;
use IOL\Generic\v1\Request\APIResponse;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class ClerkOrder
{
    public const DB_TABLE = 'clerk_orders';


    private string $id;
    private string $userId;
    private string $clerkUserId;
    private Date $created;

    private array $items = [];

    public function __construct(?string $id = null)
    {
        if (!is_null($id)) {
            if (!UUID::isValid($id)) {
                throw new InvalidValueException('Invalid Order ID');
            }
            $this->loadData(Database::getRow('id', $id, self::DB_TABLE));
        }
    }

    /**
     * @throws NotFoundException
     */
    private function loadData(array|false $values): void
    {

        if (!$values || count($values) === 0) {
            throw new NotFoundException('Order could not be loaded');
        }

        $this->id = $values['id'];
        $this->userId = $values['user_id'];
        $this->created = new Date($values['created']);
        $this->clerkUserId = $values['clerk_id'];
        $this->loadItems();
    }

    public function loadItems(): void
    {
        $database = Database::getInstance();
        $database->where('order_id', $this->id);
        $database->orderBy('sort');
        foreach($database->get(ClerkOrderItem::DB_TABLE) as $itemData){
            $item = new ClerkOrderItem();
            $item->loadData($itemData);
            $this->items[] = $item;
        }
    }

    public function createNew(string $userId, array $items, string $clerkId): int
    {
        $this->id = UUID::newId(self::DB_TABLE);
        $this->userId = $userId;
        $this->clerkUserId = $clerkId;
        $this->created = new Date('u');


        foreach($items as $sort => $item){
            $orderItem = new ClerkOrderItem();
            $orderItem->createNew($this->id, $item, $sort);

            $this->items[] = $orderItem;
        }

        $totalPrice = $this->getSubtotal();
        $userBalance = $this->getCardBalance($userId);

        if($totalPrice > $userBalance) {
            APIResponse::getInstance()->addData('balance', $userBalance);
            APIResponse::getInstance()->addData('minTopUp', ($totalPrice - $userBalance));
            APIResponse::getInstance()->setResponseCode(402);
            APIResponse::getInstance()->render();
        }

        $database = Database::getInstance();
        $database->insert(self::DB_TABLE, [
            'id' => $this->id,
            'user_id' => $this->userId,
            'clerk_id' => $this->clerkUserId,
            'created' => $this->created->format(Date::DATETIME_FORMAT_MICRO),
        ]);

        $topupId = '4c0cf233-4987-48fa-8282-65098bff4c3d';

        $realProducts = [];
        foreach($this->items as $key => $item){
            /** @var ClerkOrderItem $item */
            if ($item->getProduct()->getId() === $topupId) {
                $database->insert('transactions', [
                    'id' => UUID::newId('transactions'),
                    'value' => $item->getPrice() * -1,
                    'user_id' => $this->userId,
                    'time' => Date::now(Date::DATETIME_FORMAT_MICRO)
                ]);
            } else {
                $realProducts[] = $item;
                $database->insert('clerk_order_items', [
                    'id' => UUID::newId('clerk_order_items'),
                    'order_id' => $this->id,
                    'product_id' => $item->getProduct()->getId(),
                    'amount' => $item->getAmount(),
                    'sort' => $key,
                ]);
            }
        }

        $total = 0;
        /** @var ClerkOrderItem $itm */
        foreach($realProducts as $itm) {
            $total += $itm->getPrice();
        }

        $database->insert('transactions', [
            'id' => UUID::newId('transactions'),
            'value' => $total,
            'user_id' => $this->userId,
            'time' => Date::now(Date::DATETIME_FORMAT_MICRO)
        ]);

        return $this->getCardBalance($userId);
    }

    public function getSubtotal(): int
    {
        $topupId = '4c0cf233-4987-48fa-8282-65098bff4c3d';
        $total = 0;
        /** @var ClerkOrderItem $orderItem */
        foreach($this->items as $orderItem){
            if($orderItem->getProduct()->getId() === $topupId){
                $total -= $orderItem->getPrice();
            } else {
                $total += $orderItem->getPrice();
            }
        }
        return $total;
    }

    public function getCardBalance(string $userId): int
    {
        $database = Database::getInstance();
        $result = $database->query('SELECT SUM(value)*-1 AS balance FROM transactions WHERE user_id = "'.$userId.'"');

        return (int)$result[0]['balance'];
    }

    public function getUserFromBadge(string $badgeId): ?string
    {
        $database = Database::getInstance();
        $database->where('serial', $badgeId);
        $user = $database->get('cards');
        return $user[0]['user_id'] ?? null;
    }

}