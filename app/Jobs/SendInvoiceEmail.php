<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendInvoiceEmail implements ShouldQueue
{
    use Queueable;

    protected $invoice;
    protected $recipient;
    protected $cc;
    protected $subject;
    protected $body;
    protected $logId;

    /**
     * Create a new job instance.
     */
    public function __construct($invoice, $recipient, $cc, $subject, $body, $logId)
    {
        $this->invoice = $invoice;
        $this->recipient = $recipient;
        $this->cc = $cc;
        $this->subject = $subject;
        $this->body = $body;
        $this->logId = $logId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Load invoice with relationships for PDF
            $this->invoice->load(['party', 'challans.items', 'company']);
            $company = $this->invoice->company;
            $invoice = $this->invoice;

            // Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', compact('invoice', 'company'));
            $pdfContent = $pdf->output();

            // Send Email
            \Illuminate\Support\Facades\Mail::to($this->recipient)
                ->cc($this->cc)
                ->send(new \App\Mail\InvoiceMail($this->subject, $this->body, $pdfContent, "Invoice-{$this->invoice->invoice_number}.pdf"));

            // Update Log
            if ($this->logId) {
                \App\Models\InvoiceShareLog::where('id', $this->logId)->update(['status' => 'sent']);
            }

        } catch (\Exception $e) {
            if ($this->logId) {
                \App\Models\InvoiceShareLog::where('id', $this->logId)->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
            \Illuminate\Support\Facades\Log::error('Invoice Email Failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
