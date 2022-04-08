<?php

    namespace IOL\Clerk\v1\Entity;

    use IOL\Generic\v1\DataSource\Database;
    use IOL\Generic\v1\DataType\Date;
    use IOL\Generic\v1\DataType\UUID;
    use IOL\Generic\v1\Exceptions\InvalidValueException;
    use IOL\Generic\v1\Exceptions\NotFoundException;
    use JetBrains\PhpStorm\ArrayShape;
    use JetBrains\PhpStorm\Pure;

    class Product
    {
        public const DB_TABLE = 'products';

        private string $id;
        private Category $category;
        private ?string $number;
        private string $title;
        private string $paymentTitle;
        private ?string $description;
        private ?string $paymentDescription;
        private int $price;
        private ?Date $showFrom;
        private ?Date $showUntil;
        private ?array $additionalData;
        private array $media = [];
        private int $sort;

        /**
         * @throws \IOL\Generic\v1\Exceptions\InvalidValueException
         */
        public function __construct(?string $id = null)
        {
            if (!is_null($id)) {
                if (!UUID::isValid($id)) {
                    throw new InvalidValueException('Invalid Product-ID');
                }
                $this->loadData(Database::getRow('id', $id, self::DB_TABLE));
            }
        }

        public function loadData(array|false $values)
        {

            if (!$values || count($values) === 0) {
                throw new NotFoundException('App could not be loaded');
            }

            $this->id = $values['id'];
            $this->category = new Category($values['category_id']);
            $this->number = $values['product_number'];
            $this->title = $values['title'];
            $this->paymentTitle = $values['payment_title'];
            $this->description = $values['description'];
            $this->paymentDescription = $values['payment_description'];
            $this->price = $values['price'];
            $this->showFrom = is_null($values['show_from']) ? null : new Date($values['show_from']);
            $this->showUntil = is_null($values['show_until']) ? null : new Date($values['show_until']);
            $this->additionalData = json_decode($values['additional_data'], true);
            $this->sort = $values['sort'];

            $this->loadMedia();
        }

        public function loadMedia(): void
        {
            $database = Database::getInstance();
            $database->where('product_id', $this->id);
            $database->orderBy('sort');
            $data = $database->get(ProductMedium::DB_TABLE, [0, 1]);
            foreach($data as $medium){
                $productMedium = new ProductMedium();
                $productMedium->loadData($medium);
                $this->media[] = $productMedium;
            }
        }

        #[Pure]
        public function getImages(): array
        {
            $return = [];
            /** @var ProductMedium $medium */
            foreach($this->media as $medium){
                if($medium->getType() === 'IMAGE') {
                    $return[] = $medium->getUrl();
                }
            }
            return $return;
        }

        public function getFirstImage(): string
        {
            $images = $this->getImages();
            if(count($images) > 0) {
                /** @var ProductMedium $image */
                $image = array_unshift($images);
                return $image->getUrl();
            }
            return '';
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
        public function getTitle(): string
        {
            return $this->title;
        }

        /**
         * @return string
         */
        public function getDescription(): string
        {
            return $this->description;
        }

        /**
         * @return int
         */
        public function getPrice(): int
        {
            return $this->price;
        }

        /**
         * @return array
         */
        public function getAdditionalData(): array
        {
            return $this->additionalData;
        }

        /**
         * @return int
         */
        public function getSort(): int
        {
            return $this->sort;
        }

        /**
         * @return string
         */
        public function getPaymentTitle(): string
        {
            return $this->paymentTitle;
        }

        /**
         * @return string|null
         */
        public function getPaymentDescription(): ?string
        {
            return $this->paymentDescription;
        }

        /**
         * @return Category
         */
        public function getCategory(): Category
        {
            return $this->category;
        }

        #[ArrayShape(['id' => "string", 'name' => "string", 'price' => "int", 'category' => "string", 'categoryId' => "int", 'image' => "string"])]
        #[Pure]
        public function serialize(): array
        {
            return [
                'id' => $this->id,
                'name' => $this->title,
                'price' => $this->price,
                'category' => $this->category->getTitle(),
                'categoryId' => $this->category->getId(),
                'image' => $this->getFirstImage()
            ];
        }


        public static function getAllClerkProducts() : array
        {
            $database = Database::getInstance();
            $database->where('category_id', [2,4,6,7,8,9], 'IN');
            $database->orderBy('sort', 'ASC');

            $return = [];

            foreach($database->get(self::DB_TABLE) as $productData) {
                $product = new Product();
                $product->loadData($productData);

                $return[] = $product->serialize();
            }
            return $return;
        }
    }
