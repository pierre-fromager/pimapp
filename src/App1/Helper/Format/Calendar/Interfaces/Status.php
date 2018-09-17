<?php
/**
 * Description of App1\Helper\Format\Calendar\Interfaces\Status
 *
 * @author pierrefromager
 */
namespace App1\Helper\Format\Calendar\Interfaces;

interface Status
{
    const CODE_WAITING = 1;
    const CODE_APPROVED = 2;
    const CODE_REFUSED = 3;
    const CODE_BILLED = 4;
    const LABEL_WAITING = 'En attente';
    const LABEL_APPROVED = 'Approuvé';
    const LABEL_REFUSED = 'Refusé';
    const LABEL_BILLED = 'Facturé';
}
