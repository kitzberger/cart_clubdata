<?php
namespace Extcode\CartClubdata\ViewHelpers;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
class GetOfflineViewHelper extends AbstractViewHelper {

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
     * @param integer $uid
     * @return string
     * @api
     */
        public function render($uid) {
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class
        );
        $configurationManager = $objectManager->get(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class
        );
        $ClubConf = $configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'CkClubdata'

        );
           $program = $this->programRepository->findByUid($uid);
           //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($program);
           //exit;
        return $program->getOfflineTickets();
    }
}
?>
