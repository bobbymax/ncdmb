<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Payment;
use App\Repositories\DocumentRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    protected DocumentRepository $documentRepository;

    public function __construct(DocumentRepository $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    /**
     * Get all dashboard analytics
     */
    public function getDashboardData(): array
    {
        $user = Auth::user();
        $userId = $user->id;

        // Get all documents (using repository's scope-aware query)
        $documentsResult = $this->documentRepository->all();
        
        // If paginated, get the items, otherwise use directly
        if (method_exists($documentsResult, 'items')) {
            $documents = collect($documentsResult->items());
        } else {
            $documents = collect($documentsResult);
        }

        // Load missing relationships on each document model
        $documents->each(function ($doc) {
            if ($doc instanceof \App\Models\Document) {
                $doc->loadMissing(['documentType', 'user.department']);
            }
        });

        // Eager load payments for all documents using polymorphic relationship
        // Use select() to only get needed fields and prevent eager loading relationships
        $documentIds = $documents->pluck('id')->toArray();
        $paymentsByDocument = Payment::where('resource_type', 'App\\Models\\Document')
            ->whereIn('resource_id', $documentIds)
            ->select('id', 'resource_id', 'total_approved_amount', 'total_amount_paid')
            ->get()
            ->groupBy('resource_id');

        // Attach payments to documents as a simple collection
        $documents->each(function ($doc) use ($paymentsByDocument) {
            $doc->payments = $paymentsByDocument->get($doc->id, collect());
        });

        // Compute all analytics using efficient database queries
        return [
            'analytics' => $this->getAnalytics($documents, $userId),
            'groupedByStatus' => $this->getGroupedByStatus($documents),
            'groupedByDocumentType' => $this->getGroupedByDocumentType($documents),
            'monthlyTrend' => $this->getMonthlyTrend($documents),
            'documentsThisMonth' => $this->getDocumentsThisMonth($documents),
            'documentsThisWeek' => $this->getDocumentsThisWeek($documents),
            'departmentAnalytics' => $this->getDepartmentAnalytics($documents),
            'topDepartments' => $this->getTopDepartments($documents),
            'paymentAnalytics' => $this->getPaymentAnalytics($documents),
            'workflowAnalytics' => $this->getWorkflowAnalytics($documents),
            'completionAnalytics' => $this->getCompletionAnalytics($documents),
            'budgetAnalytics' => $this->getBudgetAnalytics($documents),
            'documentsNeedingAttention' => $this->getDocumentsNeedingAttention($documents, $userId),
        ];
    }

    /**
     * Get main analytics metrics
     */
    protected function getAnalytics($documents, int $userId): array
    {
        $totalDocuments = $documents->count();
        
        // Calculate total document value using database aggregation
        $totalDocumentValue = $documents->sum(function ($doc) {
            $docValue = $doc->approved_amount ?? 0;
            $paymentsValue = ($doc->payments ?? collect())->sum('total_approved_amount') ?? 0;
            return max($docValue, $paymentsValue);
        });

        // User documents
        $userDocuments = $documents->where('user_id', $userId);
        $userDocumentsCount = $userDocuments->count();
        $userDocumentValue = $userDocuments->sum(function ($doc) {
            $docValue = $doc->approved_amount ?? 0;
            $paymentsValue = ($doc->payments ?? collect())->sum('total_approved_amount') ?? 0;
            return max($docValue, $paymentsValue);
        });

        // Documents needing attention
        $documentsNeedingAttention = $this->getDocumentsNeedingAttention($documents, $userId);
        $documentsNeedingAttentionCount = count($documentsNeedingAttention);
        $documentsNeedingAttentionValue = collect($documentsNeedingAttention)->sum(function ($doc) {
            $docValue = $doc['approved_amount'] ?? 0;
            // For documents needing attention, we'll need to get payments separately
            $payments = Payment::where('resource_type', 'App\\Models\\Document')
                ->where('resource_id', $doc['id'])
                ->sum('total_approved_amount') ?? 0;
            return max($docValue, $payments);
        });

        // Document types and statuses counts
        $totalDocumentTypes = $documents->groupBy('document_type_id')->count();
        $totalDocumentStatuses = $documents->groupBy('status')->count();

        // Total value by type
        $totalValueByType = $documents->groupBy('document_type_id')->sum(function ($group) {
            return $group->sum(function ($doc) {
                $docValue = $doc->approved_amount ?? 0;
                $paymentsValue = ($doc->payments ?? collect())->sum('total_approved_amount') ?? 0;
                return max($docValue, $paymentsValue);
            });
        });

        return [
            'totalDocuments' => $totalDocuments,
            'totalDocumentValue' => $totalDocumentValue,
            'totalDocumentTypes' => $totalDocumentTypes,
            'totalDocumentStatuses' => $totalDocumentStatuses,
            'totalValueByType' => $totalValueByType,
            'userDocuments' => [
                'count' => $userDocumentsCount,
                'value' => $userDocumentValue,
            ],
            'documentsNeedingAttention' => [
                'count' => $documentsNeedingAttentionCount,
                'value' => $documentsNeedingAttentionValue,
            ],
        ];
    }

    /**
     * Group documents by status
     */
    protected function getGroupedByStatus($documents): array
    {
        return $documents->groupBy('status')->map(function ($group) {
            return $group->count();
        })->toArray();
    }

    /**
     * Group documents by type with count and value
     */
    protected function getGroupedByDocumentType($documents): array
    {
        return $documents->groupBy('document_type_id')->map(function ($group) {
            $firstDoc = $group->first();
            $typeName = $firstDoc->documentType->name ?? 'Unknown Type';
            $count = $group->count();
            $value = $group->sum(function ($doc) {
                $docValue = $doc->approved_amount ?? 0;
                $paymentsValue = ($doc->payments ?? collect())->sum('total_approved_amount') ?? 0;
                return max($docValue, $paymentsValue);
            });

            return [
                'name' => $typeName,
                'count' => $count,
                'value' => $value,
            ];
        })->values()->toArray();
    }

    /**
     * Get monthly trend for last 6 months
     */
    protected function getMonthlyTrend($documents): array
    {
        $now = Carbon::now();
        $trend = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::create($now->year, $now->month - $i, 1);
            $monthName = $date->format('M');
            $year = $date->year;

            $docsInMonth = $documents->filter(function ($doc) use ($date) {
                if (!$doc->created_at) {
                    return false;
                }
                $docDate = Carbon::parse($doc->created_at);
                return $docDate->month === $date->month && $docDate->year === $date->year;
            });

            $value = $docsInMonth->sum(function ($doc) {
                $docValue = $doc->approved_amount ?? 0;
                $paymentsValue = ($doc->payments ?? collect())->sum('total_approved_amount') ?? 0;
                return max($docValue, $paymentsValue);
            });

            $trend[] = [
                'month' => "{$monthName} {$year}",
                'count' => $docsInMonth->count(),
                'value' => $value,
            ];
        }

        return $trend;
    }

    /**
     * Get documents created this month
     */
    protected function getDocumentsThisMonth($documents): int
    {
        $now = Carbon::now();
        return $documents->filter(function ($doc) use ($now) {
            if (!$doc->created_at) {
                return false;
            }
            $docDate = Carbon::parse($doc->created_at);
            return $docDate->month === $now->month && $docDate->year === $now->year;
        })->count();
    }

    /**
     * Get documents created this week
     */
    protected function getDocumentsThisWeek($documents): int
    {
        $weekAgo = Carbon::now()->subWeek();
        return $documents->filter(function ($doc) use ($weekAgo) {
            if (!$doc->created_at) {
                return false;
            }
            $docDate = Carbon::parse($doc->created_at);
            return $docDate->gte($weekAgo);
        })->count();
    }

    /**
     * Get department analytics
     */
    protected function getDepartmentAnalytics($documents): array
    {
        $deptMap = [];

        foreach ($documents as $doc) {
            $dept = $doc->user->department->abv ?? $doc->dept ?? 'Unknown';
            
            if (!isset($deptMap[$dept])) {
                $deptMap[$dept] = ['count' => 0, 'value' => 0];
            }

            $deptMap[$dept]['count'] += 1;

            $docValue = $doc->approved_amount ?? 0;
            $paymentsValue = ($doc->payments ?? collect())->sum('total_approved_amount') ?? 0;
            $deptMap[$dept]['value'] += max($docValue, $paymentsValue);
        }

        return collect($deptMap)
            ->map(function ($data, $name) {
                return ['name' => $name, ...$data];
            })
            ->sortByDesc('value')
            ->values()
            ->toArray();
    }

    /**
     * Get top 5 departments
     */
    protected function getTopDepartments($documents): array
    {
        $analytics = $this->getDepartmentAnalytics($documents);
        return array_slice($analytics, 0, 5);
    }

    /**
     * Get payment analytics
     */
    protected function getPaymentAnalytics($documents): array
    {
        $payments = $documents->flatMap(function ($doc) {
            return $doc->payments ?? collect();
        });

        // Payment method and currency columns don't exist in the payments table
        // Return empty arrays for these distributions
        $methodDistribution = [];
        $currencyDistribution = [];

        $totalPaid = $payments->sum('total_amount_paid') ?? 0;
        $totalApproved = $payments->sum('total_approved_amount') ?? 0;
        $averagePayment = $payments->count() > 0 ? $totalApproved / $payments->count() : 0;

        return [
            'methodDistribution' => $methodDistribution,
            'currencyDistribution' => $currencyDistribution,
            'totalPaid' => $totalPaid,
            'totalApproved' => $totalApproved,
            'averagePayment' => $averagePayment,
            'totalPayments' => $payments->count(),
        ];
    }

    /**
     * Get workflow analytics
     */
    protected function getWorkflowAnalytics($documents): array
    {
        $docsWithWorkflow = $documents->filter(fn($doc) => $doc->workflow);
        $processingDocs = $documents->filter(fn($doc) => !$doc->is_completed && $doc->created_at);

        $avgProcessingTime = $processingDocs->map(function ($doc) {
            $created = Carbon::parse($doc->created_at);
            $now = Carbon::now();
            return $now->diffInDays($created);
        })->avg() ?? 0;

        $stuckDocuments = $processingDocs->filter(function ($doc) {
            $created = Carbon::parse($doc->created_at);
            $now = Carbon::now();
            return $now->diffInDays($created) > 30;
        });

        $efficiencyScore = $documents->count() > 0
            ? round((($documents->count() - $stuckDocuments->count()) / $documents->count()) * 100)
            : 0;

        return [
            'totalWithWorkflow' => $docsWithWorkflow->count(),
            'inProgress' => $processingDocs->count(),
            'avgProcessingDays' => round($avgProcessingTime),
            'stuckCount' => $stuckDocuments->count(),
            'efficiencyScore' => $efficiencyScore,
        ];
    }

    /**
     * Get completion analytics
     */
    protected function getCompletionAnalytics($documents): array
    {
        $completed = $documents->filter(fn($doc) => $doc->is_completed);
        $completionRate = $documents->count() > 0
            ? round(($completed->count() / $documents->count()) * 100)
            : 0;

        $completedDocs = $documents->filter(function ($doc) {
            return $doc->is_completed && $doc->created_at && $doc->updated_at;
        });

        $avgDaysToComplete = $completedDocs->map(function ($doc) {
            $created = Carbon::parse($doc->created_at);
            $updated = Carbon::parse($doc->updated_at);
            return $updated->diffInDays($created);
        })->avg() ?? 0;

        $groupedByType = $this->getGroupedByDocumentType($documents);
        $byType = collect($groupedByType)->map(function ($typeData) use ($documents) {
            $typeCompleted = $documents->filter(function ($doc) use ($typeData) {
                return $doc->documentType && $doc->documentType->name === $typeData['name'] && $doc->is_completed;
            })->count();

            return [
                'name' => $typeData['name'],
                'rate' => $typeData['count'] > 0 ? round(($typeCompleted / $typeData['count']) * 100) : 0,
                'completed' => $typeCompleted,
                'total' => $typeData['count'],
            ];
        })->sortByDesc('rate')->values()->toArray();

        return [
            'completionRate' => $completionRate,
            'completed' => $completed->count(),
            'pending' => $documents->count() - $completed->count(),
            'avgDaysToComplete' => round($avgDaysToComplete),
            'byType' => $byType,
        ];
    }

    /**
     * Get budget analytics
     */
    protected function getBudgetAnalytics($documents): array
    {
        $byYear = [];

        foreach ($documents as $doc) {
            $year = $doc->budget_year ?? Carbon::now()->year;

            if (!isset($byYear[$year])) {
                $byYear[$year] = ['allocated' => 0, 'used' => 0, 'count' => 0];
            }

            $docValue = $doc->approved_amount ?? 0;
            $paymentsValue = ($doc->payments ?? collect())->sum('total_approved_amount') ?? 0;
            $byYear[$year]['allocated'] += max($docValue, $paymentsValue);
            $byYear[$year]['used'] += ($doc->payments ?? collect())->sum('total_amount_paid') ?? 0;
            $byYear[$year]['count'] += 1;
        }

        $currentYear = Carbon::now()->year;
        $currentYearData = $byYear[$currentYear] ?? ['allocated' => 0, 'used' => 0, 'count' => 0];
        $utilizationRate = $currentYearData['allocated'] > 0
            ? round(($currentYearData['used'] / $currentYearData['allocated']) * 100)
            : 0;

        $byYearArray = collect($byYear)->map(function ($data, $year) {
            $utilization = $data['allocated'] > 0
                ? round(($data['used'] / $data['allocated']) * 100)
                : 0;

            return [
                'year' => (int)$year,
                ...$data,
                'utilization' => $utilization,
            ];
        })->sortByDesc('year')->values()->toArray();

        return [
            'byYear' => $byYearArray,
            'currentYear' => [
                ...$currentYearData,
                'year' => $currentYear,
                'utilization' => $utilizationRate,
            ],
        ];
    }

    /**
     * Get documents needing user's attention
     */
    protected function getDocumentsNeedingAttention($documents, int $userId): array
    {
        $flowTypes = ['from', 'through', 'to'];
        $needingAttention = [];

        foreach ($documents as $doc) {
            if (
                $doc->config &&
                $doc->pointer &&
                !$doc->is_completed &&
                is_array($doc->config)
            ) {
                foreach ($flowTypes as $flowType) {
                    $flowConfig = $doc->config[$flowType] ?? null;
                    if (
                        $flowConfig &&
                        isset($flowConfig['identifier']) &&
                        $flowConfig['identifier'] === $doc->pointer &&
                        isset($flowConfig['user_id']) &&
                        $flowConfig['user_id'] === $userId
                    ) {
                        $needingAttention[] = [
                            'id' => $doc->id,
                            'title' => $doc->title,
                            'ref' => $doc->ref,
                            'status' => $doc->status,
                            'approved_amount' => $doc->approved_amount ?? 0,
                        ];
                        break;
                    }
                }
            }
        }

        return $needingAttention;
    }
}

