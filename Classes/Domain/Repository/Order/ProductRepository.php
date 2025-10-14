<?php

namespace Extcode\CartClubdata\Domain\Repository\Order;

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
 * Order Product Repository
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class ProductRepository extends \Extcode\Cart\Domain\Repository\Order\ProductRepository
{
    public function findSku($uids) {
        //$uids = substr($uids, 0, -1);
        //$uids = explode(',', $uids);
        $query = $this->createQuery();
        $query->setOrderings(array('productType' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
        $and_constraints = array();
        $and_constraints[] = $query->in('sku',$uids);
        //foreach ($uids as $uid) $and_constraints[] = $query->equals('sku', $uid);
        $query->matching($query->logicalAnd($and_constraints));

        return $query->execute();
    }
}