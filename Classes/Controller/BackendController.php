<?php

namespace Extcode\CartClubdata\Controller;

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
 * Backend Controller
 *
 * @package cart_simple_product
 * @author Daniel Lorenz <ext.cart.simple.product@extco.de>
 */
class BackendController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{


    /**
     * PauseRepository
     *
     * @var \Extcode\CartClubdata\Domain\Repository\PauseRepository
     * @inject
     */


    protected $pauseRepository = NULL;



    public function injectPauseRepository(\Extcode\CartClubdata\Domain\Repository\PauseRepository $pauseRepository) {
        $this->pauseRepository = $pauseRepository;
    }

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
     * @param \Extcode\Cart\Domain\Repository\Order\ItemRepository $itemRepository
     */

    protected $itemRepository = NULL;

    public function injectItemRepository(
        \Extcode\Cart\Domain\Repository\Order\ItemRepository $itemRepository
    ) {
        $this->itemRepository = $itemRepository;
    }

    /**
     * @param \Extcode\CartClubdata\Domain\Repository\Order\ProductRepository $productRepository
     */

    protected $productRepository = NULL;

    public function injectProductRepository(
        \Extcode\CartClubdata\Domain\Repository\Order\ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * Initialize Action
     *
     * @return void
     */
    protected function initializeAction()
    {
        $configurationManager = $this->objectManager->get(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::class
        );

        $cartConfiguration =
            $configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'Cart'
            );

        $this->settings['cart']['pid'] = $cartConfiguration['settings']['cart']['pid'];

    }


