<?php

namespace App\Http\Controllers;

use App\Models\DailyReading;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\DailingReadingResource;

class DailyReadingController extends Controller
{
    public function index () {
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

    public function store (Request $request) {
        try {
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
                'status' => [
                    'required',
                    'string',
                    Rule::in(['draft', 'scheduled', 'published', 'archived'])
                ],
                'scheduled_at' => [
                    'sometimes',
                    'nullable',
                    'date',
                    'after:now',
                    'before_or_equal:display_date',
                    'required_if:status,scheduled'
                ]
            ]);

            if ($validator->fails()) {
                return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
            }

            $readingDailing = $admin->daily_reading()->create([
                'display_date' => $request->display_date,
                'verse' => $request->verse,
                'meditation' => $request->meditation,
                'biblical_reference' => $request->biblical_reference,
                'liturgical_category' => $request->liturgical_category,
                'status' => $request->status,
                'scheduled_at' => $request->scheduled_at
            ]);

            return response()->json(['message' => "reading has been created", "data" => new DailingReadingResource($readingDailing)], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'something went wrong'], 500);
        }
    }

    public function update (Request $request, DailyReading $dailingReading) {

        $validator = Validator::make($request->all(), [
             'display_date' => ['sometimes','required','date','date_format:Y-m-d','after_or_equal:today',
                Rule::unique('daily_readings', 'display_date')->ignore($dailyReading->id)
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
            ],
            'scheduled_at' => [
                'sometimes',
                'nullable',
                'date',
                'after:now',
                'required_if:status,scheduled'
            ],

        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        if (!empty($validatedData)) {
            $dailingReading->update($validatedData);
        }

        return response()->json(['message' => "reading has been created", "data" => new DailingReadingResource($readingDailing)], 200);
    }

    public function delete (Request $request, DailyReading $dailingReading)  {

        $dailingReading->delete();

        return response()->json([
            'statut' => 'success',
            'message' => "reading has been deleted",
        ], 200);
    }
}
