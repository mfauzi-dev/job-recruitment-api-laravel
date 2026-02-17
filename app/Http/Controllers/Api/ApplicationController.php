<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{

    public function store(Request $request, $jobId)
    {
        try {
            $user = $request->user();

            if (!$user->curriculum_vitae_url) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silahkan upload Daftar Riwayat Hidup anda terlebih dulu'
                ], 400);
            }

            $job = Job::where('id', $jobId)
                      ->where('status', 'open')
                      ->first();

            if (!$job) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pekerjaan ini telah ditutup.'
                ], 404);
            }

            $existingApplication = Application::where('user_id', $user->id)
                                              ->where('job_id', $job->id)
                                              ->first();

            if ($existingApplication) {
                if ($request->hasFile('cover_letter')) {
                    $tempPath = $request->file('cover_letter')->path();
                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melamar pekerjaan ini sebelumnya.'
                ], 400);
            }

            $request->validate([
                'cover_letter' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:2048'],
            ]);

            $filename = 'coverLetter-' . Str::slug($user->name) . '-' . Str::random(5) . '.' . $request->file('cover_letter')->getClientOriginalExtension();
            $coverLetterPath = $request->file('cover_letter')->storeAs(
                'uploads/cover_letter',
                $filename,
                'public'
            );

            $application = Application::create([
                'user_id'          => $user->id,
                'job_id'           => $job->id,
                'status'           => 'pending',
                'cover_letter_url' => $coverLetterPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Surat lamaran berhasil dikirim',
                'data'    => [
                    ...$application->toArray(),
                    'cover_letter_public_url' => asset('storage/' . $application->cover_letter_url),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllApplications(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'page'   => ['nullable', 'integer', 'min:1'],
            'size'   => ['nullable', 'integer', 'max:10'],
        ]);

        try {
            $search = $request->search;
            $size   = $request->input('size', 10);

            $user    = $request->user();
            $company = Company::where('user_id', $user->id)->first();

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perusahaan tidak ditemukan untuk user ini'
                ], 404);
            }

            $query = Application::with([
                        'job:id,title,company_id',
                        'user:id,name,email,curriculum_vitae_url',
                    ])
                    ->whereHas('job', function($q) use ($company) {
                        $q->where('company_id', $company->id);
                    });

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('status', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('job', function($jobQuery) use ($search) {
                          $jobQuery->where('title', 'like', '%' . $search . '%');
                      });
                });
            }

            $query = $query->latest()->paginate($size)->withQueryString();

            $customResponse = [
                'applications' => $query->items(),
                'meta'         => [
                    'current_page' => $query->currentPage(),
                    'from'         => $query->firstItem(),
                    'last_page'    => $query->lastPage(),
                    'path'         => $query->path(),
                    'per_page'     => $query->perPage(),
                    'to'           => $query->lastItem(),
                    'total'        => $query->total(),
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Semua pelamar berhasil didapatkan.',
                'data'    => $customResponse
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $jobId, $applicationId)
    {
        try {
            $user = $request->user();

            $request->validate([
                'status' => ['required', 'in:pending,accepted,rejected'],
            ]);

            $company = Company::where('user_id', $user->id)->first();

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perusahaan tidak ditemukan'
                ], 404);
            }

            $application = Application::where('id', $applicationId)
                ->where('job_id', $jobId)
                ->whereHas('job', function($q) use ($company) {
                    $q->where('company_id', $company->id);
                })
                ->first();

            if (!$application) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data lamaran tidak ditemukan.'
                ], 404);
            }

            $application->update([
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status surat lamaran berhasil diupdate',
                'data'    => $application
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request, $jobId, $applicationId)
    {
        try {
            $user = $request->user();

            $application = Application::where('id', $applicationId)
                ->where('user_id', $user->id)
                ->where('job_id', $jobId)
                ->with([
                    'user:id,name,email,curriculum_vitae_url',
                    'job:id,title,company_id',
                    'job.company:id,name',
                ])
                ->first();

            if (!$application) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data lamaran tidak ditemukan.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail surat lamaran anda',
                'data'    => $application
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}