<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JobController extends Controller
{
    
    public function getAll(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'max:10'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0'],
            'job_type' => ['nullable', 'in:fulltime,parttime,contract,internship,remote'],
            'status' => ['nullable', 'in:draft,open,closed'],
        ]);

        try {
            $search = $request->search;
            $size = $request->input('size', 10);

            $query = Job::query();

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%')
                    ->orWhere('job_type', 'like', '%' . $search . '%')
                    ->orWhereHas('company', function($companyQuery) use ($search) {
                        $companyQuery->where('name', 'like', '%' . $search . '%');
                    });
                });
            }

            if ($request->salary_min) {
                $query->where('salary_min', '>=', $request->salary_min);
            }

            if ($request->salary_max) {
                $query->where('salary_max', '<=', $request->salary_max);
            }

            // Filter job_type
            if ($request->job_type) {
                $query->where('job_type', $request->job_type);
            }

            // Filter status
            if ($request->status) {
                $query->where('status', $request->status);
            }

            $query = $query->latest()->paginate($size)->withQueryString();

            $customResponse = [
                'jobs' => $query->items(),
                'meta' => [
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
                'message' => 'Data Job Berhasil Diambil',
                'data' => $customResponse
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function publicShow($id)
    {
        try {
            $job = Job::with([
                    'company:id,name,logo_url,location,industry'
                ])
                ->where('id', $id)
                ->where('status', 'open') // hanya job aktif
                ->first();

            if (!$job) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job tidak ditemukan atau sudah ditutup'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail job berhasil diambil',
                'data' => $job
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'max:10']
        ]);

    
        $search = $request->search;
        $size = $request->input('size', 10);

        $user = $request->user();
        $company = Company::where('user_id', $user->id)->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found. Please create a company first.'
            ], 404);
        }

        $query = Job::where('company_id', $company->id)
                    ->where(function($q) use ($search) {
                        if ($search) {
                            $q->where('title', 'like', '%' . $search . '%')
                              ->orWhere('description', 'like', '%' . $search . '%')
                              ->orWhere('location', 'like', '%' . $search . '%')
                              ->orWhere('job_type', 'like', '%' . $search . '%')
                              ->orWhere('status', 'like', '%' . $search . '%');
                        }
                    })
                    ->latest()
                    ->paginate($size)
                    ->withQueryString();

        $customResponse = [
            'jobs' => $query->items(), // atau pakai JobResource::collection($query) kalau ada Resource
            'meta' => [
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
            'message' => 'Data Job Berhasil Diambil',
            'data' => $customResponse
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|gte:salary_min',
            'location' => 'required|string|max:255',
            'job_type' => 'required|in:fulltime,parttime,contract,internship,remote',
            'status' => 'required|in:draft,open,closed',
            'deadline' => 'nullable|date|after:today',
        ]);

        // Ambil company milik user login
        $company = Company::where('user_id', $request->user()->id)->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found. Please create a company first.'
            ], 404);
        }

        $job = Job::create([
            'company_id' => $company->id,
            'title' => $request->title,
            'slug' => Str::slug($request->title . '-' . Str::random(5)),
            'description' => $request->description,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'location' => $request->location,
            'job_type' => $request->job_type,
            'status' => $request->status,
            'deadline' => $request->deadline,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job created successfully',
            'data' => $job
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $company = Company::where('user_id', $user->id)->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        $job = Job::where('company_id', $company->id)
                  ->where('id', $id)
                  ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $job
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|gte:salary_min',
            'location' => 'required|string|max:255',
            'job_type' => 'required|in:fulltime,parttime,contract,internship,remote',
            'status' => 'required|in:draft,open,closed',
            'deadline' => 'nullable|date|after:today',
        ]);

        $user = $request->user();
        $company = Company::where('user_id', $user->id)->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        $job = Job::where('company_id', $company->id)
                  ->where('id', $id)
                  ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        $job->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title . '-' . Str::random(5)),
            'description' => $request->description,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'location' => $request->location,
            'job_type' => $request->job_type,
            'status' => $request->status,
            'deadline' => $request->deadline,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully',
            'data' => $job
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $company = Company::where('user_id', $user->id)->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        $job = Job::where('company_id', $company->id)
                  ->where('id', $id)
                  ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully'
        ], 200);
    }
}