    public function interfaceAction()
    {


        $this->now = $this->settings['scanner']['showFrom'];
        $filter = array('disposed' => 1,
            'state_what'=>'not',
            'states' => '3,6'
        );
        $programs = $this->programRepository->findWithinMonth($filter, 0, 0, 1, $this->now);
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($programs);
        //exit;
        $export_programs = [];
        foreach ($programs as $program) {
            $title = $program->getTitle().' '.$program->getDatetime()->format('d.m.y H:i');
            $program->setTitle($title);
            $export_programs[]=$program;
        }



        $this->view->assign('ExportPrograms', $export_programs);
        //$this->view->assign('ExportUids', $uids_export);
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($filtered_programs_export);
        //exit;
        $usercheck = false;
        $denies = explode(',',$this->settings['refund']['denyGroups']);
        $groups = $GLOBALS['BE_USER']->user['usergroup'];
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->settings);
        //exit;
        foreach ($denies as $deny) {
            if (strpos($groups,$deny) !==false) {
                $usercheck = true;
            }
        }
        if (!$usercheck) {
            $this->now = $this->settings['refund']['showFrom'];
            $programs = $this->programRepository->findWithinMonth(array('disposed' => 1), 0, 0, 1, $this->now);
            $this->view->assign('RefundPrograms', $programs);
        }
        $options = array();
        $options[] = array(
            'id' => 'future',
            'title' => 'zukünftige'
        );
        $options[] = array(
            'id' => 'all',
            'title' => 'alle'
        );
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($options);
        //exit;
        $this->view->assign('Options', $options);
        }

    protected function filterData()
    {
        $programs = $this->programRepository->findWithinMonth(array(), 0, 0, 1, $this->now);
        $uids = array();

        foreach ($programs as $program) {
            $uids[$program->getDatetime()->format('U')]=$program->getUid();

        }
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($uids);
        //exit;
        $uids = array_unique($uids);
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($uids);
        //exit;

        $orders = $this->productRepository->findSku($uids);

            $filtered = array();
            $failed = array();
            foreach ($orders as $order) {
                if (!is_null($order->getItem())) {
                    $title = substr($order->getTitle(), 0, -15);
                    $date = array_search($order->getSku(), $uids);
                    $date = date('d.m.y H:i', $date);
                    $order->setAdditionalData($title . ' ' . $date);
                    $item = $order->getItem();
                    if ($item->getShipping()->getStatus() == 'shipped'
                        and $item->getPayment()->getStatus() == 'paid') { //achtung Producte unten werden geschrieben
                        $filtered[] = $order;
                    } else $failed[] = $order;
                }
            }

        $sort = [];
        foreach ($filtered as $object) {
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(date('Y-m-d H:i',strtotime(substr($object->getAdditionalData(),-14))));
            //exit;
            $sort[] = date('Y-m-d H:i',strtotime(substr($object->getAdditionalData(),-14))); //any object field
        }

        array_multisort($sort, SORT_ASC, $filtered);

        $values = array(
            'dates' => $uids,
            'orders' => $filtered
        );
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($values);
        exit;

        return $values;

    }


    public function ticketCheckAction()
    {
        $args = $this->request->getArguments();
        $what = $args['ticketCheck']['what'];
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($what);
        if ($what == 'all')  $programs = $this->programRepository->findWithinMonth(array(),0, 0, 0,'');
        else $programs = $this->programRepository->findWithinMonth(array(), 0, 0, 1,'now');

        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(count($programs));
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($programs);
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($programs[0]);
        //exit;

        $i=0;
        $rows = array();
        foreach ($programs as $program) {
            $uids = array();
            $uid = $program->getUid();
            $uids[] = $uid;
            $disposed = 0;
            $open = 0;
            $cancelled = 0;
            $pending = 0;
            $shipped = 0;
            $not_shipped = 0;

        //if ($i==3)  \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($program);
        $orders = $this->productRepository->findSku($uids);
        //if ($i==3)  \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($orders);
        //exit;


        foreach ($orders as $order) {
            if (!is_null($order->getItem())) {
                switch ($order->getItem()->getPayment()->getStatus()) {
                    case 'paid':
                        $disposed += $order->getCount();
                        break;
                    case 'open':
                        $open += $order->getCount();
                        break;
                    case 'canceled':
                        $cancelled += $order->getCount();
                        break;
                    case 'pending':
                        $pending += $order->getCount();
                        break;
                }
                switch ($order->getItem()->getShipping()->getStatus()) {
                    case 'shipped':
                        $shipped += $order->getCount();
                        break;
                    default:
                        $not_shipped += $order->getCount();
                        break;
                }
            }
        }
            if (count($orders)) {
                $mark = '';
                if ($disposed !=intval($program->getSoldTickets()-$program->getCancelledTickets())) $mark=' style="color: red;"';
                $i++;
                $rows[]=array(
                 'uid' => $uid ,
                 'mark' => $mark,
                 'title' => $order->getTitle(),
                 'disposed' => $disposed,
                 'open' => $open,
                 'cancelled' => $cancelled,
                 'pending' => $pending,
                 'shipped' => $shipped,
                 'not_shippeed' => $not_shipped,
                 'club_sold' => $program->getSoldTickets(),
                 'club_cancelled' => $program->getCancelledTickets(),
                 'club_max' => $program->getMaxTickets(),
                 'club_corrected' => intval($program->getSoldTickets()-$program->getCancelledTickets()),
                 'club_disposed' => $program->getDisposedTickets()
                );
            }
        }

        $this->view->assign('Rows', $rows);
    }
    public function ticketCheckDetailAction()
    {
        $uids = array();
        $uid = $this->request->getArgument('showUid');
        $uids[]=$uid;
        //$program = $this->programRepository->findUid($uid);
        $orders = $this->productRepository->findSku($uids);
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($orders);
        $disposed = 0;
        $open = 0;
        $cancelled = 0;
        $pending = 0;
        $shipped = 0;
        $not_shipped = 0;

        foreach ($orders as $order) {
            if (!is_null($order->getItem())) {
                switch ($order->getItem()->getPayment()->getStatus()) {
                    case 'paid':
                        $disposed += $order->getCount();
                        break;
                    case 'open':
                        $open += $order->getCount();
                        break;
                    case 'canceled':
                        $cancelled += $order->getCount();
                        break;
                    case 'pending':
                        $pending += $order->getCount();
                        break;
                }
                switch ($order->getItem()->getShipping()->getStatus()) {
                    case 'shipped':
                        $shipped += $order->getCount();
                        break;
                    default:
                        $not_shipped += $order->getCount();
                        break;
                }

            }
        }
        $this->view->assign('Orders', $orders);
        $this->view->assign('Disposed', $disposed);



    }

    public function ticketCheckWriteAction()
    {
        $args = $this->request->getArguments();
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($args);
        //exit;
        $values = array();
        $data = $args['ticketCheck'];
        foreach ($data as $key => $value) {
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($value);
            if ($value['disposed'] != $value['disposed-old'])
                $values[$key]=$value['disposed'];
        }
        //(\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($values);
        //exit;
        foreach ($values as $key => $value) {
            $program = $this->programRepository->findByUid($key);
            $program->setDisposedTickets($value);
            $this->programRepository->update($program);
        }
        //$this->redirect('ticketCheck');
        $this->redirect('interface');
    }
    /**
     * action ticketExport
     *
     * @return void
     */

    public function ticketExportAction()
    {
        //$format = $this->request->getFormat();
        $args = $this->request->getArguments();
        $format = $args['ticketExport']['format'];
        $uids = array();
        $tickets = array();
        $failed = array();
        $filtered_tickets = array();
        $numbers = array();
        foreach ($args['ticketExport']['program'] as $prog => $uid) $uids[] = $uid;



        $now = 'tomorrow - 1 second';
        $orders = $this->productRepository->findSku($uids);

        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($orders);
        //exit;
        if ($format == 'txt') {
            //$title = 'Scanner-Export-' . date('Y-m-d_H-i');
            $title = 'Scanner-Export-'.$orders[0]->getTitle();
            $filename = $title . '.' . $format;

            $this->response->setHeader('Content-Type', 'text/' . $format, true);
            $this->response->setHeader('Content-Description', 'File transfer', true);
            $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"', true);

            $this->view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
            $this->view->setTemplatePathAndFilename('fileadmin/templates/fluid/cart_clubdata/Templates/Backend/TicketExport.csv');
        }

        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($orders);
       //exit;

        foreach ($orders as $order) {
            if (!is_null($order->getItem())) {
            if (
                //$product->getSku() == $order->getSku()
                //AND $order->getItem()->getOrderNumber() == $cart_order->getOrderNumber()
                $order->getItem()->getShipping()->getStatus() == 'shipped'
                and $order->getItem()->getPayment()->getStatus() == 'paid') {
                if ($order->getCount() > 1) {
                    $ticket_number = substr($order->getProductType(), 0, -1);
                    $tickets[] = $order;
                    for ($i = 1; $i < $order->getCount(); $i++) {
                        $code = $this->addEanCheck($ticket_number += 1);
                        $orderProduct = $this->objectManager->get(
                            \Extcode\CartClubData\Domain\Model\Order\Product::class,
                            $order->getSku(),
                            $order->getTitle(),
                            $order->getCount()
                        );
                        $orderProduct->setProductType($code);
                        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($orderProduct);
                        $tickets[] = $orderProduct;
                    }
                } else $tickets[] = $order; // only 1 ticket

            }
            }
        }

        foreach ($tickets as $ticket) {
            // Filter deaktiviert wegegen Dupletten 05.11.19
            //if (!in_array($ticket->getProductType(), $numbers)) {
                //$numbers[] = $ticket->getProductType();
                $filtered_tickets[] = $ticket;
            //}
        }

        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($failed);
        //exit;
        $message = '';
        if (count($tickets) != count($filtered_tickets)) $message = "Achtung: doppelte Ticketnummern vorhanden";

        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->view->getTemplatePathAndFilename());
        if ($format != 'txt') { // Druckausgabe
            $names = [];
            foreach ($filtered_tickets as $object) {
                if ($object->getItem()) $names[] = $object->getItem()->getBillingAddress()->getLastName(); //any object field
            }

            array_multisort($names, SORT_ASC, $filtered_tickets);

            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($failed);
            //exit;
            $fehler = 0;
            foreach ($failed as $order) {
                if (!is_null($order->getItem())) {
                    if (
                        $order->getItem()->getShipping()->getStatus() != 'shipped'
                        or $order->getItem()->getPayment()->getStatus() != 'paid') {
                        $fehler += $order->getCount();
                    }
                }
            }
        }
        $this->view->assign('Orders', $filtered_tickets);
        $this->view->assign('Message', $message);
        $this->view->assign('Fehler', $fehler);
        //$this->redirect('ticketExport', NULL, NULL, array('format' => 'html', 'message' => $message));
    }


    public function refundCheckOrdersAction()
    {
        $args = $this->request->getArguments();
        $sku = $args['refundOrders']['program'];
        if ($sku) {
            $filtered_orders = [];
            $filter = array(
                'orderDateStart' => '2022-01-01',
                'paymentStatus' => 'paid'
            );
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($filter);
            //exit;
            $orders = $this->itemRepository->findAll($filter);
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($orders);
            //exit;
            foreach ($orders as $order) {

                foreach ($order->getProducts() as $product) {
                    if ($product->getSku()==$sku) {
                        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($order);
                        if ($order->getPayment()->getStatus()=='paid') $filtered_orders[] = $order;
                    }
                }
            }
            $this->view->assign('CountOrders', count($filtered_orders));
            $this->view->assign('Sku', $sku);
        }

    }

    public function refundOrdersAction()
    {
        $args = $this->request->getArguments();
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($args);
        $sku = $args['refundOrders']['program']; // find expects list
        if ($sku) {
            $filtered_orders = [];
            $filter = array(
                'orderDateStart' => '2022-01-01',
                'paymentStatus' => 'paid'
            );
            $orders = $this->itemRepository->findAll($filter);
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($orders[0]);
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(count($orders));
            foreach ($orders as $order) {
                foreach ($order->getProducts() as $product) {
                    if ($product->getSku()==$sku) {
                       // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($order);
                        if ($order->getPayment()->getStatus()=='paid') $filtered_orders[] = $order;
                    }
                }
            }
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($filtered_orders);
            foreach ($filtered_orders as $order) {
                $this->handleRefund($order,$sku);
            }

        }

    }

    public function refundOrderAction(
        \Extcode\Cart\Domain\Model\Order\Item $orderItem) {
       if ($orderItem->getPayment()->getStatus() == 'paid') $this->handleRefund($orderItem);
    }

    /**
     * Handle Refund
     *
     * @param \Extcode\Cart\Domain\Model\Order\Item $orderItem
     *
     * @return bool
     */
    public function handleRefund(
        \Extcode\Cart\Domain\Model\Order\Item $orderItem,
         $sku=''
    ) {
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($orderItem);
        $payment = $orderItem->getPayment();
        $provider = $payment->getProvider();

        $data = [
            'orderItem' => $orderItem,
            'provider' => $provider,
            'providerUsed' => false,
            'sku' => $sku
        ];

        $signalSlotDispatcher = $this->objectManager->get(
            \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
        );
        $params = $signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            [$data]
        );
    }


    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {

        $orders = $this->productRepository->findAll();
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($products);
        //exit;

        $this->view->assign('Orders', $orders);
    }

    /**
     * action show
     *
     * @param \Extcode\CartSimpleProduct\Domain\Model\Product $product
     *
     * @ignorevalidation $product
     *
     * @return void
     */
    public function showAction(\Extcode\CartSimpleProduct\Domain\Model\Product $product = null)
    {
        if (empty($product)) {
            $this->forward('list');
        }

        $this->view->assign('product', $product);
    }

    protected function addEanCheck($code)
    {
        $key = 0;
        $mult = array(1, 3);

        for ($i = 0; $i < strlen($code); $i++)
            $key += substr($code, $i, 1) * $mult[$i % 2];

        $key = 10 - ($key % 10);

        if ($key == 10)
            $key = 0;

        // in key steht die prüfziffer - an den code anhängen
        $code .= $key;
        return $code;
    }

}
