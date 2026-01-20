<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $year = $request->input('year', now()->year);

        // Get monthly sales for the selected year
        $monthlySales = Invoice::where('company_id', $companyId)
            ->whereYear('invoice_date', $year)
            ->select(
                DB::raw('MONTH(invoice_date) as month'),
                DB::raw('SUM(final_amount) as total_sales'),
                DB::raw('COUNT(*) as invoice_count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Prepare data for chart (fill missing months with 0)
        $chartData = [
            'labels' => [],
            'data' => [],
        ];
        $detailedData = [];
        $totalSalesYear = 0;

        for ($m = 1; $m <= 12; $m++) {
            $monthName = Carbon::create()->month($m)->format('F');
            $chartData['labels'][] = $monthName;
            
            if (isset($monthlySales[$m])) {
                $amount = $monthlySales[$m]->total_sales;
                $count = $monthlySales[$m]->invoice_count;
            } else {
                $amount = 0;
                $count = 0;
            }

            $chartData['data'][] = $amount;
            $totalSalesYear += $amount;

            $detailedData[] = [
                'month' => $monthName,
                'sales' => $amount,
                'count' => $count,
            ];
        }

        $averageMonthlySales = $totalSalesYear / 12;

        // Get available years for filter
        $years = Invoice::where('company_id', $companyId)
            ->select(DB::raw('YEAR(invoice_date) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($years->isEmpty()) {
            $years = [now()->year];
        }

        return view('reports.index', compact(
            'chartData', 
            'detailedData', 
            'totalSalesYear', 
            'averageMonthlySales', 
            'year', 
            'years'
        ));
    }

    /**
     * Display the party ledger statement.
     */
    public function partyStatement(Request $request)
    {
        $companyId = $this->getCompanyId();
        
        // Default to current month if no dates provided
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->endOfMonth()->format('Y-m-d'));
        $partyId = $request->input('party_id');

        $parties = \App\Models\Party::where('company_id', $companyId)->get();
        
        $transactions = collect();
        $openingBalance = 0;
        $party = null;

        if ($partyId) {
            $party = \App\Models\Party::where('company_id', $companyId)->find($partyId);
            
            if ($party) {
                // Calculate Opening Balance
                
                $prevInvoices = Invoice::where('company_id', $companyId)
                    ->where('party_id', $partyId)
                    ->where('invoice_date', '<', $fromDate)
                    ->sum('final_amount');

                $prevPaymentsReceived = \App\Models\Payment::where('company_id', $companyId)
                    ->where('party_id', $partyId)
                    ->where('type', 'received')
                    ->where('payment_date', '<', $fromDate)
                    ->sum('amount');
                    
                $prevPaymentsSent = \App\Models\Payment::where('company_id', $companyId)
                    ->where('party_id', $partyId)
                    ->where('type', 'sent')
                    ->where('payment_date', '<', $fromDate)
                    ->sum('amount');

                // Opening Balance = (Invoices + Sent Payments) - (Received Payments)
                // Positive = Receivable (Dr), Negative = Payable (Cr)
                $openingBalance = ($prevInvoices + $prevPaymentsSent) - $prevPaymentsReceived;

                // Fetch Transactions within range
                $invoices = Invoice::where('company_id', $companyId)
                    ->where('party_id', $partyId)
                    ->whereBetween('invoice_date', [$fromDate, $toDate])
                    ->get()
                    ->map(function ($invoice) {
                        return [
                            'date' => $invoice->invoice_date,
                            'description' => 'Invoice #' . $invoice->invoice_number,
                            'type' => 'invoice',
                            'debit' => $invoice->final_amount,
                            'credit' => 0,
                            'url' => route('invoices.show', $invoice->id),
                            'ref_number' => $invoice->invoice_number
                        ];
                    });

                $payments = \App\Models\Payment::where('company_id', $companyId)
                    ->where('party_id', $partyId)
                    ->whereBetween('payment_date', [$fromDate, $toDate])
                    ->get()
                    ->map(function ($payment) {
                        $isReceived = $payment->type === 'received';
                        return [
                            'date' => $payment->payment_date,
                            'description' => 'Payment #' . $payment->payment_number . ($payment->reference_number ? ' (' . $payment->reference_number . ')' : ''),
                            'type' => 'payment',
                            'debit' => $isReceived ? 0 : $payment->amount, // Sent = Debit
                            'credit' => $isReceived ? $payment->amount : 0, // Received = Credit
                            'url' => route('payments.show', $payment->id),
                            'ref_number' => $payment->payment_number
                        ];
                    });

                // Merge and Sort
                $transactions = $invoices->concat($payments)->sortBy(function ($item) {
                    return $item['date']->format('Ymd') . $item['ref_number']; // Sort by date then ref number
                });
            }
        }

        return view('reports.party_statement', compact(
            'parties', 'party', 'transactions', 'openingBalance', 'fromDate', 'toDate'
        ));
    }

    /**
     * Display the GST Summary report (GSTR-1 style).
     */
    public function gstSummary(Request $request)
    {
        $companyId = $this->getCompanyId();
        $company = \App\Models\Company::find($companyId);
        
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->endOfMonth()->format('Y-m-d'));

        $parties = \App\Models\Party::where('company_id', $companyId)->orderBy('name')->get();
        $selectedPartyId = $request->input('party_id');

        $query = Invoice::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$fromDate, $toDate]);

        if ($selectedPartyId) {
            $query->where('party_id', $selectedPartyId);
        }

        $invoices = $query->with('party')
            ->orderBy('invoice_date')
            ->orderBy('invoice_number')
            ->get()
            ->map(function ($invoice) use ($company) {
                // Determine State Codes
                $companyStateCode = $company->state_code ? substr($company->state_code, 0, 2) : '24'; // Default GJ
                $partyGst = $invoice->party->gst_number;
                $partyStateCode = $partyGst ? substr($partyGst, 0, 2) : $companyStateCode; // Assume local if no GST

                // Calculation
                $taxableValue = $invoice->subtotal - $invoice->discount_amount;
                // Invoice->gst_amount contains Total Tax Amount
                $taxAmount = $invoice->gst_amount;
                
                $cgst = 0;
                $sgst = 0;
                $igst = 0;

                // Force CGST/SGST as per user request (ignoring state code difference)
                $cgst = $taxAmount / 2;
                $sgst = $taxAmount / 2;
                $igst = 0;

                // Diff / Rounding
                // Logic: Final - (Taxable + Tax)
                // User Request: Round Off Net Amount and put remainder in Diff
                $netAmount = round($invoice->final_amount);
                $calculatedTotal = $taxableValue + $cgst + $sgst + $igst;
                $diff = $netAmount - $calculatedTotal;

                return (object) [
                    'id' => $invoice->id,
                    'bill_no' => $invoice->invoice_number,
                    'date' => $invoice->invoice_date,
                    'party_name' => $invoice->party->name,
                    'gstin' => $partyGst,
                    'state_code' => $partyStateCode,
                    'taxable_value' => $taxableValue,
                    'cgst' => $cgst,
                    'sgst' => $sgst,
                    'igst' => $igst,
                    'diff' => $diff,
                    'net_amount' => $netAmount,
                ];
            });

        return view('reports.gst_summary', compact('company', 'invoices', 'fromDate', 'toDate', 'parties', 'selectedPartyId'));
    }

    /**
     * Display Monthly Salary Report.
     */
    public function salaryReport(Request $request)
    {
        $companyId = $this->getCompanyId();
        $month = $request->input('month', now()->format('Y-m'));
        
        $salaries = \App\Models\MonthlySalary::with('user')
            ->whereHas('user', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->where('month', $month)
            ->get();
            
        return view('reports.salary_report', compact('salaries', 'month'));
    }

    /**
     * Display Upaad Report.
     */
    public function upaadReport(Request $request)
    {
        $companyId = $this->getCompanyId();
        $month = $request->input('month', now()->format('Y-m'));
        
        $upaadsQuery = \App\Models\Upaad::with('user')
            ->whereHas('user', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->where('date', 'like', "$month%");

        if ($request->filled('user_id')) {
            $upaadsQuery->where('user_id', $request->user_id);
        }

        $upaads = $upaadsQuery->orderBy('date')->get();
            
        // Group by Employee for summary
        $employeeSummary = $upaads->groupBy('user_id')->map(function ($items) {
            return [
                'user' => $items->first()->user,
                'total' => $items->sum('amount'),
                'count' => $items->count(),
            ];
        });

        $employees = \App\Models\User::where('company_id', $companyId)
            ->where('role', 'employee')
            ->orderBy('name')
            ->get();
            
        return view('reports.upaad_report', compact('upaads', 'employeeSummary', 'month', 'employees'));
    }

    /**
     * Display Employee Statement.
     */
    public function employeeStatement(Request $request)
    {
        $companyId = $this->getCompanyId();
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->endOfMonth()->format('Y-m-d'));
        $userId = $request->input('user_id');
        
        $employees = \App\Models\User::where('company_id', $companyId)
            ->where('role', 'employee') 
            ->orderBy('name')
            ->get();
            
        $transactions = collect();
        $openingBalance = 0;
        $user = null;
        
        if ($userId) {
            $user = \App\Models\User::find($userId);
            
            if ($user && $user->company_id == $companyId) {
                // Approximate Opening Balance: Sum of Pending Salaries prior to fromDate? 
                // Or purely based on Cr/Dr logic.
                // Let's rely on "Pending Payable Amounts" as Opening Liability.
                // Liability = Credit. 
                
                // Opening Liability = (Sum of Payable Amount of Salaries where status=pending AND month < fromDate)
                // This assumes Upaads are already deducted from Payable.
                // But Upaads taken BEFORE fromDate that are NOT yet settled in a salary? 
                // This is getting tricky. 
                // Let's stick to the user's request: "Opening balance, Total earnings, Total Upaad, Closing balance".
                // This sounds like a period summary, not a running ledger.
                // But "Opening Balance" usually implies running ledger.
                // Let's define Opening Balance = 0 for simplicity if no better logic, OR
                // Opening Balance = Sum of Unpaid Salaries (Payable Amount) before From Date.
                
                $openingPendingSalaries = \App\Models\MonthlySalary::where('user_id', $userId)
                    ->where('month', '<', substr($fromDate, 0, 7)) // Rough month comparison
                    ->where('status', 'pending')
                    ->sum('payable_amount');
                    
                $openingBalance = $openingPendingSalaries; // Amount user is OWED by company.
                
                // Transactions in period
                
                // 1. Salaries Generated (Earnings)
                // We treat "Gross Earnings" as Credit to Employee? And Upaad as Debit?
                // Or "Net Payable" as Credit?
                // Request: "Total earnings, Total Upaad".
                // Let's list Salaries generated in this period.
                $salaries = \App\Models\MonthlySalary::where('user_id', $userId)
                    ->whereBetween('month', [substr($fromDate, 0, 7), substr($toDate, 0, 7)])
                    ->get();
                    
                foreach ($salaries as $salary) {
                    $transactions->push([
                        'date' => \Carbon\Carbon::parse($salary->month)->endOfMonth(), // Date assumed as month end
                        'description' => 'Salary Generated (' . $salary->month . ')',
                        'earnings' => $salary->total_amount,
                        'upaad' => $salary->total_upaad,
                        'payable' => $salary->total_amount, // Use Total Amount (Gross) to Credit the ledger. Upaad deductions are separate Dr.
                        'type' => 'salary'
                    ]);
                }
                
                // 2. Upaads Taken (Advance) - separate from Salary deduction?
                // The Upaads listed in Salary are DEDUCTIONS.
                // The Upaads in `upaads` table are Actual Money Taken.
                // If I take 500 upaad on 5th. 
                // My account: Dr 500.
                // Salary at end of month: Cr 10000.
                // Deduction: 500.
                // Net Cr: 9500.
                // So Net Payable matches.
                // We should list Upaads as "Money Taken" (Debit).
                // And Salary as "Money Earned" (Credit).
                
                $upaads = \App\Models\Upaad::where('user_id', $userId)
                    ->whereBetween('date', [$fromDate, $toDate])
                    ->get();
                    
                foreach ($upaads as $upaad) {
                    $transactions->push([
                        'date' => $upaad->date,
                        'description' => 'Advance (Upaad) - ' . $upaad->remarks,
                        'earnings' => 0,
                        'upaad' => $upaad->amount, // Amount taken
                        'payable' =>  -$upaad->amount, // Reduces net balance
                        'type' => 'upaad'
                    ]);
                }
                
                // 3. Payments (Salary Paid)
                // If Salary is "Paid", it means money went OUT to employee.
                // Dr Employee (Money received by employee).
                // Wait, if Salary Generated = Cr (Liability to Company).
                // Paid = Dr (Liability Reduced).
                
                foreach ($salaries as $salary) {
                    if ($salary->status == 'paid' && $salary->payment_date) {
                        // Check if payment date is in range
                        if ($salary->payment_date >= $fromDate && $salary->payment_date <= $toDate) {
                            $transactions->push([
                                'date' => $salary->payment_date,
                                'description' => 'Salary Paid (' . $salary->month . ')',
                                'earnings' => 0,
                                'upaad' => 0,
                                'payable' => -$salary->payable_amount, // Paid out
                                'paid' => $salary->payable_amount,
                                'type' => 'payment'
                            ]);
                        }
                    }
                }
                
                // Sort by date
                $transactions = $transactions->sortBy('date');
            }
        }
        
        return view('reports.employee_statement', compact('employees', 'user', 'transactions', 'openingBalance', 'fromDate', 'toDate'));
    }
}
