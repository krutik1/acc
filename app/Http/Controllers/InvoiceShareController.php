<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceShareLog;
use App\Jobs\SendInvoiceEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceShareController extends Controller
{
    public function emailForm(Invoice $invoice)
    {
        if ($invoice->company_id != session('selected_company_id')) {
            abort(404);
        }
        $invoice->load('party');
        return view('invoices.share_email', compact('invoice'));
    }

    public function sendEmail(Request $request, Invoice $invoice)
    {
        if ($invoice->company_id != session('selected_company_id')) {
            abort(404);
        }

        $request->validate([
            'recipient' => 'required|email',
            'cc' => 'nullable|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        // Create Log
        $log = InvoiceShareLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => auth()->id(),
            'recipient' => $request->recipient,
            'channel' => 'email',
            'status' => 'pending',
        ]);

        // Dispatch Job
        SendInvoiceEmail::dispatch(
            $invoice,
            $request->recipient,
            $request->cc,
            $request->subject,
            $request->body,
            $log->id
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Email queued successfully.');
    }

    public function shareViaWhatsApp(Invoice $invoice)
    {
        if ($invoice->company_id != session('selected_company_id')) {
            abort(404);
        }

        $invoice->load('party');

        // Generate Signed URL for public access
        $url = URL::signedRoute('invoices.public.download', ['invoice' => $invoice->id]);

        $message = "Hello {$invoice->party->name},\n\n";
        $message .= "Please find your invoice {$invoice->invoice_number} from {$invoice->company->name}.\n";
        $message .= "Download here: {$url}";

        $encodedMessage = urlencode($message);

        $mobile = $invoice->party->phone; // Assuming phone field exists in Party model

        // Log the share attempt
        InvoiceShareLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => auth()->id(),
            'recipient' => $mobile ?? 'Unknown',
            'channel' => 'whatsapp',
            'status' => 'sent', // We assume sent as we redirect
        ]);

        $whatsappUrl = "https://wa.me/{$mobile}?text={$encodedMessage}";

        return redirect()->away($whatsappUrl);
    }

    public function downloadPublic(Request $request, Invoice $invoice)
    {
        if (!$request->hasValidSignature()) {
            abort(401);
        }

        $invoice->load(['party', 'challans.items', 'company']);
        $company = $invoice->company;

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'company'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }
}
