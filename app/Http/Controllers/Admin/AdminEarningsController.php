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
        $providerOptions = $this->providerOptions();

        $filteredRows = $ledgerRows->filter(function ($row) use ($search, $remittanceFilter) {
            $needle = strtolower($search);
            $haystack = strtolower(implode(' ', array_filter([
                (string) $row->provider_name,
                (string) $row->provider_phone,
                (string) $row->service_names,
                (string) $row->remit_date,
                (string) $row->provider_id,
                (string) $row->total_bookings,
                number_format((float) $row->gross_amount, 2, '.', ''),
            ])));

            $matchesSearch = $needle === '' || str_contains($haystack, $needle);

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

            $rows = $this->buildPrintLedgerCollection($monthStart, $monthEnd);

            if ($providerId > 0) {
                $rows = $rows->where('provider_id', $providerId)->values();
            }

            $printRows = $this->sortPrintRows($rows);

            $title = 'Monthly Remittance List';
            $subtitle = Carbon::createFromFormat('Y-m', $selectedMonth, $tz)->format('F Y');
            $selectedDate = null;
        } else {
            $selectedDate = $this->sanitizeDate($request->query('date'), $today->toDateString());
            $rows = $this->buildPrintLedgerCollection($selectedDate, $selectedDate);

            if ($providerId > 0) {
                $rows = $rows->where('provider_id', $providerId)->values();
            }

            $printRows = $this->sortPrintRows($rows);

            $title = 'Daily Remittance List';
            $subtitle = Carbon::parse($selectedDate, $tz)->format('F d, Y');
            $selectedMonth = null;
        }

        $totals = [
            'providers_count' => $printRows->pluck('provider_id')->unique()->count(),
            'entry_count' => $printRows->count(),
            'gross_amount' => round((float) $printRows->sum('gross_amount'), 2),
            'remitted_amount' => round((float) $printRows->where('is_remitted', true)->sum('gross_amount'), 2),
            'outstanding_amount' => round((float) $printRows->where('is_remitted', false)->sum('gross_amount'), 2),
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
        $statusSql = $this->normalizedStatusSql('b.status');
        $bookingDateSql = $this->bookingDateSql('b.booking_date');
        $earningStatuses = $this->earningStatusListSql();

        $query = DB::table('bookings as b')
            ->join('service_providers as sp', 'sp.id', '=', 'b.provider_id')
            ->whereNotNull('b.provider_id')
            ->whereNotNull('b.booking_date')
            ->whereRaw("{$bookingDateSql} >= ? AND {$bookingDateSql} <= ?", [$dateFrom, $dateTo])
            ->whereRaw("{$statusSql} in ({$earningStatuses})")
            ->groupBy('b.provider_id', DB::raw($bookingDateSql))
            ->selectRaw('b.provider_id')
            ->selectRaw("{$bookingDateSql} as remit_date")
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

        if (Schema::hasColumn('bookings', 'service_id') || Schema::hasColumn('bookings', 'service_option_id')) {
            $serviceExpr = $this->serviceNameExpression('s', 'b');
            $optionExpr = $this->serviceOptionExpression('o', 'b');
            $serviceSummaryExpr = $this->serviceSummaryExpression($serviceExpr, $optionExpr);

            if (Schema::hasTable('services') && Schema::hasColumn('bookings', 'service_id')) {
                $query->leftJoin('services as s', 's.id', '=', 'b.service_id');
            }

            if (Schema::hasTable('service_options') && Schema::hasColumn('bookings', 'service_option_id')) {
                $query->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id');
            }

            $query->selectRaw("GROUP_CONCAT(DISTINCT {$serviceSummaryExpr} SEPARATOR ', ') as service_names");
        } else {
            $query->selectRaw("'' as service_names");
        }

        if (Schema::hasTable('provider_remittances')) {
            $query->leftJoin('provider_remittances as pr', function ($join) use ($bookingDateSql) {
                $join->on('pr.provider_id', '=', 'b.provider_id')
                    ->whereRaw("pr.remit_date = {$bookingDateSql}");
            });

            $query->selectRaw("MAX(COALESCE(pr.status, 'pending')) as remittance_status")
                ->selectRaw('MAX(pr.recorded_amount) as recorded_amount')
                ->selectRaw('MAX(pr.remitted_at) as remitted_at');
        } else {
            $query->selectRaw("'pending' as remittance_status")
                ->selectRaw('NULL as recorded_amount')
                ->selectRaw('NULL as remitted_at');
        }

        return $this->normalizeLedgerRows($query->get());
    }

    private function buildPrintLedgerCollection(string $dateFrom, string $dateTo): Collection
    {
        if (!Schema::hasTable('provider_remittances')) {
            return $this->buildLedgerCollection($dateFrom, $dateTo);
        }

        $providerNameExpr = $this->providerNameExpression('sp');
        $statusSql = $this->normalizedStatusSql('b.status');
        $bookingDateSql = $this->bookingDateSql('b.booking_date');
        $earningStatuses = $this->earningStatusListSql();
        $serviceExpr = $this->serviceNameExpression('s', 'b');
        $optionExpr = $this->serviceOptionExpression('o', 'b');
        $serviceSummaryExpr = $this->serviceSummaryExpression($serviceExpr, $optionExpr);

        $query = DB::table('bookings as b')
            ->join('service_providers as sp', 'sp.id', '=', 'b.provider_id')
            ->leftJoin('provider_remittances as pr', function ($join) use ($bookingDateSql) {
                $join->on('pr.provider_id', '=', 'b.provider_id')
                    ->whereRaw("pr.remit_date = {$bookingDateSql}");
            })
            ->whereNotNull('b.provider_id')
            ->whereNotNull('b.booking_date')
            ->whereRaw("{$statusSql} in ({$earningStatuses})")
            ->where(function ($builder) use ($dateFrom, $dateTo, $bookingDateSql) {
                $builder
                    ->whereRaw("{$bookingDateSql} >= ? AND {$bookingDateSql} <= ?", [$dateFrom, $dateTo])
                    ->orWhere(function ($dateQuery) use ($dateFrom, $dateTo) {
                        $dateQuery->whereNotNull('pr.remitted_at')
                            ->whereDate('pr.remitted_at', '>=', $dateFrom)
                            ->whereDate('pr.remitted_at', '<=', $dateTo);
                    });
            })
            ->groupBy('b.provider_id', DB::raw($bookingDateSql))
            ->selectRaw('b.provider_id')
            ->selectRaw("{$bookingDateSql} as remit_date")
            ->selectRaw('COUNT(*) as total_bookings')
            ->selectRaw('SUM(COALESCE(b.price, 0)) as gross_amount')
            ->selectRaw("COALESCE(MAX($providerNameExpr), CONCAT('Provider #', b.provider_id)) as provider_name")
            ->selectRaw("MAX(COALESCE(pr.status, 'pending')) as remittance_status")
            ->selectRaw('MAX(pr.recorded_amount) as recorded_amount')
            ->selectRaw('MAX(pr.remitted_at) as remitted_at');

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
        }

        if (Schema::hasTable('service_options') && Schema::hasColumn('bookings', 'service_option_id')) {
            $query->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id');
        }

        if (Schema::hasColumn('bookings', 'service_id') || Schema::hasColumn('bookings', 'service_option_id')) {
            $query->selectRaw("GROUP_CONCAT(DISTINCT {$serviceSummaryExpr} SEPARATOR ', ') as service_names");
        } else {
            $query->selectRaw("'' as service_names");
        }

        return $this->normalizeLedgerRows($query->get());
    }

    private function normalizeLedgerRows(Collection $rows): Collection
    {
        return $rows->map(function ($row) {
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

    private function sortPrintRows(Collection $rows): Collection
    {
        return $rows->sort(function ($a, $b) {
            $aMoment = !empty($a->remitted_at)
                ? Carbon::parse($a->remitted_at)->format('Y-m-d H:i:s')
                : ((string) $a->remit_date . ' 00:00:00');
            $bMoment = !empty($b->remitted_at)
                ? Carbon::parse($b->remitted_at)->format('Y-m-d H:i:s')
                : ((string) $b->remit_date . ' 00:00:00');

            $momentCompare = strcmp($bMoment, $aMoment);
            if ($momentCompare !== 0) {
                return $momentCompare;
            }

            return strcmp((string) $a->provider_name, (string) $b->provider_name);
        })->values();
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

    private function serviceNameExpression(string $alias, string $bookingAlias): string
    {
        if (!Schema::hasTable('services')) {
            return "CONCAT('Service #', {$bookingAlias}.service_id)";
        }

        if (Schema::hasColumn('services', 'name')) {
            return "NULLIF(TRIM({$alias}.name), '')";
        }

        if (Schema::hasColumn('services', 'service_name')) {
            return "NULLIF(TRIM({$alias}.service_name), '')";
        }

        if (Schema::hasColumn('services', 'title')) {
            return "NULLIF(TRIM({$alias}.title), '')";
        }

        return "CONCAT('Service #', {$bookingAlias}.service_id)";
    }

    private function serviceOptionExpression(string $alias, string $bookingAlias): string
    {
        if (!Schema::hasColumn('bookings', 'service_option_id')) {
            return "''";
        }

        if (!Schema::hasTable('service_options')) {
            return "CONCAT('Option #', {$bookingAlias}.service_option_id)";
        }

        if (Schema::hasColumn('service_options', 'label')) {
            return "NULLIF(TRIM({$alias}.label), '')";
        }

        if (Schema::hasColumn('service_options', 'name')) {
            return "NULLIF(TRIM({$alias}.name), '')";
        }

        if (Schema::hasColumn('service_options', 'option_name')) {
            return "NULLIF(TRIM({$alias}.option_name), '')";
        }

        if (Schema::hasColumn('service_options', 'title')) {
            return "NULLIF(TRIM({$alias}.title), '')";
        }

        return "CONCAT('Option #', {$bookingAlias}.service_option_id)";
    }

    private function serviceSummaryExpression(string $serviceExpr, string $optionExpr): string
    {
        return "
            TRIM(
                CONCAT(
                    COALESCE({$serviceExpr}, 'Service'),
                    CASE
                        WHEN COALESCE({$optionExpr}, '') <> '' THEN CONCAT(' / ', {$optionExpr})
                        ELSE ''
                    END
                )
            )
        ";
    }

    private function normalizedStatusSql(string $column = 'b.status'): string
    {
        $normalized = "LOWER(REPLACE(REPLACE(COALESCE({$column}, ''),'-','_'),' ','_'))";

        return "CASE
            WHEN {$normalized} IN ('cancelled', 'canceled', 'cancel') THEN 'cancelled'
            WHEN {$normalized} = 'inprogress' THEN 'in_progress'
            ELSE {$normalized}
        END";
    }

    private function bookingDateSql(string $column = 'b.booking_date'): string
    {
        return "DATE({$column})";
    }

    private function earningStatusListSql(): string
    {
        return "'" . implode("','", self::EARNING_STATUSES) . "'";
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
