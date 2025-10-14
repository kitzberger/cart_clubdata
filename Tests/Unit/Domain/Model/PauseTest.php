<?php
namespace Extcode\CartClubdata\Tests\Unit\Domain\Model;

/**
 * Test case.
 */
class PauseTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Extcode\CartClubdata\Domain\Model\Pause
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new \Extcode\CartClubdata\Domain\Model\Pause();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getTitleReturnsInitialValueForString()
    {
        self::assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleForStringSetsTitle()
    {
        $this->subject->setTitle('Conceived at T3CON10');

        self::assertAttributeEquals(
            'Conceived at T3CON10',
            'title',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getFromdateReturnsInitialValueForDateTime()
    {
        self::assertEquals(
            null,
            $this->subject->getFromdate()
        );
    }

    /**
     * @test
     */
    public function setFromdateForDateTimeSetsFromdate()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setFromdate($dateTimeFixture);

        self::assertAttributeEquals(
            $dateTimeFixture,
            'fromdate',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getTodateReturnsInitialValueForDateTime()
    {
        self::assertEquals(
            null,
            $this->subject->getTodate()
        );
    }

    /**
     * @test
     */
    public function setTodateForDateTimeSetsTodate()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setTodate($dateTimeFixture);

        self::assertAttributeEquals(
            $dateTimeFixture,
            'todate',
            $this->subject
        );
    }
}
