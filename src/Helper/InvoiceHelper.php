<?php

namespace Ang3\Bundle\OdooApiBundle\Helper;

use Ang3\Component\Odoo\ExternalApiClient;

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

    public function __construct(ExternalApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Open an invoice.
     */
    public function validate(int $invoiceId): void
    {
        $this->client->call(self::MODEL_NAME, 'action_invoice_open', [$invoiceId]);
    }

    /**
     * Compute taxes so as to reload the total amount.
     */
    public function computeTaxes(int $invoiceId): void
    {
        $this->client->call(self::MODEL_NAME, 'compute_taxes', [$invoiceId]);
    }
}
