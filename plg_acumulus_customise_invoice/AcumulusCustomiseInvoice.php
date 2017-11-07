<?php
/**
 * @author      Buro RaDer i.o.v. SIEL - Acumulus, http://www.burorader.com/
 * @copyright   SIEL.
 * @license     GPLv3.
 */

use Siel\Acumulus\Api;
use Siel\Acumulus\Invoice\Result;
use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Meta;
use Siel\Acumulus\Tag;

/**
 * The AcumulusCustomiseInvoice plugin class contains plumbing and example code
 * to react to events triggered by the Acumulus plugin. These events allow you
 * to:
 * - Prevent sending an invoice to Acumulus.
 * - Customise the invoice before it is sent to Acumulus.
 * - Process the results of sending the invoice to Acumulus.
 *
 * Usage of this plugin:
 * You can use and modify this example plugin as you like:
 * - only implement the events you are going to use.
 * - add your own event handling in those handler methods.
 *
 * Documentation for the events:
 * The events defined by the Acumulus plugin:
 * 1) onAcumulusInvoiceCreated
 * 2) onAcumulusInvoiceSendBefore
 * 3) onAcumulusInvoiceSendAfter
 *
 * ad 1)
 * This event is triggered after the raw invoice has been created but before
 * it is "completed". The raw invoice contains all data from the original order
 * or refund needed to create an invoice in the Acumulus format. The raw
 * invoice needs to be completed before it can be sent. Completing includes:
 * - Flattening composed products or products with options.
 * - Determining vat rates for those lines that do not yet have one (mostly
 *   discount lines or other special lines like processing or payment costs).
 * - Correcting vat rates if they were based on dividing a vat amount (in
 *   cents) by a price (in cents).
 * - Splitting discount lines over multiple vat rates.
 * - Making prices ex vat more precise to prevent invoice amount differences.
 * - Converting non Euro currencies (future feature).
 *
 * So with this event you can make changes to the raw invoice based on your
 * specific situation. By returning false, you can prevent having the invoice
 * been sent to Acumulus. Normally you should prefer the 2nd event, where you
 * can assume that the invoice has been flattened and all fields are filled in
 * and have valid values.
 *
 * However, in some specific cases this event may be needed, e.g. setting or
 * correcting tax rates before the completor strategies are executed.
 *
 * ad 2)
 * This event is triggered just before the invoice will be sent to Acumulus.
 * You can make changes to the invoice or add warnings or errors to the Result
 * object.
 *
 * Typical use cases are:
 * - Template, account number, or cost center selection based on order
 *   specifics, e.g. in a multi-shop environment.
 * - Adding descriptive info to the invoice or invoice lines based on custom
 *   order meta data or data from not supported modules.
 * - Correcting payment info based on specific knowledge of your situation or
 *   on payment modules not supported by this module.
 *
 * ad 3)
 * This event is triggered after the invoice has been sent to Acumulus. The
 * Result object will tell you if there was an exception or if errors or
 * warnings were returned by the Acumulus API. On success, the entry id and
 * token for the newly created invoice in Acumulus are available, so you can
 * e.g. retrieve the pdf of the Acumulus invoice.
 *
 * External Resources:
 * - https://apidoc.sielsystems.nl/content/invoice-add.
 * - https://apidoc.sielsystems.nl/content/warning-error-and-status-response-section-most-api-calls
 */
class PlgAcumulusAcumulusCustomiseInvoice extends JPlugin
{
    /**
     * Event observer to react to the creation of the raw Acumulus invoice.
     *
     * @param array $invoice
     *   The invoice that has been created.
     * @param Source $invoiceSource
     *   The source object (order, credit note) for which the invoice was created.
     * @param \Siel\Acumulus\Invoice\Result $localResult
     *   Any locally generated messages.
     *
     * @return bool
     *   True to continue the completion and sending of the invoice, false to
     *   prevent sending it.
     */
    public function onAcumulusInvoiceCreated(array &$invoice, Source $invoiceSource, Result $localResult)
    {
	    // Here you can make changes to the raw invoice based on your specific
	    // situation, e.g. setting or correcting tax rates before the completor
	    // strategies execute.

	    // NOTE: the example below is now an option in the advanced settings:
	    // Prevent sending 0-amount invoices (free products/subscriptions).
	    if (empty($invoice) || $invoice['customer']['invoice'][Meta::InvoiceAmountInc] == 0) {
		    return false;
	    }

		// Change invoice here.

        return true;
    }

