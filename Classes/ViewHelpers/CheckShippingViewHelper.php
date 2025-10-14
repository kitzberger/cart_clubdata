<?php
namespace Extcode\CartClubdata\ViewHelpers;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
class CheckShippingViewHelper extends AbstractViewHelper {

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
     * @param \Extcode\Cart\Domain\Model\Cart\Cart $cart
     * @return string
     * @api
     */
        public function render($cart) {
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class
        );
        $configurationManager = $objectManager->get(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class
        );
        $cartConf = $configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'Cart'
            );
        //$cart = $arguments['cart'];
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($cart);
        //exit;
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($cartConf);
        //if ($cart->getShipping()->getId()==2) {
            $deadline = $cartConf['settings']['shipDeadline'];
            $products = $cart->getProducts();
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($products);
            foreach ($products as $product) {
                $date = substr($product->getTitle(),-14);
                $date = substr($date,0,6).'20'.substr($date,6,2);
                //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($date);
                //$result = $this->pauseRepository->findPause(strtotime($date.' '.$deadline));
                $result = $this->pauseRepository->findPause(date('U'));
                //$result = $this->pauseRepository->findAll();
                if (count($result)) return $result[0]->getTitle();
                //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($date.' '.$deadline);
                //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(strtotime($date.' '.$deadline));
                //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($result);
               //exit;
                //if ($this->pauseRepository->findPause(strtotime($date.' '.$deadline))) return false;
                if (date('U') > strtotime($date.' '.$deadline)) return 'error';
            }
        //}
        return '';
    }
}
?>
