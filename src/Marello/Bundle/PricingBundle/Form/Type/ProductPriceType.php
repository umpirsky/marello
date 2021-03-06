<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductPriceType extends AbstractType
{
    const NAME = 'marello_product_price';

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('channel', 'genemu_jqueryselect2_entity', [
                'class' => 'MarelloSalesBundle:SalesChannel',
            ])
            ->add('currency', 'hidden', [
                'required' => true,
                'data'     => $this->localeSettings->getCurrency(),
            ])
            ->add('value', 'oro_money', [
                'required' => true,
                'label'    => 'marello.pricing.productprice.value.label',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $currencyCode   = $this->localeSettings->getCurrency();
        $currencySymbol = $this->localeSettings->getCurrencySymbolByCurrency($currencyCode);

        $resolver->setDefaults([
            'data_class'      => 'Marello\Bundle\PricingBundle\Entity\ProductPrice',
            'intention'       => 'productprice',
            'single_form'     => true,
            'currency'        => $currencyCode,
            'currency_symbol' => $currencySymbol,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['currency']        = $options['currency'];
        $view->vars['currency_symbol'] = $options['currency_symbol'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
