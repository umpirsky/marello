<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Entity\Product;

class LoadProductData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var \Oro\Bundle\OrganizationBundle\Entity\Organization $defaultOrganization  */
    protected $defaultOrganization;

    /** @var \Marello\Bundle\InventoryBundle\Entity\Warehouse $defaultWarehouse */
    protected $defaultWarehouse;

    /** @var ObjectManager $manager */
    protected $manager;

    public function getDependencies()
    {
        return [
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductStatusData',
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSalesData'
        ];
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->defaultOrganization = $this->manager
            ->getRepository('OroOrganizationBundle:Organization')
            ->getOrganizationById(1);
        $this->defaultWarehouse = $this->manager
            ->getRepository('MarelloInventoryBundle:Warehouse')
            ->getDefault();

        $this->loadProducts();
    }

    /**
     * load products
     */
    public function loadProducts()
    {
        $handle = fopen($this->getDictionary('products.csv'), "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));

                $product = $this->createProduct($data);
                $this->setReference('marello-product-' . $i, $product);
                $i++;
            }
            fclose($handle);
        }
        $this->manager->flush();
    }

    /**
     * create new products and inventory items
     * @param array $data
     * @return Product $product
     */
    private function createProduct(array $data)
    {
        $product = new Product();
        $product->setSku($data['sku']);
        $product->setPrice($data['price']);
        $product->setName($data['name']);
        $inventoryItem = new InventoryItem();
        $inventoryItem->setProduct($product);
        $inventoryItem->setWarehouse($this->defaultWarehouse);
        $inventoryItem->setQuantity($data['stock_level']);
        $product->getInventoryItems()->add($inventoryItem);

        $status = $this->getReference('product_status_' . $data['status']);
        $product->setStatus($status);

        $channels = explode(';',$data['channel']);

        foreach ($channels as $channelId) {
            $channel = $this->getReference('marello_sales_channel_'. (int)$channelId);
            $product->addChannel($channel);
        }

        $this->manager->persist($product);

        return $product;
    }

    /**
     * Get dictionary file by name
     * @param $name
     * @return string
     */
    private function getDictionary($name)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
    }
}
