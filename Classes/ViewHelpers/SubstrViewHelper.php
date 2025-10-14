<?php
namespace Extcode\CartClubdata\ViewHelpers;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
class SubstrViewHelper extends AbstractViewHelper {
    /**
     * return string chunk
     *
     * @param $string string
     * @param $start string
     * @param $length string
     * @return string
     */
    public function render($string, $start, $length) {
        return substr($string, $start, $length);
    }
}
?>
