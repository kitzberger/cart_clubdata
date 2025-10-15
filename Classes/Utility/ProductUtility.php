<?php
namespace Extcode\CartClubdata\Utility;
/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
/**
 * Product Utility
 *
 * @package cart_simple_product
 * @author Daniel Lorenz <ext.cart.simple.product@extco.de>
 */
class ProductUtility
{
    /**
     * Object Manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;
    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;
    /**
     * @var array
     */
    protected $cartConf;
    /**
     * @var array
     */
    protected $CkClubdataConf;
    /**
     * Intitialize
     */
    public function __construct()
    {
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class
        );
        $this->configurationManager = $this->objectManager->get(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class
        );
        $this->cartConf = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'Cart'
        );
        $this->CkClubdataConf = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'CkClubdata'
        );
    }
    public function loadCartProductFromForeignDataStorage($params)
    {
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($params);

        $cartProductValues = $params['cartProductValues'];
        //print_r($cartProductValues);

        $productStorageId = $params['productStorageId'];
        $cartProduct = $params['cartProduct'];

        $taxClasses = $params['taxClasses'];
        if (intval($this->CkClubdataConf['settings']['cart']['productStorageId']) === $productStorageId) {
            $productStorageConf = $this->cartConf['productStorages'][$productStorageId];

            if ($productStorageConf['class'] == '\TYPO3\CkClubdata\Domain\Repository\ProgramRepository') {
                $productId = intval($cartProductValues['productId']);
                $productRepository = $this->objectManager->get(
                    \TYPO3\CkClubdata\Domain\Repository\ProgramRepository::class
                );
                /** @var \Extcode\CartSimpleProduct\Domain\Model\Product $productProduct */
                $productProduct = $productRepository->findByUid($productId);
                //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($productProduct);
                $stock = 0;
                $handleStock=false;

                if ($productProduct->getmaxTickets() > 0) {
                    $handleStock = true;
                    $stock = $productProduct->getmaxTickets() - $productProduct->getsoldTickets();
                    //$stock --;
                }


                $cartProduct = new \Extcode\Cart\Domain\Model\Cart\Product(
                    $productType = 'vitual',
                    $productProduct->getUid(),
                    $productStorageId,
                    null,
                    $productProduct->getUid(), //sku
                    $productProduct->getTitle().' '.$productProduct->getDatetime()->format('d.m.y H:i'),
                    $productProduct->getPriceB(),
                    $taxClasses[2],
                    $cartProductValues['quantity'],
                    $isNetPrice = false,
                    $feVariant = null
                );
                $cartProduct->setStock($stock);
                $cartProduct->setHandleStock($handleStock);

                //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($cartProduct);
            }
            $params['cartProduct'] = $cartProduct;
        }
        return [$params];
    }
}