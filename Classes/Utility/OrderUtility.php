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
/**
 * Address Service
 *
 * @author CK
 */
class OrderUtility
{
    /**
     * Object Manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;
    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;
    /**
     * Configuration Manager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;
    /**
     * Cart Repository
     *
     * @var \Extcode\Cart\Domain\Repository\CartRepository
     */
    protected $cartRepository;
    /**
     * Cart Settings
     *
     * @var array
     */
    protected $cartConf = [];

    /**
     * OrderUtility Item
     *
     * @var \Extcode\Cart\Domain\Model\Order\Item
     */
    protected $orderItem = null;
    /**
     * Cart
     *
     * @var \Extcode\Cart\Domain\Model\Cart\Cart
     */
    protected $cart = null;
    /**
     * CartFHash
     *
     * @var string
     */
    protected $cartFHash = '';

    /**
     * ProgramRepository
     *
     * @var \TYPO3\CkClubdata\Domain\Repository\ProgramRepository
     * @inject
     */

    protected $programRepository = NULL;

    public function injectProgramRepository(\TYPO3\CkClubdata\Domain\Repository\ProgramRepository $programRepository) {
        $this->pogramRepository = $programRepository;
    }
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
        $this->cartConf =
            $this->configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'Cart'
            );
        $this->uriBuilder = $this->objectManager->get(\TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder::class);

        $this->persistenceManager = $this->objectManager->get("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");

        $this->settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,'Cartclubdata');
    }

    /**
     * Handle Payment - Signal Slot Function
     *
     * @param array $params
     *
     * @return array
     */
    public function changeOrderItemBeforeSaving($params)
    {
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($params);
        /*$request = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_cart_cart');
        if ($request['billing_address_to_feuser']) $addrToFeUser = true;
        if ($addrToFeUser) {
            $params['orderItem']->getBillingAddress()->setAdditional('tofeuser');
        }*/

        foreach ($params['orderItem']->getProducts() as $product) {
            $program = $this->programRepository->findByUid($product->getSku());
            $sold = $program->getSoldTickets();
            $want = $product->getCount();
            $new = $sold;
            $init = $new;
            $new += $want;
            $init++; //startnummer Neues Ticket
            $counter = $this->settings['ticket']['numberPrefix'].sprintf($this->settings['ticket']['numberFormat'], $init); // Kartenzähler
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($counter);
            //exit;
            $program_date = $program->getDateTime()->format('ymdH');
            $ticket_number = $this->addEanCheck($program_date . $counter);
            $program->setsoldTickets($new);
            $product->setproductType($ticket_number);
            $this->programRepository->update($program);
        }

    }


    /**
     * CheckStock - Signal Slot Function
     *
     * @param array $params
     *
     * @return array
     */
    public function checkStock($params)
    {
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($params);
        $product = $params['cartProduct'];
        $product->setHandleStock(true);
        $program = $this->programRepository->findByUid($product->getSku());
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($program);
        $stock = 0;
        if ($program->getMaxTickets() > 0) {
            $stock = $program->getMaxTickets() - $program->getSoldTickets();
        }
        $want = $product->getQuantity();
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($sold);
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($stock);
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($want);
        if ($stock==0 OR $stock < $want) {
            $uri = $this->uriBuilder->reset()
               ->setTargetPageUid($this->cartConf['settings']['cart']['pid'])
                ->setArguments(array('tx_cart_cart[quantity_error]'=>$want,'tx_cart_cart[action]'=>'updateCart'))
                ->setCreateAbsoluteUri(true)
                ->build();
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($uri);
            //exit;
            //$this->redirect('updateCart');
            //\TYPO3\CMS\Core\Utility\HttpUtility::redirect($uri);
            /*$ok = false;
            \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($stock);
            $message = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'tx_cart.error.stock_handling.add',
                'cart'
            );
            $error = [
                'message' => $message,
                'severity' => \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            ];

            $errors[] = $error;
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($errors);
            //return $errors;*/
        }
        //$uri = $this->uriBuilder->reset()
        //         ->setTargetPageUid(53)
        //         ->setArguments(array('action'=>'updateCart','controller'=>'cart'))
        //         ->setCreateAbsoluteUri(true)
        //         ->build();
        //\TYPO3\CMS\Core\Utility\HttpUtility::redirect($uri);

        //$this->persistenceManager->persistAll();
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($product);
        return [$params];
    }

    private  function addEanCheck($code) {
        $key = 0;
        $mult = array( 1, 3 );

        for ( $i = 0; $i < strlen( $code ); $i++ )
            $key += substr( $code, $i, 1 ) * $mult[$i % 2];

        $key = 10 - ( $key % 10 );

        if ( $key == 10 )
            $key = 0;

        // in key steht die prüfziffer - an den code anhängen
        $code .= $key;
        return $code;
    }


}