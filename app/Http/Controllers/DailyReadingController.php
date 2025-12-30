<?php

namespace App\Http\Controllers;

use App\Models\DailyReading;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\DailyReadingResource;

class DailyReadingController extends Controller
{
    public function display () {
        $today = now()->toDateString();

        $reading = DailyReading::with('author')
            ->where('display_date', $today)
            ->where('status', 'published')
            ->first();

        if (!$reading) {
            $reading = DailyReading::with('author')
                ->where('status', 'scheduled')
                ->where('scheduled_at', '<', $today)
                ->orderBy('scheduled_at', 'desc')
                ->first();

            if (!$reading) {
                $reading = DailyReading::with('author')
                    ->where('status', 'published')
                    ->where('display_date', '<', $today)
                    ->orderBy('display_date', 'desc')
                    ->first();

                    if (!$reading) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Aucune lecture disponible.',
                            'date' => $today,
                        ], 404);
                    }
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Lecture du jour',
            'date' => $today,
            'is_todays_reading' => true,
            'data' => new DailyReadingResource($reading),
        ]);
    }

    public function index (Request $request) {
       try {
         $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date|before_or_equal:date_to',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'author_id' => 'nullable|exists:admins,id',
        ]);
        $query = DailyReading::with(['author'])
                            ->orderBy('created_at', 'desc');
         // Filtres
         // by status
        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }
        // by date
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('display_date', [$request->date_from,$request->date_to]);
        } elseif ($request->filled('date_from')) {
            $query->where('display_date', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->where('display_date', '<=', $request->date_to);
        }
        // by liturgical category
        if ($request->get('liturgical_category')) {
            $query->where('liturgical_category', $request->get('liturgical_category'));
        }
        // by author
        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }
        if ($request->get('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('verse', 'like', "%{$search}%")
                  ->orWhere('meditation', 'like', "%{$search}%")
                  ->orWhere('biblical_reference', 'like', "%{$search}%");
            });
        }
         // Pagination avec paramètre optionnel
        $perPage = $request->get('per_page', 10);
        $dailyReading = $query->paginate($perPage);

        // Réponse JSON avec les actualités paginées
        return response()->json([
            'message' => "Actualities retrieved successfully",
            'data' => DailyReadingResource::collection($dailyReading),
            'meta' => [
                'current_page' => $dailyReading->currentPage(),
                'total' => $dailyReading->total(),
                'per_page' => $dailyReading->perPage(),
            ]
        ], 200);
       }  catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            Log::error('reading index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve daily reading',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show (DailyReading $reading) {
        try {
            // Incrémenter le compteur de vues
            $reading->increment('views_count');

            return response()->json([
                'message' => "Reading retrieved successfully",
                'data' => new DailyReadingResource($reading->load(['author']))
            ], 200);

        } catch (\Exception $e) {
            Log::error('Reading show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve reading',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function store (Request $request) {
        try {
             /** @var \App\Models\Admin $admin */
            $admin = auth()->guard('admin')->user();

            $validator = Validator::make($request->all(), [
                'display_date' => ['required', 'date', 'date_format:Y-m-d','after_or_equal:today','unique:daily_readings,display_date'],
                'verse' => ['required','string','max:500','min:10'],
                'meditation' => ['required','string','min:50','max:5000'],
                'biblical_reference' => ['required','string','max:100',
                    'regex:/^[A-Za-z]{1,4}\s*\d{1,3}(?::\d{1,3}(?:-\d{1,3})?)?(?:,\s*[A-Za-z]{1,4}\s*\d{1,3}(?::\d{1,3}(?:-\d{1,3})?)?)*$/'
                    // Format: Jn 3:16-18 ou Mt 5:1-12, Lc 6:20-26
                ],
                'liturgical_category' => ['nullable','string','max:50',
                    Rule::in([
                        'Temps ordinaire',
                        'Avent',
                        'Noël',
                        'Carême',
                        'Pâques',
                        'Pentecôte',
                        'Toussaint',
                        'Mariage',
                        'Funérailles',
                        'Baptême',
                        'Messe dominicale',
                        'Fête de saint'
                    ])
                ],
                'status' => ['required','string',Rule::in(['draft', 'scheduled', 'published', 'archived'])],
            ]);
            $validated = $validator->validated();
            // Si le statut est PUBLISHED et published_at n'est pas défini, définir à maintenant
            if (($validated['status'] ?? 'draft') === 'published' && empty($validated['display_date'])) {
                $validated['display_date'] = now();
            }
            $readingDailing = $admin->daily_reading()->create( $validated);

            return response()->json([
                'message' => "reading has been created",
                "data" => new DailyReadingResource($readingDailing)
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            Log::error('Reading create error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to store reading',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function update (Request $request, DailyReading $dailingReading) {
        try {
            $validator = Validator::make($request->all(), [
                'display_date' => ['sometimes','required','date','date_format:Y-m-d','after_or_equal:today',
                    Rule::unique('daily_readings', 'display_date')->ignore($dailingReading->id)
                ],
                'verse' => ['sometimes','required','string','max:500','min:10'],
                'meditation' => ['sometimes','required','string','min:50','max:5000'],
                'biblical_reference' => ['sometimes','required','string','max:100',
                    'regex:/^[A-Za-z]{1,4}\s*\d{1,3}(?::\d{1,3}(?:-\d{1,3})?)?(?:,\s*[A-Za-z]{1,4}\s*\d{1,3}(?::\d{1,3}(?:-\d{1,3})?)?)*$/'
                ],
                'liturgical_category' => ['sometimes','nullable','string','max:50',
                    Rule::in([
                        'Temps ordinaire',
                        'Avent',
                        'Noël',
                        'Carême',
                        'Pâques',
                        'Pentecôte',
                        'Toussaint',
                        'Mariage',
                        'Funérailles',
                        'Baptême',
                        'Messe dominicale',
                        'Fête de saint'
                    ])
                ],
                'status' => ['sometimes','required','string',
                    Rule::in(['draft', 'scheduled', 'published', 'archived'])
                ]
            ]);
            $validatedData = $validator->validated();
             // Si le statut change en PUBLISHED et published_at n'est pas défini, définir à maintenant
            if (isset($validated['status']) && $validated['status'] === 'scheduled' && empty($validated['display_date'])) {
                $validated['display_date'] = now();
            }
            $dailingReading->update($validatedData);
             return response()->json([
                'message' => "reading has been created",
                "data" => new DailyReadingResource($dailingReading)
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            // Gestion des autres exceptions
        } catch (\Exception $e) {
            Log::error('Reading update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update reading',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy (DailyReading $dailingReading)  {
        try {
             $dailingReading->delete();

            return response()->json([
                'statut' => 'success',
                'message' => "reading has been deleted",
            ], 200);
        } catch (\Exception $e) {
            Log::error('Reading delete error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete reading',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
