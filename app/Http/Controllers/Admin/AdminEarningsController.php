<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminEarningsController extends Controller
{
    private const EARNING_STATUSES = ['paid', 'completed'];

    public function index(Request $request)
    {
        $tz = config('app.timezone') ?? 'Asia/Manila';
        $today = Carbon::now($tz);

        $dateFrom = $this->sanitizeDate($request->query('date_from'), $today->copy()->startOfMonth()->toDateString());
        $dateTo = $this->sanitizeDate($request->query('date_to'), $today->toDateString());

        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $search = trim((string) $request->query('q', ''));
        $remittanceFilter = trim((string) $request->query('remittance', 'all'));
        $remittanceTableReady = Schema::hasTable('provider_remittances');

        $ledgerRows = $this->buildLedgerCollection($dateFrom, $dateTo);
        $providerOptions = $ledgerRows
            ->map(fn ($row) => (object) [
                'id' => (int) $row->provider_id,
                'name' => (string) $row->provider_name,
            ])
            ->unique('id')
            ->sortBy('name')
            ->values();

        $filteredRows = $ledgerRows->filter(function ($row) use ($search, $remittanceFilter) {
            $matchesSearch = $search === ''
                || str_contains(strtolower((string) $row->provider_name), strtolower($search));

            $matchesStatus = match ($remittanceFilter) {
                'remitted' => $row->is_remitted,
                'outstanding' => !$row->is_remitted,
                default => true,
            };

            return $matchesSearch && $matchesStatus;
        })->values();

        $summary = [
            'providers_count' => $filteredRows->pluck('provider_id')->unique()->count(),
            'ledger_count' => $filteredRows->count(),
            'gross_total' => round((float) $filteredRows->sum('gross_amount'), 2),
            'remitted_total' => round((float) $filteredRows->where('is_remitted', true)->sum('gross_amount'), 2),
            'outstanding_total' => round((float) $filteredRows->where('is_remitted', false)->sum('gross_amount'), 2),
        ];

        $sortedRows = $filteredRows->sort(function ($a, $b) {
            $dateCompare = strcmp((string) $b->remit_date, (string) $a->remit_date);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            return strcmp((string) $a->provider_name, (string) $b->provider_name);
        })->values();

        $perPage = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pageItems = $sortedRows->forPage($currentPage, $perPage)->values();

        $ledger = new LengthAwarePaginator(
            $pageItems,
            $sortedRows->count(),
            $perPage,
            $currentPage,
            [
                'path' => url()->current(),
                'query' => $request->query(),
            ]
        );

        return view('admin.earnings', compact(
            'ledger',
            'summary',
            'dateFrom',
            'dateTo',
            'search',
            'remittanceFilter',
            'remittanceTableReady',
            'providerOptions'
        ));
    }

    public function print(Request $request)
    {
        $tz = config('app.timezone') ?? 'Asia/Manila';
        $today = Carbon::now($tz);

        $period = in_array($request->query('period'), ['daily', 'monthly'], true)
            ? $request->query('period')
            : 'daily';

        $providerId = (int) $request->query('provider_id', 0);
        $selectedProvider = null;

        $providerOptions = $this->providerOptions();
        if ($providerId > 0) {
            $selectedProvider = $providerOptions->firstWhere('id', $providerId);
        }

        if ($period === 'monthly') {
            $selectedMonth = $this->sanitizeMonth($request->query('month'), $today->format('Y-m'));
            $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth, $tz)->startOfMonth()->toDateString();
            $monthEnd = Carbon::createFromFormat('Y-m', $selectedMonth, $tz)->endOfMonth()->toDateString();

            $rows = $this->buildLedgerCollection($monthStart, $monthEnd);

            if ($providerId > 0) {
                $rows = $rows->where('provider_id', $providerId)->values();
            }

            $printRows = $rows->groupBy('provider_id')->map(function ($group) {
                $first = $group->first();

                return (object) [
                    'provider_id' => (int) $first->provider_id,
                    'provider_name' => (string) $first->provider_name,
                    'provider_phone' => (string) $first->provider_phone,
                    'total_days' => $group->count(),
                    'remitted_days' => $group->where('is_remitted', true)->count(),
                    'outstanding_days' => $group->where('is_remitted', false)->count(),
                    'total_bookings' => (int) $group->sum('total_bookings'),
                    'gross_amount' => (float) $group->sum('gross_amount'),
                    'remitted_amount' => (float) $group->where('is_remitted', true)->sum('gross_amount'),
                    'outstanding_amount' => (float) $group->where('is_remitted', false)->sum('gross_amount'),
                ];
            })->sortBy('provider_name')->values();

            $title = 'Monthly Remittance List';
            $subtitle = Carbon::createFromFormat('Y-m', $selectedMonth, $tz)->format('F Y');
            $selectedDate = null;
        } else {
            $selectedDate = $this->sanitizeDate($request->query('date'), $today->toDateString());
            $rows = $this->buildLedgerCollection($selectedDate, $selectedDate);

            if ($providerId > 0) {
                $rows = $rows->where('provider_id', $providerId)->values();
            }

            $printRows = $rows->map(function ($row) {
                return (object) [
                    'provider_id' => (int) $row->provider_id,
                    'provider_name' => (string) $row->provider_name,
                    'provider_phone' => (string) $row->provider_phone,
                    'total_days' => 1,
                    'remitted_days' => $row->is_remitted ? 1 : 0,
                    'outstanding_days' => $row->is_remitted ? 0 : 1,
                    'total_bookings' => (int) $row->total_bookings,
                    'gross_amount' => (float) $row->gross_amount,
                    'remitted_amount' => $row->is_remitted ? (float) $row->gross_amount : 0.0,
                    'outstanding_amount' => !$row->is_remitted ? (float) $row->gross_amount : 0.0,
                ];
            })->sortBy('provider_name')->values();

            $title = 'Daily Remittance List';
            $subtitle = Carbon::parse($selectedDate, $tz)->format('F d, Y');
            $selectedMonth = null;
        }

        $totals = [
            'providers_count' => $printRows->count(),
            'gross_amount' => round((float) $printRows->sum('gross_amount'), 2),
            'remitted_amount' => round((float) $printRows->sum('remitted_amount'), 2),
            'outstanding_amount' => round((float) $printRows->sum('outstanding_amount'), 2),
            'total_bookings' => (int) $printRows->sum('total_bookings'),
        ];

        return view('admin.earnings_print', compact(
            'title',
            'subtitle',
            'period',
            'printRows',
            'totals',
            'providerOptions',
            'selectedProvider',
            'selectedDate',
            'selectedMonth',
            'providerId'
        ));
    }

    public function markRemitted(Request $request)
    {
        if (!Schema::hasTable('provider_remittances')) {
            return back()->withErrors(['Provider remittance tracking table is missing. Run migrations first.']);
        }

        $data = $request->validate([
            'provider_id' => ['required', 'integer'],
            'remit_date' => ['required', 'date'],
        ]);

        $ledgerRow = $this->findLedgerRow((int) $data['provider_id'], $data['remit_date']);

        if (!$ledgerRow) {
            return back()->withErrors(['The selected provider earnings entry could not be found.']);
        }

        $payload = [
            'status' => 'remitted',
            'recorded_amount' => $ledgerRow->gross_amount,
            'remitted_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('provider_remittances', 'remitted_by_admin_id') && session()->has('admin_id')) {
            $payload['remitted_by_admin_id'] = session('admin_id');
        }

        $existing = DB::table('provider_remittances')
            ->where('provider_id', $ledgerRow->provider_id)
            ->where('remit_date', $ledgerRow->remit_date)
            ->first();

        if ($existing) {
            DB::table('provider_remittances')
                ->where('id', $existing->id)
                ->update($payload);
        } else {
            $payload['provider_id'] = $ledgerRow->provider_id;
            $payload['remit_date'] = $ledgerRow->remit_date;
            $payload['created_at'] = now();

            DB::table('provider_remittances')->insert($payload);
        }

        $this->logAdminAction('Marked provider remittance for provider #' . $ledgerRow->provider_id . ' on ' . $ledgerRow->remit_date);

        return back()->with('success', 'Provider earnings marked as remitted.');
    }

    public function markOutstanding(Request $request)
    {
        if (!Schema::hasTable('provider_remittances')) {
            return back()->withErrors(['Provider remittance tracking table is missing. Run migrations first.']);
        }

        $data = $request->validate([
            'provider_id' => ['required', 'integer'],
            'remit_date' => ['required', 'date'],
        ]);

        $existing = DB::table('provider_remittances')
            ->where('provider_id', (int) $data['provider_id'])
            ->where('remit_date', $data['remit_date'])
            ->first();

        if (!$existing) {
            return back()->withErrors(['The selected remittance entry could not be found.']);
        }

        DB::table('provider_remittances')
            ->where('id', $existing->id)
            ->update([
                'status' => 'pending',
                'remitted_at' => null,
                'updated_at' => now(),
            ]);

        $this->logAdminAction('Reopened provider remittance for provider #' . $data['provider_id'] . ' on ' . $data['remit_date']);

        return back()->with('success', 'Provider earnings marked as outstanding again.');
    }

    private function buildLedgerCollection(string $dateFrom, string $dateTo): Collection
    {
        $providerNameExpr = $this->providerNameExpression('sp');

        $query = DB::table('bookings as b')
            ->join('service_providers as sp', 'sp.id', '=', 'b.provider_id')
            ->whereNotNull('b.provider_id')
            ->whereNotNull('b.booking_date')
            ->whereDate('b.booking_date', '>=', $dateFrom)
            ->whereDate('b.booking_date', '<=', $dateTo)
            ->whereRaw("LOWER(b.status) in ('paid', 'completed')")
            ->groupBy('b.provider_id', 'b.booking_date')
            ->selectRaw('b.provider_id')
            ->selectRaw('b.booking_date as remit_date')
            ->selectRaw('COUNT(*) as total_bookings')
            ->selectRaw('SUM(COALESCE(b.price, 0)) as gross_amount')
            ->selectRaw("COALESCE(MAX($providerNameExpr), CONCAT('Provider #', b.provider_id)) as provider_name");

        if (Schema::hasColumn('service_providers', 'status')) {
            $query->whereRaw("LOWER(COALESCE(sp.status, '')) = 'approved'");
        }

        if (Schema::hasColumn('service_providers', 'phone')) {
            $query->selectRaw("MAX(COALESCE(NULLIF(TRIM(sp.phone), ''), '')) as provider_phone");
        } else {
            $query->selectRaw("'' as provider_phone");
        }

        if (Schema::hasTable('services') && Schema::hasColumn('bookings', 'service_id')) {
            $query->leftJoin('services as s', 's.id', '=', 'b.service_id');

            if (Schema::hasColumn('services', 'name')) {
                $query->selectRaw("GROUP_CONCAT(DISTINCT COALESCE(NULLIF(TRIM(s.name), ''), 'Service') ORDER BY s.name SEPARATOR ', ') as service_names");
            } elseif (Schema::hasColumn('services', 'service_name')) {
                $query->selectRaw("GROUP_CONCAT(DISTINCT COALESCE(NULLIF(TRIM(s.service_name), ''), 'Service') ORDER BY s.service_name SEPARATOR ', ') as service_names");
            } else {
                $query->selectRaw("'' as service_names");
            }
        } else {
            $query->selectRaw("'' as service_names");
        }

        if (Schema::hasTable('provider_remittances')) {
            $query->leftJoin('provider_remittances as pr', function ($join) {
                $join->on('pr.provider_id', '=', 'b.provider_id')
                    ->on('pr.remit_date', '=', 'b.booking_date');
            });

            $query->selectRaw("MAX(COALESCE(pr.status, 'pending')) as remittance_status")
                ->selectRaw('MAX(pr.recorded_amount) as recorded_amount')
                ->selectRaw('MAX(pr.remitted_at) as remitted_at');
        } else {
            $query->selectRaw("'pending' as remittance_status")
                ->selectRaw('NULL as recorded_amount')
                ->selectRaw('NULL as remitted_at');
        }

        return $query->get()->map(function ($row) {
            $row->provider_name = trim((string) ($row->provider_name ?? '')) ?: 'Unnamed Provider';
            $row->provider_phone = trim((string) ($row->provider_phone ?? ''));
            $row->service_names = trim((string) ($row->service_names ?? ''));
            $row->total_bookings = (int) ($row->total_bookings ?? 0);
            $row->gross_amount = (float) ($row->gross_amount ?? 0);
            $row->recorded_amount = $row->recorded_amount !== null ? (float) $row->recorded_amount : null;
            $row->remittance_status = strtolower(trim((string) ($row->remittance_status ?? 'pending')));
            $row->is_remitted = $row->remittance_status === 'remitted';
            $row->amount_changed = $row->recorded_amount !== null && abs($row->recorded_amount - $row->gross_amount) > 0.009;

            return $row;
        });
    }

    private function providerOptions(): Collection
    {
        $providerNameExpr = $this->providerNameExpression('sp');

        $query = DB::table('service_providers as sp')
            ->selectRaw('sp.id')
            ->selectRaw("COALESCE($providerNameExpr, CONCAT('Provider #', sp.id)) as provider_name")
            ->orderBy('provider_name');

        if (Schema::hasColumn('service_providers', 'status')) {
            $query->whereRaw("LOWER(COALESCE(sp.status, '')) = 'approved'");
        }

        return $query->get()
            ->map(fn ($row) => (object) [
                'id' => (int) $row->id,
                'name' => trim((string) $row->provider_name) ?: ('Provider #' . $row->id),
            ]);
    }

    private function findLedgerRow(int $providerId, string $remitDate): ?object
    {
        return $this->buildLedgerCollection($remitDate, $remitDate)
            ->first(function ($row) use ($providerId, $remitDate) {
                return (int) $row->provider_id === $providerId
                    && (string) $row->remit_date === $remitDate;
            });
    }

    private function providerNameExpression(string $alias): string
    {
        $table = 'service_providers';

        if (Schema::hasColumn($table, 'first_name') && Schema::hasColumn($table, 'last_name')) {
            return "
                NULLIF(
                    TRIM(
                        CONCAT(
                            COALESCE($alias.first_name, ''),
                            ' ',
                            COALESCE($alias.last_name, '')
                        )
                    ),
                    ''
                )
            ";
        }

        if (Schema::hasColumn($table, 'name')) {
            return "NULLIF(TRIM($alias.name), '')";
        }

        if (Schema::hasColumn($table, 'full_name')) {
            return "NULLIF(TRIM($alias.full_name), '')";
        }

        return "NULL";
    }

    private function sanitizeDate(?string $value, string $fallback): string
    {
        try {
            return $value ? Carbon::parse($value)->toDateString() : $fallback;
        } catch (\Throwable $exception) {
            return $fallback;
        }
    }

    private function sanitizeMonth(?string $value, string $fallback): string
    {
        try {
            return $value ? Carbon::createFromFormat('Y-m', $value)->format('Y-m') : $fallback;
        } catch (\Throwable $exception) {
            return $fallback;
        }
    }

    private function logAdminAction(string $action): void
    {
        if (!Schema::hasTable('admin_logs')) {
            return;
        }

        $data = [
            'action' => $action,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('admin_logs', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if (Schema::hasColumn('admin_logs', 'admin_id') && session()->has('admin_id')) {
            $data['admin_id'] = session('admin_id');
        }

        DB::table('admin_logs')->insert($data);
    }
}
