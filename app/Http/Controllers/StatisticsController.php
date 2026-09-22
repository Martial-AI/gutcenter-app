<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentMonthlyFee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->can('statistics.view'), 403);

        $studentCount = Student::count();
        $teacherCount = User::role('Prof')->count();
        $staffCount = User::role(['Secrétaire', 'Trésorier', 'Directeur General'])->count();

        $classes = SchoolClass::withCount(['enrollments' => function ($q) {
            $q->where('status', 'active');
        }])->orderBy('name')->get();

        $classDistribution = $classes->map(fn ($c) => [
            'name' => $c->name,
            'count' => $c->enrollments_count,
            'capacity' => (int) ($c->capacity ?: 30),
        ])->values();

        $totalCapacity = $classes->sum('capacity') ?: 0;
        $capacityOccupancyRate = $totalCapacity > 0 ? min(100, round(($studentCount / $totalCapacity) * 100, 1)) : 0.0;

        // Monthly Financial Trends (Past actual records & forward projections)
        $financialMonths = collect();
        $startDate = now()->subMonths(5)->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $monthCarbon = $startDate->copy()->addMonths($i);
            $isFuture = $monthCarbon->isAfter(now()->endOfMonth());

            $rev = (float) StudentMonthlyFee::whereYear('fee_month', $monthCarbon->year)
                ->whereMonth('fee_month', $monthCarbon->month)
                ->whereNotNull('paid_at')
                ->sum('amount');

            $exp = (float) Expense::whereYear('created_at', $monthCarbon->year)
                ->whereMonth('created_at', $monthCarbon->month)
                ->sum('amount');

            $financialMonths->push([
                'label' => $monthCarbon->locale(app()->getLocale())->translatedFormat('M Y'),
                'is_projection' => $isFuture,
                'revenue' => $rev,
                'expense' => $exp,
                'net' => $rev - $exp,
            ]);
        }

        // Multi-Year Growth & Capacity Projections
        $currentYear = now()->year;
        $yearlyProjections = collect([
            ['year' => (string) ($currentYear - 1), 'students' => $studentCount, 'capacity' => (int) $totalCapacity],
            ['year' => (string) $currentYear, 'students' => $studentCount, 'capacity' => (int) $totalCapacity],
            ['year' => (string) ($currentYear + 1), 'students' => $studentCount, 'capacity' => (int) $totalCapacity],
            ['year' => (string) ($currentYear + 2), 'students' => $studentCount, 'capacity' => (int) $totalCapacity],
        ]);

        return view('statistics.index', compact(
            'studentCount',
            'teacherCount',
            'staffCount',
            'classes',
            'classDistribution',
            'totalCapacity',
            'capacityOccupancyRate',
            'financialMonths',
            'yearlyProjections'
        ));
    }
}
