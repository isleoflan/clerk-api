<?php

    namespace IOL\Clerk\v1\Entity;

    use IOL\Generic\v1\DataSource\Database;
    use IOL\Generic\v1\DataType\Date;
    use IOL\Generic\v1\DataType\UUID;
    use IOL\Generic\v1\Exceptions\InvalidValueException;
    use IOL\Generic\v1\Exceptions\NotFoundException;

    class ProductMedium
    {
        public const DB_TABLE = 'product_media';
        public const BASE_URL = 'https://cdn.isleoflan.ch/shop/';

        private string $id;
        private Product $product;
        private string $type;
        private string $value;
        private int $sort;

        /**
         * @throws \IOL\Generic\v1\Exceptions\InvalidValueException
         */
        public function __construct(?string $id = null)
        {
            if (!is_null($id)) {
                if (!UUID::isValid($id)) {
                    throw new InvalidValueException('Invalid Product-Medium-ID');
                }
                $this->loadData(Database::getRow('id', $id, self::DB_TABLE));
            }
        }

        public function loadData(array|false $values)
        {

            if (!$values || count($values) === 0) {
                throw new NotFoundException('Product Medium could not be loaded');
            }

            $this->id = $values['id'];
            //$this->product = new Product($values['product_id']);
            $this->type = $values['medium_type'];
            $this->value = $values['medium_value'];
            $this->sort = $values['sort'];
        }

        public function getUrl(): string
        {
            return self::BASE_URL.$this->getValue();
        }

        /**
         * @return string
         */
        public function getId(): string
        {
            return $this->id;
        }


        /**
         * @return Product
         */
        public function getProduct(): Product
        {
            return $this->product;
        }


        /**
         * @return string
         */
        public function getType(): string
        {
            return $this->type;
        }


        /**
         * @return string
         */
        public function getValue(): string
        {
            return $this->value;
        }
        /**
         * @return int
         */
        public function getSort(): int
        {
            return $this->sort;
        }

    }
