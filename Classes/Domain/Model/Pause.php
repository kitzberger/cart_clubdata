<?php
namespace Extcode\CartClubdata\Domain\Model;

/***
 *
 * This file is part of the "Cart Clubata" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019
 *
 ***/

/**
 * Pause
 */
class Pause extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * title
     *
     * @var string
     */
    protected $title = '';

    /**
     * fromdate
     *
     * @var \DateTime
     */
    protected $fromdate = null;

    /**
     * todate
     *
     * @var \DateTime
     */
    protected $todate = '';

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the fromdate
     *
     * @return \DateTime $fromdate
     */
    public function getFromdate()
    {
        return $this->fromdate;
    }

    /**
     * Sets the fromdate
     *
     * @param \DateTime $fromdate
     * @return void
     */
    public function setFromdate(\DateTime $fromdate)
    {
        $this->fromdate = $fromdate;
    }

    /**
     * Returns the todate
     *
     * @return \DateTime todate
     */
    public function getTodate()
    {
        return $this->todate;
    }

    /**
     * Sets the todate
     *
     * @param string $todate
     * @return void
     */
    public function setTodate($todate)
    {
        $this->todate = $todate;
    }
}