    /**
     * Event observer to react to the onAcumulusInvoiceSendBefore event.
     *
     * This event allows to prevent sending the invoice to Acumulus or alter it just before it is being sent to Acumulus
     *
     * @param array $invoice
     *   The invoice that has been created.
     * @param Source $invoiceSource
     *   The source object (order, credit note) for which the invoice was created.
     * @param \Siel\Acumulus\Invoice\Result $localResult
     *   Any locally generated messages.
     *
     * @return bool
     *   True to continue the completion and sending of the invoice, false to
     *   prevent sending it.
     */
    public function onAcumulusInvoiceSendBefore(array &$invoice, Source $invoiceSource, Result $localResult)
    {
	    // Here you can make changes to the raw invoice based on your specific
	    // situation, e.g. setting or correcting tax rates before the completor
	    // strategies execute.
	    // Here you can make changes to the invoice based on your specific
	    // situation, e.g. setting the payment status to its correct value:
	    if (!empty($invoice)) {
		    $invoice['customer']['invoice'][Tag::PaymentStatus] = $this->isOrderPaid($invoiceSource) ? Api::PaymentStatus_Paid : Api::PaymentStatus_Due;
	    }
        return true;
    }

    /**
     * Event observer to react to the onAcumulusInvoiceSendBefore event.
     *
     * This event allows to prevent sending the invoice to Acumulus or alter it just before it is being sent to Acumulus
     *
     * @param array $invoice
     *   The invoice that has been created.
     * @param Source $invoiceSource
     *   The source object (order, credit note) for which the invoice was created.
     * @param \Siel\Acumulus\Invoice\Result $result
     *   Any locally generated messages.
     *
     * @return bool
     *   True on success, false otherwise.
     */
    public function onAcumulusInvoiceSendAfter(array &$invoice, Source $invoiceSource, Result $result)
    {
	    if ($result->getException()) {
		    // Serious error:
		    if ($result->isSent()) {
			    // During sending.
		    }
		    else {
			    // Before sending.
		    }
	    }
	    elseif ($result->hasError()) {
		    // Invoice was sent to Acumulus but not created due to errors in the
		    // invoice.
	    }
	    else {
		    // Sent successfully, invoice has been created in Acumulus:
		    if ($result->getWarnings()) {
			    // With warnings.
		    }
		    else {
			    // Without warnings.
		    }
	    }
        return true;
    }

	/**
	 * Returns if the order has been paid or not.
	 *
	 * WooCommerce does not store any payment data, so determining the payment
	 * status is not really possible other then using order states. Therefore
	 * this is a valid example of a change you may want to make to the invoice
	 * before it is being send.
	 *
	 * Please fill in your own logic here in this method.
	 *
	 * @param \Siel\Acumulus\Invoice\Source $invoiceSource
	 *   Wrapper around the original WooCommerce order or refund for which the
	 *   invoice has been created.
	 *
	 * @return bool
	 *   True if the order has been paid, false otherwise.
	 */
	protected function isOrderPaid(Source $invoiceSource)
	{
		/**
		 * When you are using HikaShop, the order will be a \hikashopOrderClass
		 * object.
		 *
		 * @var \hikashopOrderClass $order
		 */
		/**
		 * When you are using VirtueMart, the order will be an array with keys:
		 * [details]
		 *   [BT]: stdClass (BillTo details)
		 *   [ST]: stdClass (ShipTo details) (if available, copy of BT otherwise)
		 * [history]
		 *   [0]: stdClass (virtuemart_order_histories table record)
		 *   ...
		 * [items]
		 *   [0]: stdClass (virtuemart_order_items table record)
		 *   ...
		 * [calc_rules]
		 *   [0]: stdClass (virtuemart_order_calc_rules table record)
		 *   ...
		 *
		 * @var array $order
		 */
		$order = $invoiceSource->getSource();
//		$this->>container->getLog()->info('PlgAcumulusCustomiseInvoice::isOrderPaid(): invoiceSource = ' . var_export($order, true));
		return true;
	}
}

$i = 3;
$j = 4;
