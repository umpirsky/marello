<?php

namespace Marello\Bundle\ProductBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Marello\Bundle\ProductBundle\Entity\Variant;
use Marello\Bundle\ProductBundle\Entity\Product;

class ProductVariantHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var Product
     */
    protected $parent;

    /**
     * @param FormInterface $form
     * @param Request       $request
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param Variant $entity
     * @param Product $parent
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(Variant $entity, Product $parent)
    {
        $this->setParentEntity($entity, $parent);
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->updateProducts($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * Returns form instance
     *
     * @return FormInterface
     */
    public function getFormView()
    {
        return $this->form->createView();
    }

    /**
     * "Success" form handler
     *
     * @param Variant $entity
     */
    protected function updateProducts(Variant $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Set the parent product to the Variant entity
     *
     * @param Variant $entity
     * @param Product $parent
     */
    public function setParentEntity(Variant $entity, Product $parent)
    {
        $entity->addProduct($parent);
    }
}
