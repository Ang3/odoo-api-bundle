<?php

namespace Ang3\Bundle\OdooApiBundle\Helper;

use Ang3\Component\OdooApiClient\ExternalApiClient;

/**
 * @author Joanis ROUANET
 */
class InvoiceHelper
{
    /**
     * Helper model name.
     */
    const MODEL_NAME = 'account.invoice';

    /**
     * @var ExternalApiClient
     */
    private $client;

    /**
     * @param ExternalApiClient $client
     */
    public function __construct(ExternalApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Open an invoice.
     *
     * @param int $invoiceId
     */
    public function validate($invoiceId)
    {
        $this->client->call(self::MODEL_NAME, 'action_invoice_open', [$invoiceId]);
    }

    /**
     * Compute taxes so as to reload the total amount.
     *
     * @param int $invoiceId
     */
    public function computeTaxes($invoiceId)
    {
        $this->client->call(self::MODEL_NAME, 'compute_taxes', [$invoiceId]);
    }
}
