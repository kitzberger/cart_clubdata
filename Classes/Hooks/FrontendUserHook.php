<?php
namespace Extcode\CartClubdata\Hooks;
class FrontendUserHook
{
    /**
     * @param array &$parameters
     */
    public function showCartActionAfterCartWasLoaded(&$parameters, $refObj)
    {
        $billingAddress = $parameters['billingAddress'];
        $request = $parameters['request'];
        if ($billingAddress instanceof \Extcode\Cart\Domain\Model\Order\Address) {
            return;
        }
        if ($request && $request->getOriginalRequest() && $request->getOriginalRequest()->getArguments()) {
            return;
        }
        $feUserUid = (int)$GLOBALS['TSFE']->fe_user->user['uid'];
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class
        );
        $frontendUserRepository = $objectManager->get(
            \In2code\Femanager\Domain\Repository\UserRepository::class
        );
        $frontendUser = $frontendUserRepository->findByUid($feUserUid);
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($frontendUser);
        if ($frontendUser instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUser) {
            $billingAddress = $objectManager->get(
                \Extcode\Cart\Domain\Model\Order\Address::class
            );
            $billingAddress->setEmail($frontendUser->getEmail());
            $billingAddress->setTitle($frontendUser->getTitle());
            $billingAddress->setSalutation($frontendUser->getSalutation());
            $billingAddress->setFirstName($frontendUser->getFirstName());
            $billingAddress->setLastName($frontendUser->getLastName());
            $billingAddress->setCompany($frontendUser->getCompany());
            $billingAddress->setStreet($frontendUser->getAddress());
            $billingAddress->setZip($frontendUser->getZip());
            $billingAddress->setCity($frontendUser->getCity());
            $billingAddress->setPhone($frontendUser->getTelephone());
        }
        $parameters['billingAddress'] = $billingAddress;
    }
}